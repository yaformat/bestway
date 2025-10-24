<?php 
namespace App\Traits;
use App\Models\Photo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Collection;

trait HasPhotos
{
    /**
     * Флаг для отслеживания необходимости удаления фото
     */
    protected bool $shouldDeletePhoto = false;
    
    /**
     * ID фотографий для привязки
     */
    protected ?array $photoIdsToAttach = null;

    /**
     * Устанавливает фото для модели
     */
    public function setPhoto($photoId): self
    {
        $this->photoIdsToAttach = $photoId ? [$photoId] : [];
        return $this;
    }

    /**
     * Устанавливает несколько фото для модели
     */
    public function setPhotos(array $photoIds): self
    {
        $this->photoIdsToAttach = $photoIds;
        return $this;
    }

    /**
     * Помечает фото на удаление
     */
    public function markPhotoForDeletion(): self
    {
        $this->shouldDeletePhoto = true;
        return $this;
    }

    /**
     * Связывает фотографии с текущей моделью
     */
    protected function processPhotos(): void
    {
        // Если установлен флаг удаления
        if ($this->shouldDeletePhoto) {
            $this->deletePhotosPermanently($this);
            $this->shouldDeletePhoto = false;
            return;
        }

        // Если есть фото для привязки
        if ($this->photoIdsToAttach !== null) {
            $this->attachPhotos($this->photoIdsToAttach);
            $this->photoIdsToAttach = null;
        }
    }

    /**
     * Связывает фотографии с моделью
     */
    protected function attachPhotos(array $photoIds): void
    {
        if (empty($photoIds)) {
            return;
        }

        // Убираем пустые значения и дубликаты
        $photoIds = array_unique(array_filter($photoIds));
        if (empty($photoIds)) {
            return;
        }

        $photos = Photo::whereIn('id', $photoIds)
            ->where('entity_id', 0)
            ->whereNull('entity_type')
            ->get();

        if ($photos->isEmpty()) {
            return;
        }

        foreach ($photos as $photo) {
            $photo->entity_id = $this->id;
            $photo->entity_type = $this->getMorphClass();
            $photo->save();
        }

        $attachedIds = $photos->pluck('id')->toArray();

        // Удаляем старые фото
        Photo::whereNotIn('id', $attachedIds)
            ->where('entity_id', $this->id)
            ->where('entity_type', $this->getMorphClass())
            ->delete();

        // Перемещаем фото
        $this->movePhotos($this, $attachedIds);
    }

    /**
     * Перемещает фотографии в соответствующую папку
     */
    private function movePhotos($model, array $ids = []): void
    {
        $photos = Photo::whereIn('id', $ids)
            ->where('entity_id', $model->id)
            ->where('entity_type', $model->getMorphClass())
            ->get();

        foreach ($photos as $photo) {
            $oldFolder = storage_path(Photo::FOLDER.'all/');
            $newFolder = storage_path(Photo::FOLDER.$photo->entity.'/');

            if (\File::exists($newFolder.$photo->filename)) {
                continue;
            }

            if (!\File::exists($newFolder)) {
                \File::makeDirectory($newFolder, 0755, true);
            }

            \File::move(
                $oldFolder.$photo->filename,
                $newFolder.$photo->filename
            );
        }
    }

    /**
     * Полностью удаляет фотографии модели
     */
    private function deletePhotosPermanently($model): void
    {
        Photo::where('entity_id', $model->id)
            ->where('entity_type', $model->getMorphClass())
            ->chunk(10, function ($photos) {
                $photos->each->forceDelete();
            });
    }

    /**
     * Boot the HasPhotos trait
     */
    protected static function bootHasPhotos()
    {
        // Обрабатываем фото после сохранения модели
        static::saved(function($model) {
            $model->processPhotos();
        });

        static::updated(function($model) {
            $model->processPhotos();
        });

        // Обработка удаления модели
        static::deleted(function($model) {
            $photos = Photo::where('entity_id', $model->id)
                ->where('entity_type', $model->getMorphClass());

            if (!in_array(SoftDeletes::class, class_uses($model))) {
                // Если у родительской модели НЕТ soft deletes - удаляем фото полностью с файлами
                $photos->chunk(10, function ($chunks) {
                    $chunks->each->forceDelete();
                });
            } else {
                // Если у родительской модели ЕСТЬ soft deletes - просто помечаем фото как удаленные
                $photos->delete();
            }
        });

        // Добавляем глобальный scope для загрузки фото
        static::addGlobalScope('withPhotos', function ($builder) {
            $builder->with(['photos' => function ($query) {
                $query->withTrashed();
            }]);
        });

        // Добавляем атрибут photo при получении модели
        static::retrieved(function($model) {
            $model->append('photo');
        });
    }

    /**
     * Получает первое фото модели
     */
    public function getPhotoAttribute()
    {
        return $this->photos->first();
    }

    /**
     * Получает удаленные фото
     */
    public function getPhotosDeletedAttribute()
    {
        return $this->photos()->whereNotNull('deleted_at')->get();
    }

    /**
     * Получает активные фото - ИСПРАВЛЕНО!
     */
    public function getPhotosAttribute()
    {
        // Возвращаем КОЛЛЕКЦИЮ, а не Relation
        return $this->photos()->whereNull('deleted_at')->get();
    }

    /**
     * Определяет отношение морф-много для фотографий
     */
    public function photos()
    {
        return $this->morphMany(Photo::class, 'entity');
    }
}