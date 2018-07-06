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
    return view('index');
});

Route::get('test', 'TestController@test');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::any('adminer', '\Miroc\LaravelAdminer\AdminerController@index');

Route::get('/register/{link}', 'UserController@renderUserRegisterForm');
Route::post('/registerme', 'UserController@setUserDetailsFromInviteLink')->name('register');

Route::group(['prefix' => 'api'], function () {

    /**
     * USER API GROUP
     */
    Route::group(['prefix' => 'user'], function () {
        Route::post('login', 'Api\UserController@login');

        Route::post('username-change', 'Api\UserController@changeUserName');

        Route::group(['prefix' => 'password'], function () {
            Route::post('email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
            Route::post('reset', 'Auth\ResetPasswordController@reset');
        });

        // Route::get('messages', 'ChatsController@fetchMessages');
        // Route::post('messages', 'ChatsController@sendMessage');

        // all routes specific for users. Get only after auth
        Route::group(['middleware' => 'jwt'], function () {
            Route::get('info', 'Api\UserController@getUserDetails');
            Route::resource('/schedule', 'WorkersSheduleController');
            Route::resource('/departament-block', 'WorkersBlockController');
            Route::get('/departament-block/get-list/{year}/{month}', 'WorkersBlockController@indexByMonth');
            Route::post('/departament-block/check-status', 'WorkersBlockController@checkDate');
            Route::get('roles', 'Api\UserController@getRoles')->middleware('role:owner');
        });
    });
    /**
     * END USER API GROUP
     */

    Route::group(['middleware' => ['permission:invite_users']], function () {
        Route::post('/user-invite', 'Api\UserController@inviteusers');
    });
});
