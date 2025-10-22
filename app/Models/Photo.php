<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Helpers\Helper;
use Illuminate\Support\Facades\File;

class Photo extends Model
{
    use HasFactory, SoftDeletes, Prunable;

    public const FOLDER = 'app/public/img/';

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
        // Удаляем вызов deletePhotoFile, так как он уже вызывается в forceDelete
        // $this->deletePhotoFile();
    }

    public function deletePhotoFile()
    {
        if (File::exists($this->path)) {
            File::delete($this->path);
        }
    }

    /**
     * Переопределение метода forceDelete для удаления файла фотографии
     */
    public function forceDelete()
    {
        $this->deletePhotoFile();
        return parent::forceDelete();
    }

    /**
     * Get all of the owning photoable models.
     */
    public function entity()
    {
        return $this->morphTo();
    }

    public function getPathAttribute() {
        $folder = self::FOLDER;
        $folder .= $this->entity.'/'.$this->filename;
        $path = storage_path($folder);
        return $path;
    }

    public function getUrlAttribute() {
        $baseUrl = url(\Storage::url('img/'.$this->entity.'/'.$this->attributes['filename']));
        $timestamp = $this->updated_at ? $this->updated_at->timestamp : time();

        return $baseUrl . '?v=' . $timestamp;   
    }

    public function getEntityAttribute() {
        $entity_type = $this->attributes['entity_type'] ?? null;

        return Helper::getDirectoryNameFromEntity($entity_type);
    }
}
