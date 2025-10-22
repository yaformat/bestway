<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

/**
* @OA\Info(title="BestWay Api", version="1.02")
* @OA\Tag(
*   name="Справочник: Кухни",
*   description=""
* ),
* @OA\Tag(
*   name="Справочник: Оборудование",
*   description=""
* ),
* @OA\Tag(
*   name="Справочник: Поставщики",
*   description=""
* ),
* @OA\Tag(
*   name="Справочник: Поставщики (локации)",
*   description=""
* ),
* @OA\Tag(
*   name="РЕСУРСЫ",
*   description=""
* ),
* @OA\Tag(
*   name="РЕСУРСЫ: Категории",
*   description=""
* ),
* @OA\Tag(
*   name="Тех. карты",
*   description=""
* ),
* @OA\Tag(
*   name="Тех. карты: Полуфабрикаты",
*   description=""
* ),
* @OA\Tag(
*   name="Тех. карты: Категории",
*   description=""
* ),
* @OA\Tag(
*   name="Склады",
*   description=""
* ),
* @OA\Tag(
*   name="Склады: Поставки",
*   description=""
* ),
* @OA\Tag(
*   name="Склады: Инветаризации",
*   description=""
* ),
* @OA\Tag(
*   name="Склады: Списания",
*   description=""
* ),
* @OA\Tag(
*   name="Склады: Перемещения",
*   description=""
* ),
* @OA\Tag(
*   name="Склады: Подтверждения",
*   description=""
* ),
* @OA\Tag(
*   name="Производство: Шаблоны",
*   description=""
* ),
* @OA\Tag(
*   name="Производство: План",
*   description=""
* ),
* @OA\Tag(
*   name="КАССЫ",
*   description=""
* ),
* @OA\Tag(
*   name="КАССЫ: Транзакции",
*   description=""
* ),
* @OA\Tag(
*   name="КАССЫ: Категории транзакций",
*   description=""
* ),
* @OA\Tag(
*   name="Медиа: Фото",
*   description=""
* ),
* @OA\Tag(
*   name="Медиа: Видео",
*   description=""
* ),
*/
class Controller extends BaseController
{

    public function __construct()
    {

    }

    public function index()
    {
        return view('application');
    }

}
