<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use App\Models\Resource;
use App\Enums\ResourceUnitEnum;
use App\Enums\ResourceLosseEnum;
use App\Enums\StockActionStatusEnum;

use Carbon\Carbon;

/**
 *  @OA\Schema(
 *      schema="Price",
 *      type="object",
 *      @OA\Property(property="raw", type="float", example=125.5),
 *      @OA\Property(property="currency", type="string", example="с"),
 *      @OA\Property(property="display", type="string", example="125.5 c"),
 *  ), 
 *  @OA\Schema(
 *      schema="Status",
 *      type="object",
 *      @OA\Property(property="id", type="string", example="draft", enum={"draft","processing","completed","archived","cancelled","confirmation","confirmed"}),
 *      @OA\Property(property="name", type="string", example="Черновик"),
 *      @OA\Property(property="bgcolor", nullable=true, description="Цвет фона", type="string", example="#CC771D"),
 *      @OA\Property(property="textcolor", nullable=true, description="Цвет текста", type="string", example="#ffffff"),
 *      @OA\Property(property="bordercolor", nullable=true, description="Цвет обводки", type="string", example="##BA6D1A"),
 *  ),
 *  @OA\Schema(
 *      schema="StockValue",
 *      type="object",
 *      nullable="true",
 *      @OA\Property(property="raw", type="float", example=1.5),
 *      @OA\Property(property="unit", type="string", example="kilogram"),
 *      @OA\Property(property="display", type="string", example="1.5 кг"),
 *  ),
 *  @OA\Schema(
 *      schema="Time",
 *      type="object",
 *      nullable="true",
 *      @OA\Property(property="hours", type="integer", example=0),
 *      @OA\Property(property="minutes", type="integer", example=30),
 *  ),
 * @OA\Schema(
 *     schema="ResourceLosses",
 *     title="Модель данных потерь ресурса",
 *     description="Модель данных ResourceLosses",
 *     required={"id"},
 *     type="object",
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         description="ID потери",
 *         example="peeling"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="название",
 *         example="чистка"
 *     ),
 *     @OA\Property(
 *         property="value",
 *         type="number",
 *         format="integer",
 *         description="Значение потери",
 *         example=5
 *     ),
 * )
 */

class Helper
{
    const CURRENCY = 'с';

    public static function formatValue(Resource $resource, $value = 0)
    {
        if ($resource->unit_factor > 0) {
            $value /= $resource->unit_factor;
        }
        $value = round($value, $resource->round);

        return sprintf('%s %s', $value, $resource->unit_label);
    }

    private static function formatNumber($number = 0, $decimals = 0)
    {
        return number_format($number, $decimals, '.', ' ');
    }

    public static function formatPrice($price = 0)
    {
        $decimals = (floor($price) === $price) ? 0 : 2;

        return self::formatNumber($price, $decimals) . ' ' . self::CURRENCY;
    }

    public static function formatStockValue($value = 0)
    {
        $formatted = round($value, 2);
        if(floor($formatted) == $formatted) {
            return intval($formatted);
        } else {
            // Remove trailing zeros
            return floatval(number_format($formatted, 2, '.', ''));
        }
        return $value;
    }

    public static function formatPriceObject($price = 0, ResourceUnitEnum $unit = null)
    {
        $price = round($price, 3);

        $priceObject = new \StdClass;
        $priceObject->raw = $price;
        $priceObject->currency = self::CURRENCY;
        $priceObject->display = self::formatPrice($price) . (!empty($unit) ? '/'.$unit->getLabel() : '');

        return $priceObject;
    }

    public static function formatUnitPriceObject($price = 0, $value = 0, ResourceUnitEnum $unit = null, $unit_factor = 0)
    {
        if (!$unit_factor || !$value) {
            $price = 0;
        } else {
            $price = $price * $unit_factor;
            $price = $price / $value;
        }

        $price = round($price, 3);

        $priceObject = new \StdClass;
        $priceObject->raw = $price;
        $priceObject->currency = self::CURRENCY;
        $priceObject->unit_label = $unit->getLabel();
        $priceObject->display = self::formatPrice($price) . (!empty($unit) ? '/'.$unit->getLabel() : '');

        return $priceObject;
    }

    public static function formatTimeObject($hours = 0, $minutes = 0)
    {
        return [
            'hours' => intval($hours),
            'minutes' => intval($minutes),
        ];
    }

    public static function formatLossesObject($value = null, $skip = false)
    {
        $losses = [];
        $value = json_decode($value, true);

        // Check if the value is an array of objects or an associative array
        if (isset($value[0]) && is_array($value[0]) && isset($value[0]['id'])) {
            // Convert array of objects to associative array
            $value = array_reduce($value, function ($acc, $item) {
                $acc[$item['id']] = $item['value'];
                return $acc;
            }, []);
        }

        foreach (ResourceLosseEnum::toArray() as $losseName => $losse) {
            $value_ = (isset($value[$losseName])) ? $value[$losseName] : 0;

            if (!$value_ && $skip) continue; 

            $losses[] = [
                'id' => $losseName,
                'name' => $losse['label'],
                'value' => $value_
            ];
        }

        return $losses;
    }

    public static function formatStockValueObject($value = 0, $unit_factor = 1, ResourceUnitEnum $unit = null)
    {
        $unit_factor = (float) $unit_factor > 0 ? $unit_factor : 1;
        
        $value = $value / $unit_factor;

        $valueObject = new \StdClass;
        $valueObject->raw = self::formatStockValue($value);
        $valueObject->unit = $unit;
        $valueObject->unit_label = (!empty($unit) ? $unit->getLabel() : null);
        $valueObject->display = $valueObject->raw . (!empty($unit) ? ' '.$unit->getLabel() : '');

        return $valueObject;
    }

    public static function formatDate($date) {
        $carbonDate = Carbon::parse($date);

        $dateObject = new \StdClass;
        $dateObject->raw = $date;
        $dateObject->day = $carbonDate->locale('ru')->isoFormat('dd');
        $dateObject->date = $carbonDate->format('d.m');
        $dateObject->display = $carbonDate->locale('ru')->isoFormat('dd DD.MM');
    
        return $dateObject;
    }

    public static function formatStatusObject(StockActionStatusEnum $status) {
        if (!$status) return null;
        
        $statusObject = new \StdClass;
        $statusObject->id = $status;
        $statusObject->name = $status->getLabel();

        $colors = $status->getColors();

        $statusObject->bgcolor = $colors[0];
        $statusObject->textcolor = $colors[1];
        $statusObject->bordercolor = $colors[2];

        return $statusObject;
    }

    public static function generateUniqueFilename($file): string
    {
        return time().'_'.$file->getClientOriginalName();
    }

    public static function createDirectoryIfNeeded(string $folderPath): void
    {
        if (!File::isDirectory($folderPath)) {
            File::makeDirectory($folderPath, 0777, true, true);
        }
    }

    public static function getDirectoryNameFromEntity($entity_name = '')
    {
        if (empty($entity_name)) {
            return 'all';
        }

        $entity_name_parts = explode('\\', $entity_name);

        $entity_name = end($entity_name_parts);
        $entity_name = Str::snake($entity_name);

        return $entity_name;
    }

}