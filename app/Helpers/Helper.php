<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

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

    private static function formatNumber($number = 0, $decimals = 0)
    {
        return number_format($number, $decimals, '.', ' ');
    }

    public static function formatPrice($price = 0)
    {
        $decimals = (floor($price) === $price) ? 0 : 2;

        return self::formatNumber($price, $decimals) . ' ' . self::CURRENCY;
    }

    public static function formatTimeObject($hours = 0, $minutes = 0)
    {
        return [
            'hours' => intval($hours),
            'minutes' => intval($minutes),
        ];
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