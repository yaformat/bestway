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

Route::get('/{any}', function () {
    return view('admin.application');
})->where('any', '^(?!api).*$');



// Middleware для проверки домена
// Route::middleware([
//     // 'web', 
//     // 'set.domain'
// ])->group(function () {
    
//     // Фронтенд (Blade) - корень сайта
//     Route::get('/', function () {
//         return view('front.index');
//     })->name('home');
//     // Фронтенд (Blade) - корень сайта
//     Route::get('/admin', function () {
//         return view('admin.application');
//     })->name('admin');

// });

// Route::prefix('admin')->middleware([
//     // 'web', 
//     // 'set.domain', 
//     // 'auth'
// ])->group(function () {

//     Route::get('/{any}', function () {
//         return view('admin.application');
//     })->where('any', '^(?!api).*$');

// });