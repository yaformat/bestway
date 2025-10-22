<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Resource;
use App\Models\ResourceStock;

class CalculateResourceCommand extends Command
{
    protected $signature = 'calculate:resource';
    protected $description = 'Calculate for Resource model';

    public function handle(): void
    {
        $resource_ids = $this->getResourceIds();

        if (empty($resource_ids)) {
            $this->info("No resource_ids found");
            return;
        }

        $resourcePrepareData = $this->prepareResourceStockData($resource_ids);

        $this->updateResourceData($resourcePrepareData);

        $this->info("calculate:resource executed");
    }

    private function getResourceIds()
    {
        return ResourceStock::query()
            ->where('status', 1)
            ->where(function ($query) {
                $query->whereNull('updated_at')
                ->orWhere('updated_at', '>=', now());
            })
            ->groupBy('resource_id')
            ->pluck('resource_id')
            ->toArray();
    }
    
    private function prepareResourceStockData(array $resource_ids): array
    {
        $resourceStocks = ResourceStock::whereIn('resource_id', $resource_ids)->where('status', 1)->orderBy('created_at', 'DESC')->get();

        $resourcePrepareData = [];

        foreach ($resourceStocks as $resourceStockRow) {

            $resource_id = $resourceStockRow->resource_id;

            if (!isset($resourcePrepareData[$resourceStockRow->resource_id])) {
                $resourcePrepareData[$resourceStockRow->resource_id] = [
                    'price' => 0,
                    'value' => 0,
                    'last_price' => 0,
                    'avg_price' => 0,
                ];
            }

            $resourcePrepareData[$resourceStockRow->resource_id]['value'] += $resourceStockRow->value;
            $resourcePrepareData[$resourceStockRow->resource_id]['price'] += $resourceStockRow->price;

            if (!$resourcePrepareData[$resourceStockRow->resource_id]['last_price'] && $resourceStockRow->supply_id > 0 && $resourceStockRow->value > 0 && $resourceStockRow->price > 0) {
                $resourcePrepareData[$resourceStockRow->resource_id]['last_price'] = $resourceStockRow->price / $resourceStockRow->value;
            }

        }

        foreach ($resourcePrepareData as $resource_id => &$prepareData) {
            if ($prepareData['price'] > 0 && $prepareData['value'] > 0) {
                $prepareData['avg_price'] = $prepareData['price'] / $prepareData['value'];
            }
        }

        ResourceStock::whereIn('resource_id', $resource_ids)->update(['updated_at' => now()]);

        return $resourcePrepareData;
    }

    private function updateResourceData(array $resourcePrepareData): void
    {
        $resourceData = [];
        foreach ($resourcePrepareData as $resource_id => $preparedData) {
            $resourceData[] = [
                'id' => $resource_id,
                'stocks_avg_price' => $preparedData['avg_price'],
                'stocks_last_price' => $preparedData['last_price'],
                'stocks_value' => $preparedData['value'],
                'stocks_price' => $preparedData['price'],
            ];
        }

        Resource::upsert($resourceData, 'id');
    }
}