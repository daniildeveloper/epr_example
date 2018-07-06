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

        Route::group(['prefix' => 'password'], function () {
            Route::post('email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
            Route::post('reset', 'Auth\ResetPasswordController@reset');
        });

        Route::post('username-change', 'Api\UserController@changeUserName');

        // all routes specific for users. Get only after auth
        Route::group(['middleware' => 'jwt'], function () {
            Route::get('info', 'Api\UserController@getUserDetails');
            // Route::resource('/schedule', 'WorkersSheduleController');
            // Route::resource('/departament-block', 'WorkersBlockController');
            // Route::get('/departament-block/get-list/{year}/{month}', 'WorkersBlockController@indexByMonth');
            // Route::post('/departament-block/check-status', 'WorkersBlockController@checkDate');
            Route::get('roles', 'Api\UserController@getRoles')->middleware('role:owner');
        });
    });
    /**
     * END USER API GROUP
     */

    Route::group(['middleware' => 'jwt'], function () {
        Route::group(['prefix' => '/stock', 'middleware' => 'permission:show_stock_info'], function () {
            Route::resource('/framework', 'Api\Stock\FrameworkController');
            Route::resource('/rframework', 'Api\Stock\RestFrameworkController');
            Route::resource('/packaging', 'Api\Stock\PackagingController');
            Route::resource('/sticker', 'Api\Stock\StickerController');
            Route::resource('/ware', 'Api\Stock\WareController');
            Route::group(['/prefix' => 'data'], function () {
                Route::get('/ware', 'Api\Stock\DataController@getWareData');
            });

        });
        Route::group(['prefix' => '/stock', 'middleware' => 'permission:hide_wares'], function () {
            Route::get('/wareshow/hide/{id}', 'Api\Stock\WareController@hideVisible');
            Route::get('/wareshow/show/{id}', 'Api\Stock\WareController@showVisible');
        });

        Route::group(['prefix' => 'stock-data', 'middleware' => 'permission:show_stock_info'], function () {
            Route::get('/', 'Api\Stock\NomenclatureCreationDataControlller@get');
            Route::get('/frameworks', 'Api\Stock\NomenclatureCreationDataControlller@getFrameworks');
            Route::get('/rest-frameworks', 'Api\Stock\NomenclatureCreationDataControlller@getRestFrameworks');
            Route::get('/packagings', 'Api\Stock\NomenclatureCreationDataControlller@getPackagings');
            Route::get('/ware', 'Api\Stock\NomenclatureCreationDataControlller@getWares');
            Route::get('/stickers', 'Api\Stock\NomenclatureCreationDataControlller@getStickers');
        });

        Route::group(['prefix' => '/crud-nomenclatures', 'middleware' => 'permission:crud_nomenclatures'], function () {
            Route::group(['prefix' => '/manipulations'], function () {
                Route::post('store', 'Api\Stock\RestFrameworkmanipulationsController@store');
                Route::get('/latest/{framework_id}', 'Api\Stock\RestFrameworkmanipulationsController@getLatest');
                Route::get('/supplies-plan', 'Api\Stock\StickersAndPackagingsManipulationController@suppliesPlan');
                Route::post('/packagingBy', 'Api\Stock\StickersAndPackagingsManipulationController@confirmPackagingsSupply');
                Route::post('/packagingDecline', 'Api\Stock\StickersAndPackagingsManipulationController@declinePackagingsSupply');
                Route::post('/stickerBy', 'Api\Stock\StickersAndPackagingsManipulationController@confirmStickersSupply');
                Route::post('/stickerDecline', 'Api\Stock\StickersAndPackagingsManipulationController@declineStickersSupply');
            });
        });

        Route::group(['prefix' => 'order-change', 'middleware' => 'permission:change_wares_order'], function () {
            Route::get('/', 'WareOrderController@index');
            Route::post('/', 'WareOrderController@store');
        });
    });

    Route::group(['middleware' => ['permission:invite_users']], function () {
        Route::post('/user-invite', 'Api\UserController@inviteusers');
    });
});
