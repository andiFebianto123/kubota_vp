<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => ['web', 'twofactor'],
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('purchase-order', 'PurchaseOrderCrudController');
    Route::crud('vendor', 'VendorCrudController');
    Route::get('vendor-export', 'VendorCrudController@exportAdvance');
    Route::crud('user', 'UserCrudController');
    Route::crud('general-message', 'GeneralMessageCrudController');
    Route::get('dashboard', 'DashboardController@index');
    Route::crud('delivery', 'DeliveryCrudController');
    Route::get('delivery-detail/{ds_num}/{ds_line}', 'DeliveryCrudController@deliveryDetail');
    Route::get('delivery-export-pdf-single-ds', 'DeliveryCrudController@exportPdfSingleDs');
    Route::post('delivery-export-pdf-mass-label-post', 'DeliveryCrudController@exportPdfMassLabelPost');
    Route::post('delivery-export-pdf-mass-ds-post', 'DeliveryCrudController@exportPdfMassDsPost');
    Route::get('delivery-sheet-export', 'DeliveryCrudController@exportAdvance');

    Route::get('order-sheet-export-pdf/{po_num}', 'PurchaseOrderCrudController@exportPdfOrderSheet');
    Route::get('order-sheet-export-excel/{po_num}', 'PurchaseOrderCrudController@exportExcelOrderSheet');
    Route::get('template-serial-numbers', 'DeliveryCrudController@exportTemplateSerialNumber');
    Route::post('serial-number-import', 'DeliveryCrudController@importSn');

    Route::crud('forecast', 'ForecastCrudController');
    Route::crud('purchase-order-line', 'PurchaseOrderLineCrudController');
    Route::crud('configuration', 'ConfigurationCrudController');

    Route::group(['prefix' => 'purchase-order'], function(){
        Route::crud('temp-upload-delivery', 'TempUploadDeliveryCrudController');
    });


    Route::post('purchase-order-mass-read', 'PurchaseOrderCrudController@massRead');
    Route::post('purchase-order-import-ds', 'PurchaseOrderCrudController@importDs');
    Route::post('purchase-order-accept-po-line', 'PurchaseOrderCrudController@acceptPoLine');
    Route::post('purchase-order-reject-po-line', 'PurchaseOrderCrudController@rejectPoLine');
    Route::get('purchase-order-export-excel', 'PurchaseOrderCrudController@exportExcel');
    Route::get('purchase-order-export', 'PurchaseOrderCrudController@exportAdvance');

    Route::get('purchase-order-line-export-excel-accept', 'PurchaseOrderLineCrudController@exportExcelAccept');
    Route::get('purchase-order-line-export-pdf-accept', 'PurchaseOrderLineCrudController@exportPdfAccept');
    Route::get('purchase-order-line/{id}/unread', 'PurchaseOrderLineCrudController@unread');
    Route::get('purchase-order/{id}/{line}/detail-change', 'PurchaseOrderCrudController@detailChange');
    Route::post('send-mail-new-po', 'PurchaseOrderCrudController@sendMailNewPo');
    Route::get('purchase-order/check-existing-temp', 'PurchaseOrderCrudController@checkExistingTemp');

    Route::post('temp-upload-delivery/insert-to-db', 'TempUploadDeliveryCrudController@insertToDb');
    Route::post('temp-upload-delivery/print-insert-to-db', 'TempUploadDeliveryCrudController@printInsertToDb');
    Route::post('temp-upload-delivery/cancel-to-db', 'TempUploadDeliveryCrudController@cancelToDb');
    Route::post('template-mass-ds', 'PurchaseOrderCrudController@templateMassDs');

    Route::crud('delivery-status', 'DeliveryStatusCrudController');
    Route::get('delivery-statuses-export', 'DeliveryStatusCrudController@exportAdvance');
    Route::crud('delivery-serial', 'DeliverySerialCrudController');
    Route::get('validate-ds-po', 'DeliveryCrudController@addOnValidatePo');
    // route untuk accept all PO
    Route::get('accept-all-po', 'PurchaseOrderCrudController@acceptAllPo');
    // route untuk ajax filter di nomor item di po
    Route::get('filter-po/ajax-itempo-options', 'PurchaseOrderCrudController@itemPoOptions');
    // route untuk ajax filter vendor untuk mendapatkan kode vendor
    Route::get('filter-vendor/ajax-itempo-options', 'VendorCrudController@itemVendorOptions');
    Route::get('filter-vendor/ajax-itempo-options2', 'VendorCrudController@itemVendorOptions2');

    Route::get('delivery/{id}/print_label', 'PurchaseOrderLineCrudController@exportPdfLabelInstant');
    Route::get('delivery-print-label', 'PurchaseOrderLineCrudController@exportPdfLabel');
    Route::post('delivery-print-label-post', 'PurchaseOrderLineCrudController@exportPdfLabelPost');

    Route::crud('material-outhouse', 'MaterialOuthouseCrudController');
    Route::crud('material-outhouse-summary-per-item', 'MaterialOuthouseSummaryPerItemCrudController');
    Route::get('mo-item-export', 'MaterialOuthouseSummaryPerItemCrudController@exportAdvance');

    Route::crud('material-outhouse-summary-per-po', 'MaterialOuthouseSummaryPerPoCrudController');
    Route::get('material-outhouse-summary-per-po/{id}/details', 'MaterialOuthouseSummaryPerPoCrudController@showDetailsRow');
    Route::get('mo-po-export', 'MaterialOuthouseSummaryPerPoCrudController@exportAdvance');

    Route::crud('role', 'RoleCrudController');
    Route::crud('permission', 'PermissionCrudController');

    Route::post('role/get-role-permission', 'RoleCrudController@getPermissionOfRole');
    Route::post('role/change-role-permission', 'RoleCrudController@changeRolePermission');
    Route::get('role/show-role-permission', 'RoleCrudController@showPermission');
    Route::crud('tax-invoice', 'TaxInvoiceCrudController');
    Route::get('export-tax-invoice', 'TaxInvoiceCrudController@exportAdvanceTop');
    Route::get('export-tax-history-invoice', 'TaxInvoiceCrudController@exportAdvanceBottom');
    
    Route::get('export-db', 'ConfigurationCrudController@exportDb');
    Route::get('confirm-faktur-pajak/{id}', 'TaxInvoiceCrudController@confirmFakturPajak');
    Route::post('confirm-reject-faktur-pajak/{id}', 'TaxInvoiceCrudController@confirmRejectFakturPajak');
    Route::get('tax-invoice/ajax-delivery-status', 'TaxInvoiceCrudController@ajaxDeliveryStatus');

    Route::post('get-comments', 'TaxInvoiceCrudController@showComments');
    Route::post('send-comments', 'TaxInvoiceCrudController@sendMessage');
    Route::post('delete-comments', 'TaxInvoiceCrudController@deleteMessage');
    Route::get('forecast/export', 'ForecastCrudController@export');

    Route::post('tax-invoice/search2', [
        'uses'      => 'TaxInvoiceCrudController@search2',
        'operation' => 'list',
    ]);
    
    Route::crud('history-mo-summary-per-po', 'HistoryMoSummaryPerPoCrudController');
    Route::get('history-mo-summary-per-po/{id}/details', 'HistoryMoSummaryPerPoCrudController@showDetailsRow');
    Route::get('history-mo-po-export', 'HistoryMoSummaryPerPoCrudController@exportAdvance');
    Route::crud('history-mo-summary-per-item', 'HistoryMoSummaryPerItemCrudController');
    Route::get('history-mo-item-export', 'HistoryMoSummaryPerItemCrudController@exportAdvance');
    Route::get('template-users', 'UserCrudController@templateUsers');
    Route::post('user-import', 'UserCrudController@import');
    Route::get('user-export', 'UserCrudController@exportAdvance');

    Route::get('active-inactive/{id}', 'UserCrudController@activeInactive');

});