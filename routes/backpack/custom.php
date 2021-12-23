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
    Route::get('delivery-export-pdf', 'DeliveryCrudController@exportPdf');
    Route::get('delivery-export-mass-pdf', 'DeliveryCrudController@exportMassPdf');
    Route::post('delivery-export-mass-pdf-post', 'DeliveryCrudController@exportMassPdfPost');
    Route::post('delivery-export-mass-pdf-post2', 'DeliveryCrudController@exportMassPdfPost2');
    Route::get('order-sheet-export-pdf/{po_num}', 'PurchaseOrderCrudController@exportPdfOrderSheet');
    Route::get('order-sheet-export-excel/{po_num}', 'PurchaseOrderCrudController@exportExcelOrderSheet');
    Route::get('template-serial-numbers', 'DeliveryCrudController@exportTemplateSerialNumber');
    Route::post('serial-number-import', 'DeliveryCrudController@importSn');

    Route::crud('forecast', 'ForecastCrudController');
    Route::crud('purchase-order-line', 'PurchaseOrderLineCrudController');
    Route::crud('configuration', 'ConfigurationCrudController');
    Route::crud('temp-upload-delivery', 'TempUploadDeliveryCrudController');
    Route::post('purchase-order-mass-read', 'PurchaseOrderCrudController@massRead');
    Route::post('purchase-order-import-ds', 'PurchaseOrderCrudController@importDs');
    Route::post('purchase-order-accept-po-line', 'PurchaseOrderCrudController@acceptPoLine');
    Route::post('purchase-order-reject-po-line', 'PurchaseOrderCrudController@rejectPoLine');
    Route::get('purchase-order-export-excel', 'PurchaseOrderCrudController@exportExcel');
    Route::get('purchase-order-line-export-excel-accept', 'PurchaseOrderLineCrudController@exportExcelAccept');
    Route::get('purchase-order-line-export-pdf-accept', 'PurchaseOrderLineCrudController@exportPdfAccept');
    Route::get('purchase-order-line/{id}/unread', 'PurchaseOrderLineCrudController@unread');
    Route::get('purchase-order/{id}/{line}/detail-change', 'PurchaseOrderCrudController@detailChange');
    Route::post('temp-upload-delivery/insert-to-db', 'TempUploadDeliveryCrudController@insertToDb');
    Route::post('temp-upload-delivery/print-insert-to-db', 'TempUploadDeliveryCrudController@printInsertToDb');
    Route::post('temp-upload-delivery/cancel-to-db', 'TempUploadDeliveryCrudController@cancelToDb');
    Route::get('template-mass-ds', 'PurchaseOrderCrudController@templateMassDs');

    Route::crud('delivery-status', 'DeliveryStatusCrudController');
    Route::crud('delivery-serial', 'DeliverySerialCrudController');
    Route::get('validate-ds-po', 'DeliveryCrudController@addOnValidatePo');
    // route untuk accept all PO
    Route::get('accept-all-po', 'PurchaseOrderCrudController@accept_all_po');
    // route untuk ajax filter di nomor item di po
    Route::get('test/ajax-itempo-options', 'PurchaseOrderCrudController@itemPoOptions');
    // route untuk ajax filter vendor untuk mendapatkan kode vendor
    Route::get('test/ajax-vendor-options', 'VendorCrudController@itemVendorOptions');

    // Route untuk export PDF print label delivery sheet detail
    // Route::get('delivery/{id}/print_label', 'PurchaseOrderLineCrudController@exportPdfLabelSingle');
    Route::get('delivery/{id}/print_label', 'PurchaseOrderLineCrudController@exportPdfLabelInstant');
    Route::get('delivery-print-label', 'PurchaseOrderLineCrudController@exportPdfLabel');
    Route::post('delivery-print-label-post', 'PurchaseOrderLineCrudController@exportPdfLabelPost');

    Route::crud('material-outhouse', 'MaterialOuthouseCrudController');
    Route::crud('material-outhouse-summary', 'MaterialOuthouseSummaryCrudController');
    Route::crud('role', 'RoleCrudController');
    Route::crud('permission', 'PermissionCrudController');

    Route::post('role/get-role-permission', 'RoleCrudController@getPermissionOfRole');
    Route::post('role/change-role-permission', 'RoleCrudController@changeRolePermission');
    Route::get('role/show-role-permission', 'RoleCrudController@showPermission');
    Route::crud('tax-invoice', 'TaxInvoiceCrudController');
    
    Route::get('export-db', 'ConfigurationCrudController@exportDb');
    Route::get('confirm-faktur-pajak/{id}', 'TaxInvoiceCrudController@confirmFakturPajak');
    Route::get('confirm-reject-faktur-pajak/{id}', 'TaxInvoiceCrudController@confirmRejectFakturPajak');
    Route::post('get-comments', 'TaxInvoiceCrudController@showComments');
    Route::post('send-comments', 'TaxInvoiceCrudController@sendMessage');
    Route::post('delete-comments', 'TaxInvoiceCrudController@deleteMessage');
});