<?php
namespace App\Services;

use Intervention\Image\ImageManager;
use Illuminate\Http\UploadedFile;
use App\Helpers\Helper;

class ImageService
{
    public function __construct(protected ImageManager $manager) {}

    /**
     * Resize image with aspect ratio and save to folder on disk
     *
     * @param UploadedFile $file
     * @param int $maxWidth
     * @param int $maxHeight
     * @param string $folderPath Абсолютный путь для сохранения
     * @return string имя файла
     */
    public function resizeAndSaveWithAspectRatio(
        UploadedFile $file,
        int $maxWidth,
        int $maxHeight,
        string $folderPath
    ): string {
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0755, true);
        }

        $filename = Helper::generateUniqueFilename($file);

        $image = $this->manager->read($file->getRealPath()); 

        $image->resize($maxWidth, $maxHeight, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $image->save($folderPath . $filename);

        return $filename;
    }

    /**
     * Поворачивает изображение на заданный угол и сохраняет обратно
     *
     * @param string $path Абсолютный путь к файлу
     * @param int $angle Угол поворота (по часовой стрелке)
     * @return void
     */
    public function rotateImage(string $path, int $angle): void
    {
        $image = $this->manager->read($path);
        $image->rotate(-$angle);
        $image->save($path);
    }
    
}
