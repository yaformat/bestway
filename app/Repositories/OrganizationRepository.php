<?php
namespace App\Repositories;

use App\Models\Organization;
use App\Helpers\ValidatorHelper;

class OrganizationRepository extends BaseRepository
{
    public function __construct(Organization $model)
    {
        parent::__construct($model);
    }

    public function getItems(\App\Utils\ItemsListSearchParameters $parameters)
    {
        $query = Organization::query();

        $query->with(['owner']);
        $query->withCount(['users']);

        if(!empty($parameters->search)) {
            $query->where('name', 'like', '%' . $parameters->search . '%');
        }

        $this->applyQuerySorting($query, $parameters->sortBy);

        return $query->paginate($parameters->limit, ['*'], 'page', $parameters->page);
    }

    public function getItem($id)
    {
        return Organization::with(['owner'])->findOrFail($id);
    }
}