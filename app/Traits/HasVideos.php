<?php 

namespace App\Traits;

use App\Models\Video;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasVideos
{
    private function saveVideosFromRequest($model): void
    {
        // Проверка флага, чтобы метод не выполнялся, если флаг в модели установлен
        if (property_exists($model, 'preventSaveVideos') && $model->preventSaveVideos) {
            return;
        }

        $request = request();

        // Обработка состояния на удаление видео
        if ($request->has('videoMarkedForDeletion') && $request->input('videoMarkedForDeletion')) {
            $this->deleteVideosPermanently($model);
            return;
        }

        $ids = [];

        if ($request->has('video_ids')) {
            $ids = $request->input('video_ids');
        }
    
        if ($request->has('video_id')) {
            $ids[] = $request->input('video_id');
        }
    
        if (empty($ids)) {
            return;
        }

        $videos = Video::whereIn('id', $ids)
            ->where('entity_id', 0)
            ->whereNull('entity_type')
            ->get();

        if ($videos->isEmpty()) {
            return;
        }

        foreach ($videos as $video) {
            $video->entity_id = $model->id;
            $video->entity_type = get_class($model);
            \Log::info('Saving video for entity', [
                'entity_type' => get_class($model), 
                'entity_id' => $model->id,
                'model' => $model
            ]);
            $video->save();
        }

        $ids = $videos->pluck('id')->toArray();
        
        //Delete video ids not presented in request
        Video::whereNotIn('id', $ids)
            ->where('entity_id', $model->id)
            ->where('entity_type', get_class($model))
            ->delete();

        //Move videos to specific folder
        $this->moveVideos($model, $ids);
    }

    private function moveVideos($model, $ids = []): void
    {
        $videos = Video::whereIn('id', $ids)
            ->where('entity_id', $model->id)
            ->where('entity_type', get_class($model))
            ->get();

        foreach ($videos as $video) {
            $oldFolder = storage_path(Video::FOLDER.'all/');
            $newFolder = storage_path(Video::FOLDER.$video->entity.'/');

            if (\File::exists($newFolder.$video->filename)) {
                continue;
            }

            if (!\File::exists($newFolder)) {
                \File::makeDirectory($newFolder, 0755, true);
            }
        
            \File::move(
                $oldFolder.$video->filename,
                $newFolder.$video->filename
            );
        }
    }

    private function deleteVideosPermanently($model): void
    {
        Video::where('entity_id', $model->id)
            ->where('entity_type', get_class($model))
            ->chunk(100, function ($videos) {
                $videos->each->forceDelete();
            });
    }

    /**
     * Boot the HasVideos trait.
     *
     * Listen for the deleting event of a model, then remove the relation between it and its videos
     */
    protected static function bootHasVideos()
    {
        static::saved(function ($model) {
            $model->saveVideosFromRequest($model);
        });

        static::deleted(function($model) {
            if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()) {
                $model->deleteVideosPermanently($model);
            } else {
                // Do not delete videos on soft delete
                // $model->videos()->delete();
            }
        });
    
        if (in_array(SoftDeletes::class, class_uses(static::class))) {
            static::restored(function($model) {
                //$model->videos()->restore();
            });
        }

        static::addGlobalScope('withVideos', function ($builder) {
            $builder->with(['videos' => function ($query) {
                //$query->withTrashed();
            }]);
        });

        static::retrieved(function($model) {
            $model->append('video');
        });

    }
    
    /**
    * @OA\Schema(
    *   schema="VideoElement",
    *   title="Модель Видео",
    *   type="object",
    *   nullable=true,
    *   @OA\Property(
    *     property="id",
    *     type="integer",
    *     description="The video's id",
    *   ),
    *   @OA\Property(
    *     property="filename",
    *     type="string",
    *     description="The video's filename",
    *   ),
    *   @OA\Property(
    *     property="url",
    *     type="string",
    *     description="The URL to the video",
    *   ),
    * )
    */
    public function getVideoAttribute()
    {
        $video = $this->videos->first();

        return $video;
    }

    /**
     * Get all of the model's videos
     */
    public function videos()
    {
        return $this->morphMany(Video::class, 'entity');
    }
}
