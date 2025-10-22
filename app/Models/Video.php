<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\File;

use App\Helpers\Helper;

class Video extends Model
{
    use HasFactory, SoftDeletes, Prunable;

    public const FOLDER = 'app/public/video/';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'filename',
    ];

    protected $appends = [
        'url',
    ];

    protected $hidden = [
        'entity_id',
        'entity_type',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // public function prunable()
    // {
    //     return static::where('deleted_at', '<=', now()->subMonth());
    // }
 
    protected function pruning()
    {
        // Удаляем вызов deleteVideoFile, так как он уже вызывается в forceDelete
        // $this->deleteVideoFile();
    }

    public function deleteVideoFile()
    {
        $folder = self::FOLDER;
        $folder .= $this->entity.'/'.$this->filename;
        $path = storage_path($folder);

        if (File::exists($path)) {
            File::delete($path);
        }
    }

    /**
     * Переопределение метода forceDelete для удаления файла видео
     */
    public function forceDelete()
    {
        $this->deleteVideoFile();
        return parent::forceDelete();
    }

    /**
     * Get all of the owning videoable models.
     */
    public function entity()
    {
        return $this->morphTo();
    }

    public function getUrlAttribute() {
        return url(\Storage::url('video/'.$this->entity.'/'.$this->attributes['filename']));
    }

    public function getEntityAttribute() {
        $entity_type = $this->attributes['entity_type'] ?? null;

        return Helper::getDirectoryNameFromEntity($entity_type);
    }
}
