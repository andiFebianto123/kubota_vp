<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Route::get('two-factor', 'Auth\TwoFactorController@index')->name("twofactor");
Route::get('admin/logout', 'Auth\LoginController@logout')->name("logout");
Route::get('admin/login', 'Auth\LoginController@index')->name("rectmedia.auth.login");
Route::post('admin/login', 'Auth\LoginController@authenticate')->name("rectmedia.auth.authenticate");
// Route::post('authenticate', 'Auth\LoginController@authenticate')->name("rectmedia.auth.login");
Route::get('two-factor', 'Auth\TwoFactorController@index')->name("twofactor");
Route::post('two-factor-update', 'Auth\TwoFactorController@update')->name("twofactor.update");

Route::get('/', function () {
    return redirect()->to('admin');
});

