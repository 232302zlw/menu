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

Route::get('/', function () {
    return view('welcome');
});


Route::prefix('wechat')->namespace('wechat')->group(function(){
    Route::get('create_menu','MenuController@create_menu');     // 创建菜单视图
    Route::post('save_menu','MenuController@save_menu');        // 创建菜单入库
    Route::get('file_menu','MenuController@file_menu');         // 创建菜单入库
    Route::get('get_access_token','MenuController@get_access_token');
});
