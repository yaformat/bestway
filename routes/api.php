<?php

use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Route;

use App\Enums\ResourceTypeEnum;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;

use App\Http\Controllers\DataController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TranslationController;

use App\Http\Controllers\PhotoController;
use App\Http\Controllers\VideoController;

use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransactionCategoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::match(['get', 'post'], '/',  function () {
  return ApiResponse::error('Not Found', 404);
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::group(['middleware' => 'token.valid'], function() {
      Route::get('logout', [AuthController::class, 'logout']);
      Route::get('user', [AuthController::class, 'user']);
      Route::post('refresh', [AuthController::class, 'refresh']);
      
    });
});


Route::get('/translations/{code}', [TranslationController::class, 'getTranslations']);

//AUTH MIDDLEWARE
Route::group(['middleware' => ['token.valid', 'auth:sanctum']], function() {
  
  Route::get('/search', [SearchController::class, 'search']);
  Route::get('/global-data', [DataController::class, 'index']);
  Route::get('/navigation', [DataController::class, 'navigation']);

  //Photo
  Route::group(['prefix' => 'photo'], function () {
    Route::post('/upload', [PhotoController::class, 'upload']);
    Route::post('{photo}/rotate', [PhotoController::class, 'rotate']);
    Route::get('/{id}', [PhotoController::class, 'show']);
    Route::delete('/{id}', [PhotoController::class, 'destroy']);
  });
  
  //Video
  Route::group(['prefix' => 'video'], function () {
    Route::post('/upload', [VideoController::class, 'upload']);
    Route::delete('/{id}', [VideoController::class, 'destroy']);
  });


  
  // Направления
  Route::prefix('directions')->group(function () {
      Route::post('/', [App\Http\Controllers\DirectionController::class, 'index']);
      Route::put('/', [App\Http\Controllers\DirectionController::class, 'store']);
      Route::get('/root', [App\Http\Controllers\DirectionController::class, 'getRootDirections']);
      Route::get('/tree', [App\Http\Controllers\DirectionController::class, 'getDirectionTree']);
      Route::get('/{parentId}/children', [App\Http\Controllers\DirectionController::class, 'getChildDirections']);
      Route::get('/{id}', [App\Http\Controllers\DirectionController::class, 'show']);
      Route::put('/{id}', [App\Http\Controllers\DirectionController::class, 'update']);
      Route::delete('/{id}', [App\Http\Controllers\DirectionController::class, 'destroy']);
      Route::post('/{id}/restore', [App\Http\Controllers\DirectionController::class, 'restore']);
  });

  // Отели
  Route::prefix('hotels')->group(function () {
      Route::post('/', [App\Http\Controllers\HotelController::class, 'index']);
      Route::put('/', [App\Http\Controllers\HotelController::class, 'store']);
        
      Route::get('/reference-data', [App\Http\Controllers\HotelController::class, 'formReferenceData']); // Получить справочники для формы

      Route::get('/direction/{directionId}', [App\Http\Controllers\HotelController::class, 'searchByDirection']);
      Route::get('/resort/{resortId}', [App\Http\Controllers\HotelController::class, 'searchByResort']);
      Route::get('/{id}', [App\Http\Controllers\HotelController::class, 'show']);
      Route::put('/{id}', [App\Http\Controllers\HotelController::class, 'update']);
      Route::delete('/{id}', [App\Http\Controllers\HotelController::class, 'destroy']);
      Route::post('/{id}/restore', [App\Http\Controllers\HotelController::class, 'restore']);
      Route::post('/{id}/copy', [App\Http\Controllers\HotelController::class, 'copy']);
      Route::post('/{id}/toggle-active', [App\Http\Controllers\HotelController::class, 'toggleActive']);
  });

  // Курорты
  Route::prefix('resorts')->group(function () {
      Route::post('/', [App\Http\Controllers\ResortController::class, 'index']);
      Route::put('/', [App\Http\Controllers\ResortController::class, 'store']);
      Route::get('/{id}', [App\Http\Controllers\ResortController::class, 'show']);
      Route::put('/{id}', [App\Http\Controllers\ResortController::class, 'update']);
      Route::delete('/{id}', [App\Http\Controllers\ResortController::class, 'destroy']);
      Route::post('/{id}/restore', [App\Http\Controllers\ResortController::class, 'restore']);
      Route::post('/{id}/copy', [App\Http\Controllers\ResortController::class, 'copy']);
      Route::post('/{id}/toggle-active', [App\Http\Controllers\ResortController::class, 'toggleActive']);
  });

  // Туры
  Route::prefix('tours')->group(function () {
      Route::post('/', [App\Http\Controllers\TourController::class, 'index']);
      Route::put('/', [App\Http\Controllers\TourController::class, 'store']);
      Route::get('/direction/{directionId}', [App\Http\Controllers\TourController::class, 'searchByDirection']);
      Route::get('/{id}', [App\Http\Controllers\TourController::class, 'show']);
      Route::put('/{id}', [App\Http\Controllers\TourController::class, 'update']);
      Route::delete('/{id}', [App\Http\Controllers\TourController::class, 'destroy']);
      Route::post('/{id}/restore', [App\Http\Controllers\TourController::class, 'restore']);
      Route::post('/{id}/copy', [App\Http\Controllers\TourController::class, 'copy']);
      Route::post('/{id}/toggle-active', [App\Http\Controllers\TourController::class, 'toggleActive']);
  });

  // Экскурсии
  Route::prefix('excursions')->group(function () {
      Route::post('/', [App\Http\Controllers\ExcursionController::class, 'index']);
      Route::put('/', [App\Http\Controllers\ExcursionController::class, 'store']);
      Route::get('/direction/{directionId}', [App\Http\Controllers\ExcursionController::class, 'searchByDirection']);
      Route::get('/{id}', [App\Http\Controllers\ExcursionController::class, 'show']);
      Route::put('/{id}', [App\Http\Controllers\ExcursionController::class, 'update']);
      Route::delete('/{id}', [App\Http\Controllers\ExcursionController::class, 'destroy']);
      Route::post('/{id}/restore', [App\Http\Controllers\ExcursionController::class, 'restore']);
      Route::post('/{id}/copy', [App\Http\Controllers\ExcursionController::class, 'copy']);
      Route::post('/{id}/toggle-active', [App\Http\Controllers\ExcursionController::class, 'toggleActive']);
  });

  // Трансферы
  Route::prefix('transfers')->group(function () {
      Route::post('/', [App\Http\Controllers\TransferController::class, 'index']);
      Route::put('/', [App\Http\Controllers\TransferController::class, 'store']);
      Route::get('/type/{type}', [App\Http\Controllers\TransferController::class, 'searchByType']);
      Route::get('/{id}', [App\Http\Controllers\TransferController::class, 'show']);
      Route::put('/{id}', [App\Http\Controllers\TransferController::class, 'update']);
      Route::delete('/{id}', [App\Http\Controllers\TransferController::class, 'destroy']);
      Route::post('/{id}/restore', [App\Http\Controllers\TransferController::class, 'restore']);
      Route::post('/{id}/copy', [App\Http\Controllers\TransferController::class, 'copy']);
      Route::post('/{id}/toggle-active', [App\Http\Controllers\TransferController::class, 'toggleActive']);
  });

  
  // User Profile Controller routes
  Route::get('/user/profile', [UserProfileController::class, 'show']);
  Route::put('/user/profile', [UserProfileController::class, 'update']);
  
  // User Controller routes
  Route::resource('user', UserController::class)->except(['index']);
  Route::post('user', [UserController::class, 'index']);
  Route::post('user/{id}/toggle-active', [UserController::class, 'toggleActive']);

  //Translation
  Route::resource('translation', TranslationController::class);
  Route::post('translation/{id}/restore', [TranslationController::class, 'restore']);

});
