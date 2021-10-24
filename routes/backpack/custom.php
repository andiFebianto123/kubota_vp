<?php

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => ['web', 'twofactor'],
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('tag', 'TagCrudController');
    Route::crud('purchase-order', 'PurchaseOrderCrudController');
    Route::crud('vendor', 'VendorCrudController');
});
// Route::group(['middleware' => 'web', 'prefix' => config('backpack.base.route_prefix', 'admin'), 'namespace' => 'Backpack\Base\app\Http\Controllers'], function () {
//     // Route::auth();
//     Route::get('logout', 'Auth\LoginController@logout');
//     // Route::get('dashboard', 'AdminController@dashboard');
//     // Route::get('/', 'AdminController@redirect');
// });
