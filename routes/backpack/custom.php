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
    Route::crud('configuration', 'ConfigurationCrudController');
    Route::crud('temp-upload-delivery', 'TempUploadDeliveryCrudController');
    Route::post('purchase-order-mass-read', 'PurchaseOrderCrudController@massRead');
    Route::post('purchase-order-import-ds', 'PurchaseOrderCrudController@importDs');
    Route::get('purchase-order-export-excel', 'PurchaseOrderCrudController@exportExcel');
    Route::get('purchase-order-line-export-excel-accept', 'PurchaseOrderLineCrudController@exportExcelAccept');
    Route::get('purchase-order-line-export-pdf-accept', 'PurchaseOrderLineCrudController@exportPdfAccept');
    Route::get('purchase-order-line/{id}/unread', 'PurchaseOrderLineCrudController@unread');
    Route::post('temp-upload-delivery/insert-to-db', 'TempUploadDeliveryCrudController@insertToDb');
    Route::post('temp-upload-delivery/cancel-to-db', 'TempUploadDeliveryCrudController@cancelToDb');

});