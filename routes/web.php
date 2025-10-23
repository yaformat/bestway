<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('asdasdasdlokijuhy777', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

// Route::get('/{any}', function () {
//     return view('admin.application');
// })->where('any', '^(?!api).*$');



Route::middleware([
    // 'web', 
    // 'set.domain'
])->group(function () {
    
    // Фронтенд (Blade) - корень сайта
    Route::get('/', function () {
        return view('front.index');
    })->name('home');

});

Route::prefix('admin')->group(function () {
    // Маршрут для корня админки
    Route::get('/', function () {
        return view('admin.application');
    });

    // Маршрут для всех вложенных путей
    Route::get('/{any}', function () {
        return view('admin.application');
    })->where('any', '^(?!api).*$');
});
