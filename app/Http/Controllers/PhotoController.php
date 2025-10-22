<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Http\Responses\ApiResponse;
use App\Models\Photo;
use App\Services\ImageService;

use App\Http\Requests\PhotoUploadRequest;
use Illuminate\Http\Request;

class PhotoController extends BaseController
{
    private const FOLDER = 'app/public/img/all/';
    protected ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function show($id)
    {
        $photo = Photo::findOrFail($id);

        return ApiResponse::success($photo->toArray());
    }

    /**
     * @OA\Post(
     *     path="/api/photo/upload",
     *     summary="Загрузка фото",
     *     description="Загружает новое фото на сервер",
     *     operationId="uploadPhoto",
     *     tags={"Медиа: Фото"},
     *     @OA\RequestBody(
     *         description="Данные, необходимые для загрузки нового фото",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="photo",
     *                     description="Файл изображения для загрузки",
     *                     type="string",
     *                     format="binary",
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Данные загруженного фото.",
     *         @OA\JsonContent(
     *           allOf={
     *               @OA\Schema(ref="#/components/schemas/SuccessResult"),
     *         @OA\Schema(
     *          @OA\Property(
     *             property="data",
     *             ref="#/components/schemas/PhotoElement"
     *          ),
     *         ),
     *     } 
     * )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Неверный запрос.",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResult")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизованный доступ, требуется аутентификация",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResult")
     *     ),
     * )
     */
    public function upload(PhotoUploadRequest $request)
    {
        $folderPath = storage_path(self::FOLDER);

        $filename = $this->imageService->resizeAndSaveWithAspectRatio(
            $request->file('photo'),
            1000,
            1000,
            $folderPath
        );

        $photo = Photo::create([
            'filename' => $filename,
        ]);

        return ApiResponse::success($photo->toArray(), '', 201);
    }

    public function rotate(Request $request, Photo $photo)
    {
        $request->validate([
            'angle' => 'required|numeric|in:0,90,180,270,360'
        ]);

        $angle = (int) $request->input('angle');

        try {
            $this->imageService->rotateImage($photo->path, $angle);

            $photo->updated_at = now();
            $photo->save();

            return ApiResponse::success($photo->toArray());
        } catch (\Throwable $e) {
            return ApiResponse::error('Не удалось повернуть изображение');
        }
    }

    /**
    * @OA\Delete(
    *     path="/api/photo/{id}",
    *     summary="Удаление фото",
    *     description="Удаляет указанное фото с сервера",
    *     operationId="deletePhoto",
    *     tags={"Медиа: Фото"},
    *      @OA\Parameter(
    *          name="id",
    *          description="ID фото для удаления",
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
    *         description="Фото, указанное для удаления, не найдено",
    *         @OA\JsonContent(ref="#/components/schemas/ErrorResult")
    *     ),
    * )
    */
    public function destroy(int $id)
    {
        $photo = Photo::findOrFail($id);
        $photo->forceDelete();

        return ApiResponse::success(true);
    }

}