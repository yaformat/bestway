<?php
namespace App\Repositories;

use App\Models\User;
use App\Helpers\ValidatorHelper;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function getItems(\App\Utils\ItemsListSearchParameters $parameters)
    {
        $query = User::query();

        if(!empty($parameters->search)) {
            $query->where('name', 'like', '%' . $parameters->search . '%');
        }

        $this->applyQuerySorting($query, $parameters->sortBy);
        
        return $query->paginate($parameters->limit, ['*'], 'page', $parameters->page);
    }

    public function getItem($id)
    {
        return User::findOrFail($id);
    }
}