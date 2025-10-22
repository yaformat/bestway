<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use App\Models\Video;
use App\Http\Responses\ApiResponse;
use App\Helpers\Helper;

use App\Http\Requests\VideoUploadRequest;

class VideoController extends BaseController
{
    private const FOLDER = 'app/public/video/all/';

    /**
     * @OA\Post(
     *     path="/api/video/upload",
     *     summary="Загрузка видео",
     *     description="Загружает новое видео на сервер",
     *     operationId="uploadVideo",
     *     tags={"Медиа: Видео"},
     *     @OA\RequestBody(
     *         description="Данные, необходимые для загрузки нового видео",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="video",
     *                     description="Файл изображения для загрузки",
     *                     type="string",
     *                     format="binary",
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Данные загруженного видео.",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Неверный запрос. Либо неправильный ввод, либо файл не предоставлен.",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResult")
     *     ),
     * )
     */
    public function upload(VideoUploadRequest $request): JsonResponse
    {
        if (!$request->hasFile('video')) {
            return ApiResponse::error('Файл не предоставлен', 400);
        }

        $filename = $this->saveFile($request->file('video'));

        return $this->storeVideoAndRespond($filename);
    }

    private function saveFile($file): string 
    {
        $folderPath = storage_path(self::FOLDER);
        Helper::createDirectoryIfNeeded($folderPath);

        $filename = Helper::generateUniqueFilename($file);
        $file->move($folderPath, $filename);

        return $filename;
    }

    private function storeVideoAndRespond(string $filename): JsonResponse
    {
        $video = Video::create([
            'filename' => $filename,
        ]);

        return ApiResponse::success($video->toArray(), '', 201);
    }

    /**
    * @OA\Delete(
    *     path="/api/video/{id}",
    *     summary="Удаление видео",
    *     description="Удаляет указанное видео с сервера",
    *     operationId="deleteVideo",
    *     tags={"Медиа: Видео"},
    *      @OA\Parameter(
    *          name="id",
    *          description="ID видео для удаления",
    *          required=true,
   *          in="path",
   *          @OA\Schema(
   *              type="integer"
   *           ),
   *      ),
   *     @OA\Response(
   *         response=200,
   *         description="Результат удаления",
   *         @OA\JsonContent(ref="#/components/schemas/SuccessResult")
   *     ),
   *     @OA\Response(
   *         response=401,
   *         description="Неавторизованный доступ, требуется аутентификация",
   *         @OA\JsonContent(ref="#/components/schemas/ErrorResult")
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Видео, указанное для удаления, не найдено",
   *         @OA\JsonContent(ref="#/components/schemas/ErrorResult")
   *     ),
   * )
    */

    public function destroy(int $id)
    {
        $video = Video::findOrFail($id);
        $video->forceDelete();

        return ApiResponse::success(true);
    }
}
