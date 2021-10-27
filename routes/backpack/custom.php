<?php

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => ['web', 'twofactor'],
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('tag', 'TagCrudController');
    Route::crud('purchase-order', 'PurchaseOrderCrudController');
    Route::crud('vendor', 'VendorCrudController');
    Route::crud('user', 'UserCrudController');
    Route::crud('general-message', 'GeneralMessageCrudController');
    Route::get('dashboard', 'DashboardController@index');
    Route::crud('delivery', 'DeliveryCrudController');
    Route::crud('forecast', 'ForecastCrudController');
    Route::crud('purchase-order-line', 'PurchaseOrderLineCrudController');
});
// });