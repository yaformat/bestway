<?php
namespace App\Utils;

use App\Http\Requests\ItemsListBaseRequest;

class ItemsListSearchParameters
{
    public $search;
    public $filters;
    public $sortBy;
    public $limit;
    public $page;
    public $request;

    public function __construct(ItemsListBaseRequest $request)
    {
        $this->request = $request;
        $this->search = $request->getSearch();
        $this->filters = $request->getFilters();
        $this->sortBy = $request->getSortBy();
        $this->limit = $request->getLimit();
        $this->page = $request->getPage();
    }
}