<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function getItem(int $id)
    {
        return $this->find($id);
    }

    public function getItems(\App\Utils\ItemsListSearchParameters $parameters)
    {
        $query = $this->model->query();

        $this->applyQueryFilters($query, $parameters);
        $this->applyQuerySorting($query, $parameters->sortBy);

        return $query->paginate($parameters->limit, ['*'], 'page', $parameters->page);
    }
    
    public function find(int $id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        $item = $this->model->create($data);
        $this->processPhotoData($item, $data);
        return $item;
    }

    public function update(int $id, array $data)
    {
        $item = $this->find($id);
        $item->update($data);
        $this->processPhotoData($item, $data);
        return $item;
    }

    public function delete(int $id)
    {
        return (bool) $this->find($id)->delete();
    }

    public function restore(int $id)
    {
        $this->ensureSoftDeletes();

        $item = $this->model->withTrashed()->findOrFail($id);
        $item->restore();

        return $item;
    }

    /**
     * Копирует ресурс с новыми данными
     */
    public function copy(int $originalId, array $newData)
    {
        $original = $this->find($originalId);
        
        // Получаем все атрибуты оригинала, исключая системные поля
        $attributes = $original->toArray();
        
        // Удаляем поля, которые не должны копироваться
        unset($attributes['id']);
        unset($attributes['created_at']);
        unset($attributes['updated_at']);
        unset($attributes['deleted_at']);
        
        // Применяем новые данные
        $attributes = array_merge($attributes, $newData);
        
        // Создаем новый ресурс
        $newItem = $this->model->create($attributes);
        
        // Копируем связанные данные если есть
        $this->copyRelatedData($original, $newItem);
        
        return $newItem;
    }

    /**
     * Копирует связанные данные (переопределяется в дочерних репозиториях при необходимости)
     */
    protected function copyRelatedData(Model $original, Model $copy): void
    {
        // Базовая реализация - ничего не делаем
        // Переопределяется в конкретных репозиториях для копирования связей
    }

    public function trashedCount(): int
    {
        $this->ensureSoftDeletes();

        return $this->model->onlyTrashed()->count();
    }

    public function toggleActive(int $id)
    {
        $item = $this->find($id);
        $item->is_active = !$item->is_active;
        $item->save();

        return $item;
    }

    public function applyQueryFilters(&$query, \App\Utils\ItemsListSearchParameters $parameters)
    {
        if (!empty($parameters->filters['only_trashed'][0])) {
            $this->applyQueryOnlyTrashed($query);
        }

        // Поиск (включая переводы)
        if (!empty($parameters->search)) {
            $searchTerm = '%' . $parameters->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhereHas('translations', function($subQ) use ($searchTerm) {
                      $subQ->where('name', 'like', $searchTerm);
                  });
            });
        }
    }

    public function applyQuerySorting(Builder &$query, ?string $sorting): void
    {
        $direction = $this->checkSorting($sorting);

        if ($direction) {
            $query->orderBy($sorting, $direction);
        }
    }

    public function applyQueryOnlyTrashed(Builder &$query): void
    {
        $this->ensureSoftDeletes();
        $query->onlyTrashed();
    }

    protected function checkSorting(?string $sorting): ?string
    {
        if (!$sorting || !method_exists($this->model, 'sortings')) {
            return null;
        }

        $sortings = $this->model->sortings();
        if (!isset($sortings[$sorting])) {
            throw new \InvalidArgumentException("Invalid sorting parameter: $sorting");
        }

        return $sortings[$sorting];
    }

    protected function ensureSoftDeletes(): void
    {
        if (!in_array(SoftDeletes::class, class_uses_recursive($this->model))) {
            throw new \LogicException(class_basename($this->model) . ' does not use SoftDeletes trait.');
        }
    }

    /**
     * Обрабатывает данные фотографий для модели
     */
    public function processPhotoData(Model $item, array $data): void
    {
        $photoChanged = false;
        
        if (isset($data['photo_id'])) {
            $item->setPhoto($data['photo_id']);
            $photoChanged = true;
        }

        if (isset($data['photo_ids'])) {
            $item->setPhotos($data['photo_ids']);
            $photoChanged = true;
        }

        if (!empty($data['photoMarkedForDeletion'])) {
            $item->markPhotoForDeletion();
            $photoChanged = true;
        }

        if ($photoChanged) {
            $item->save();
        }
    }
}
