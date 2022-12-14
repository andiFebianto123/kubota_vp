@inject('constant', 'App\Helpers\Constant')
@extends(backpack_view('blank'))

@php
$defaultBreadcrumbs = [
trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
$crud->entity_name_plural => url($crud->route),
trans('backpack::crud.preview') => false,
];
$action = 'create';

// if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
$breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
<section class="container-fluid d-print-none">
    <a href="javascript: window.print();" class="btn float-right"><i class="la la-print"></i></a>
    <h2>
        <span class="text-capitalize">{{$entry->po_num}}-{{$entry->po_line}}</span>
        <small>Preview</small>
        @if ($crud->hasAccess('list'))
        <small class=""><a href="javascript:history.back()" class="font-sm"><i class="la la-angle-double-left"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></a></small>
        @endif
    </h2>
</section>
@endsection

@section('content')
<div class="row">
    <div class="{{ $crud->getShowContentClass() }}">
        <!-- Default box -->
        <div class="">
            @if ($crud->model->translationEnabled())
            <div class="row">
                <div class="col-md-12 mb-2">
                    <!-- Change translation button group -->
                    <div class="btn-group float-right">
                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            {{trans('backpack::crud.language')}}: {{ $crud->model->getAvailableLocales()[request()->input('locale')?request()->input('locale'):App::getLocale()] }} &nbsp; <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            @foreach ($crud->model->getAvailableLocales() as $key => $locale)
                            <a class="dropdown-item" href="{{ url($crud->route.'/'.$entry->getKey().'/show') }}?locale={{ $key }}">{{ $locale }}</a>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="col-md-8">
        <div class="card-header bg-secondary">
            <label class="font-weight-bold mb-0">Detail</label> 
        </div>
        <div class="card no-padding no-border">
            <table class="table">
                <tr>
                    <td>PO</td>
                    <td width="2px">:</td>
                    <td>{{$entry->po_num}}</td>
                </tr>
                <tr>
                    <td>PO Line</td>
                    <td>:</td>
                    <td>{{$entry->po_line}}</td>
                </tr>
                <tr>
                    <td>Item</td>
                    <td>:</td>
                    <td>{{$entry->item}}<br>{{$entry->description}}</td>
                </tr>
                <tr>
                    <td>Qty Order</td>
                    <td>:</td>
                    <td>{{$entry->order_qty}}</td>
                </tr>
                <tr>
                    <td>Due Date</td>
                    <td>:</td>
                    <td>{{date('Y-m-d', strtotime($entry->due_date))}}</td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>:</td>
                    <td>    <span class="{{$arr_po_line_status[$entry->status]['color']}}">
                            {{$arr_po_line_status[$entry->status]['text']}}
                            </span>
                    </td>
                </tr>
                @if($constant::checkPermission('Show Price In PO Menu'))
                <tr>
                    <td>Unit Price</td>
                    <td>:</td>
                    <td>@if($entry->purchaseOrder->vendor){{$entry->purchaseOrder->vendor->currency}} {{number_format($entry->unit_price,0,',','.')}}@endif</td>
                </tr>
                @endif
            </table>
        </div><!-- /.box-body -->
    </div><!-- /.box -->

    <div class="col-md-8">
        @if($constant::checkPermission('Create Delivery Sheet'))
        <div class="card-header bg-secondary">
            <label class="font-weight-bold mb-0">Create Delivery Sheet</label> 
        </div>
        <div class="card no-padding no-border">
                @if(sizeof($unfinished_po_line['datas']) > 0)
                <div class="m-4 p-2" style="border:1px solid #ff9800; color:#ff9800;">
                    <b> PO Line yang belum selesai:</b><br>
                    @foreach($unfinished_po_line['datas'] as $key => $upl)
                        {{$key+1}}. {{$upl->po_num."-".$upl->po_line}} ({{date('Y-m-d',strtotime($upl->due_date))}}) {{($upl->total_shipped_qty)?$upl->total_shipped_qty:"0"}}/{{$upl->order_qty}}<br>
                    @endforeach
                </div>
                @endif
                <form id="form-delivery" method="post"
                        action="{{ url('delivery') }}"
                        @if ($crud->hasUploadFields('create'))
                        enctype="multipart/form-data"
                        @endif
                        >
                    {!! csrf_field() !!}
                    <!-- load the view from the application if it exists, otherwise load the one in the package -->
                    <div class="m-2">
                    @include('crud::inc.show_fields', ['fields' => $crud->fields()])
                    </div>

                    @if(sizeof($unfinished_po_line['datas']) > 0)
                    <button id="btn-for-form-delivery" class="btn btn-sm btn-primary-vp mx-4 mb-4 mt-0" data-toggle="modal" data-target="#modalAlertDueDate" type="button">Submit</button>
                    @else
                    <button id="btn-for-form-delivery" class="btn btn-sm btn-primary-vp mx-4 mb-4 mt-0"  type="button" onclick="submitNewDs()">Submit</button>
                    @endif
                </form>
        </div>
        @endif
    </div>

    <div class="col-md-12">
        @if($constant::checkPermission('Read PO Line Detail'))
        <div class="card">
            <div class="card-header bg-secondary">
               <label class="font-weight-bold mb-0">Delivery Sheet Detail</label> 
            </div>
            <div class="card-body">
                @if(sizeof($deliveries) > 0)
                    <table id="ds-table" class="table table-striped mb-0 table-responsive">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="check-all-cb" name="print_deliveries" class="check-all" data-delivery="{{sizeof($deliveries)}}" >
                                </th>
                                <th>PO</th>
                                <th>DS Number</th>
                                <th>DS Line</th>
                                <th>Group DS</th>
                                <th>Shipped Date</th>
                                <th>Qty</th>
                                @if($constant::checkPermission('Show Price In PO Menu'))
                                <th>Amount ({{$entry->currency}})</th>
                                <th>Total ({{$entry->currency}})</th>
                                @endif
                                <th>DO Number</th>
                                <th>Operator</th>
                                <th>Ref DS Num</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $total_price = 0;
                                $total_amount = 0;
                                $total_qty = 0;
                            @endphp
                            @foreach ($deliveries as $key => $delivery)
                            <tr>
                                <td>
                                    <input type="checkbox" value="{{$delivery->id}}" name="print_delivery[]" class="check-delivery check-{{$delivery->id}}">
                                </td>
                                <td style="white-space: nowrap;">{{$delivery->po_num}}-{{$delivery->po_line}}</td>
                                <td>{{$delivery->ds_num}}</td>
                                <td>{{$delivery->ds_line}}</td>
                                <td>{{$delivery->group_ds_num}}</td>
                                <td>{{date('Y-m-d',strtotime($delivery->shipped_date))}}</td>
                                <td>{{$delivery->shipped_qty}}</td>
                                @if($constant::checkPermission('Show Price In PO Menu'))
                                <td>{{number_format($delivery->unit_price,0,',','.')}}</td>
                                <td>{{number_format($delivery->shipped_qty*$delivery->unit_price,0,',','.')}}</td>
                                @endif
                                <td>{{$delivery->no_surat_jalan_vendor}}</td>
                                <td>{{$delivery->petugas_vendor}}</td>
                                <td style="white-space: nowrap;">
                                    <a href="{{url('delivery-detail').'/'.$delivery->ref_ds_num.'/'.$delivery->ref_ds_line}}" class='btn-link'>
                                        {{$delivery->ref_ds_num}}-{{$delivery->ref_ds_line}}
                                    </a>
                                </td>
                                <td style="white-space: nowrap;">
                                    <!-- <a href="#" class="btn btn-sm btn-danger"><i class="la la-file-pdf"></i> + Harga</a>
                                    <a href="#" class="btn btn-sm btn-secondary"><i class="la la-file-pdf"></i> - Harga</a> -->
                                    <a href="{{url('delivery-detail/'.$delivery->ds_num.'/'.$delivery->ds_line)}}" class="btn btn-sm btn-outline-primary" data-toggle='tooltip' data-placement='top' title="DS Detail"><i class="la la-qrcode"></i></a>
                                    @if($constant::checkPermission('Print Label Delivery Sheet'))
                                    <button type="button" id="btn-for-form-print-label-{{$delivery->id}}" class="btn btn-sm btn-outline-primary" onclick="printLabelInstant('{{$delivery->id}}')"" data-toggle='tooltip'  data-placement='top' title="Print Label"><i class="la la-tag"></i></button>
                                    @endif
                                    @if($constant::checkPermission('Delete Delivery Sheet'))
                                    <a href="javascript:void(0)" onclick="deleteEntry(this)" data-route="{{ url('delivery/'.$delivery->id) }}" class="btn btn-sm btn-outline-danger" data-toggle='tooltip' data-placement='top' data-button-type="delete" title="Delete"><i class="la la-trash"></i></a>
                                    @endif
                                </td>
                            </tr>
                            @php
                                $total_qty += $delivery->shipped_qty;
                                $total_amount += $delivery->unit_price;
                                $total_price += $delivery->unit_price*$delivery->shipped_qty;
                            @endphp
                            @endforeach

                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-center font-weight-bold">
                                    Total
                                </td>
                                <td>
                                    {{$total_qty}}
                                </td>
                                @if($constant::checkPermission('Show Price In PO Menu'))
                                <td>
                                 {{-- number_format($total_amount,0,',','.') --}}
                                </td>
                                <td>
                                 {{ number_format($total_price,0,',','.')}}
                                </td>
                                @endif
                                <td colspan='4'></td>
                            </tr>
                        </tfoot>
                    </table>
                    @if($constant::checkPermission('Print Label Delivery Sheet'))
                    <button type="button" id="btn-for-form-print-label" class="btn btn-sm btn-danger" onclick="printLabel()"><i class="la la-file-pdf"></i> <span>PDF Label</span></button>
                    @endif
                    <button type="button" id="btn-for-form-print-mass-ds" class="btn btn-sm btn-danger" onclick="printMassDs()"><i class="la la-file-pdf"></i> <span>PDF DS</span></button>
                   
                @else
                <p>No Data Available</p>
                @endif
            </div>

        </div><!-- /.box-body -->
        @endif
    </div>


    <div class="col-md-12">
        @if($constant::checkPermission('Read PO Line Detail'))
        <div class="card">
            <div class="card-header bg-secondary">
               <label class="font-weight-bold mb-0">Delivery Sheet Detail (Repair)</label> 
            </div>
            <div class="card-body">
                @if(sizeof($delivery_repairs) > 0)
                    <table id="ds-table-repair" class="table table-striped mb-0 table-responsive">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="check-all-cb-repair" name="print_deliverie_repairs" class="check-all-repair" data-delivery="{{sizeof($delivery_repairs)}}" >
                                </th>
                                <th>PO</th>
                                <th>DS Number</th>
                                <th>DS Line</th>
                                <th>Group DS</th>
                                <th>Shipped Date</th>
                                <th>Qty</th>
                                @if($constant::checkPermission('Show Price In PO Menu'))
                                <th>Amount ({{$entry->currency}})</th>
                                <th>Total ({{$entry->currency}})</th>
                                @endif
                                <th>DO Number</th>
                                <th>Operator</th>
                                <th>Ref DS Num</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $total_price = 0;
                                $total_amount = 0;
                                $total_qty = 0;
                            @endphp
                            @foreach ($delivery_repairs as $key => $delivery_repair)
                            <tr>
                                <td>
                                    <input type="checkbox" value="{{$delivery_repair->id}}" name="print_delivery_repair[]" class="check-delivery-repair check-repair-{{$delivery_repair->id}}">
                                </td>
                                <td style="white-space: nowrap;">{{$delivery_repair->po_num}}-{{$delivery_repair->po_line}}</td>
                                <td>{{$delivery_repair->ds_num}}</td>
                                <td>{{$delivery_repair->ds_line}}</td>
                                <td>{{$delivery_repair->group_ds_num}}</td>
                                <td>{{date('Y-m-d',strtotime($delivery_repair->shipped_date))}}</td>
                                <td>{{$delivery_repair->shipped_qty}}</td>
                                @if($constant::checkPermission('Show Price In PO Menu'))
                                <td>{{number_format($delivery_repair->unit_price,0,',','.')}}</td>
                                <td>{{number_format($delivery_repair->shipped_qty*$delivery_repair->unit_price,0,',','.')}}</td>
                                @endif
                                <td>{{$delivery_repair->no_surat_jalan_vendor}}</td>
                                <td>{{$delivery_repair->petugas_vendor}}</td>
                                <td style="white-space: nowrap;">
                                    <a href="{{url('delivery-detail').'/'.$delivery_repair->ref_ds_num.'/'.$delivery_repair->ref_ds_line}}" class='btn-link'>
                                        {{$delivery_repair->ref_ds_num}}-{{$delivery_repair->ref_ds_line}}
                                    </a>
                                </td>
                                <td style="white-space: nowrap;">
                                    <a href="{{url('delivery-detail/'.$delivery_repair->ds_num.'/'.$delivery_repair->ds_line)}}" class="btn btn-sm btn-outline-primary" data-toggle='tooltip' data-placement='top' title="DS Detail"><i class="la la-qrcode"></i></a>
                                    @if($constant::checkPermission('Print Label Delivery Sheet'))
                                    <button type="button" id="btn-for-form-print-label-{{$delivery_repair->id}}" class="btn btn-sm btn-outline-primary" onclick="printLabelInstant('{{$delivery_repair->id}}')"" data-toggle='tooltip'  data-placement='top' title="Print Label"><i class="la la-tag"></i></button>
                                    @endif
                                    @if($constant::checkPermission('Delete Delivery Sheet'))
                                    <a href="javascript:void(0)" onclick="deleteEntry(this)" data-route="{{ url('delivery/'.$delivery_repair->id) }}" class="btn btn-sm btn-outline-danger" data-toggle='tooltip' data-placement='top' data-button-type="delete" title="Delete"><i class="la la-trash"></i></a>
                                    @endif
                                </td>
                            </tr>
                            @php
                                $total_qty += $delivery_repair->shipped_qty;
                                $total_amount += $delivery_repair->unit_price;
                                $total_price += $delivery_repair->unit_price*$delivery_repair->shipped_qty;
                            @endphp
                            @endforeach

                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-center font-weight-bold">
                                    Total
                                </td>
                                <td>
                                    {{$total_qty}}
                                </td>
                                @if($constant::checkPermission('Show Price In PO Menu'))
                                <td>
                                 {{-- number_format($total_amount,0,',','.') --}}
                                </td>
                                <td>
                                 {{ number_format($total_price,0,',','.')}}
                                </td>
                                @endif
                                <td colspan='4'></td>
                            </tr>
                        </tfoot>
                    </table>
                    @if($constant::checkPermission('Print Label Delivery Sheet'))
                    <button type="button" id="btn-for-form-print-label-repair" class="btn btn-sm btn-danger" onclick="printLabelRepair()"><i class="la la-file-pdf"></i> <span>PDF Label</span></button>
                    @endif
                    <button type="button" id="btn-for-form-print-mass-ds-repair" class="btn btn-sm btn-danger" onclick="printMassDsRepair()"><i class="la la-file-pdf"></i> <span>PDF DS</span></button>
                @else
                <p>No Data Available</p>
                @endif
            </div>

        </div><!-- /.box-body -->
        @endif
    </div>
</div>

@endsection


@section('after_styles')
<link rel="stylesheet" type="text/css" href="{{asset('packages/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{asset('packages/datatables.net-fixedheader-bs4/css/fixedHeader.bootstrap4.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{asset('packages/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}">

<link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/crud.css').'?v='.config('backpack.base.cachebusting_string') }}">
<link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/show.css').'?v='.config('backpack.base.cachebusting_string') }}">
<link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/form.css').'?v='.config('backpack.base.cachebusting_string') }}">
<link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/create.css').'?v='.config('backpack.base.cachebusting_string') }}">

@stack('crud_fields_styles')
    <style>
      .form-group.required label:not(:empty):not(.form-check-label)::after {
        content: '';
      }
      .form-group.required > label:not(:empty):not(.form-check-label)::after {
        content: ' *';
        color: #ff0000;
      }
    </style>
@endsection

@section('after_scripts')

<script type="text/javascript" src="{{ asset('packages/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('packages/datatables.net-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('packages/datatables.net-responsive/js/dataTables.responsive.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('packages/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('packages/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('packages/datatables.net-fixedheader-bs4/js/fixedHeader.bootstrap4.min.js')}}"></script>

<script src="{{ asset('packages/backpack/crud/js/crud.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
<script src="{{ asset('packages/backpack/crud/js/show.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
<script src="{{ asset('packages/backpack/crud/js/form.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
<script src="{{ asset('packages/backpack/crud/js/create.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
<div id="modalWarningQty" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Peringatan!</h5>
        </div>
        <div class="modal-body">
            <p class="text-accept">
               Apakah Anda yakin akan melanjutkan pembuatan DS?
               <p class="list-error"></p>
            </p>
            <div class="mt-4 text-right">
                <button type="button" class="btn btn-sm btn-outline-vp-primary" onclick="submitAfterValid('form-delivery')">Ya</button>
                <button type="button" class="btn btn-sm btn-outline-danger" data-dismiss="modal">Tidak</button>
            </div>
        </div>
    </div>
  </div>
</div>
<div id="modalAlertDueDate" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title text-danger">Peringatan!</h5>
        </div>
        <div class="modal-body">
            <p class="text-accept">
                {{$unfinished_po_line['message']}}
            </p>
            <button type="button" class="btn btn-sm btn-outline-danger" data-dismiss="modal">Tutup</button>
        </div>
    </div>
  </div>
</div>
@stack('crud_fields_scripts')
<script>
    var urlMassDs = "{{url('delivery-export-pdf-mass-ds-post')}}"
    var urlPrintLabel = "{{url('delivery-export-pdf-mass-label-post')}}"

    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
    $(document).ready( function () {
        $('#ds-table').DataTable();
        $('#ds-table-repair').DataTable();
        initializeFieldsWithJavascript('form');
    } );

    function submitNewDs(){
        var showModal = false
        var htmlErrorMsg = ""
        if($('#current-qty').val() > $('#current-qty').data('max')){
            showModal = true
            htmlErrorMsg += "<li>[DS] Jumlah Qty melebihi batas (max. "+$('#current-qty').data('max')+")</li>"
        }

        if($('*').hasClass('form-issued')){
            $.each($('.has-error-form-issued'), function( k, v ) {
                var num = k+1
                showModal = true
                htmlErrorMsg += "<li>[MI-"+num+"] "+$(this).text()+"</li>"
            })
        } 

        if(showModal){
            $('.list-error').html(htmlErrorMsg)
            $('#modalWarningQty').modal("show")
        }else{
            submitAfterValid('form-delivery')
        }
    }

    var rowsSelected = []
    var totalChecked = 0

    $('#check-all-cb').change(function (e) {
        totalChecked = 0
        $(".check-delivery").prop('checked', $(this).prop('checked'))
        anyChecked = $(this).prop('checked')
        $(this).val($(this).prop('checked'))
        if ($(this).prop('checked')) {
            $("#ds-table tbody input[type='checkbox']").prop("checked", false).trigger("click");
        }else{
            $("#ds-table tbody input[type='checkbox']").prop("checked", true).trigger("click");
        }
        e.stopPropagation()
    })

    $("#ds-table tbody").on('click', 'input[type="checkbox"]', function(e){
        var rowId = $(this).val();
        var index = $.inArray(rowId, rowsSelected);
        if(this.checked && index === -1){
            rowsSelected.push(rowId);
            $(this).prop('checked', true)
            totalChecked ++
        } else if (!this.checked && index !== -1){
            rowsSelected.splice(index, 1)
            $(this).prop('checked', false) 
            totalChecked --
        }
        e.stopPropagation();
    });

    var rowsSelectedRepair = []
    var totalCheckedRepair = 0
    $('#check-all-cb-repair').change(function (e) {
        totalCheckedRepair = 0
        $(".check-delivery-repair").prop('checked', $(this).prop('checked'))
        anyChecked = $(this).prop('checked')
        $(this).val($(this).prop('checked'))
        if ($(this).prop('checked')) {
            $("#ds-table-repair tbody input[type='checkbox']").prop("checked", false).trigger("click");
        }else{
            $("#ds-table-repair tbody input[type='checkbox']").prop("checked", true).trigger("click");
        }
        e.stopPropagation()
    })

    $("#ds-table-repair tbody").on('click', 'input[type="checkbox"]', function(e){
        var rowId = $(this).val();
        var index = $.inArray(rowId, rowsSelectedRepair);
        if(this.checked && index === -1){
            rowsSelectedRepair.push(rowId);
            $(this).prop('checked', true)
            totalCheckedRepair ++
        } else if (!this.checked && index !== -1){
            rowsSelectedRepair.splice(index, 1)
            $(this).prop('checked', false) 
            totalCheckedRepair --
        }
        e.stopPropagation();
    });


    function printLabel(){
        submitAjaxValid('form-print-label', {action:urlPrintLabel, data: { print_delivery: rowsSelected}})
    }

    function printLabelInstant(id){
        submitAjaxValid('form-print-label-'+id, {action:urlPrintLabel, data: { print_delivery: [id]}})
    }

    function printMassDs(){
        submitAjaxValid('form-print-mass-ds', {action:urlMassDs, data: { print_delivery: rowsSelected}})
    }

    function printLabelRepair(){
        submitAjaxValid('form-print-label-repair', {action:urlPrintLabel, data: { print_delivery: rowsSelectedRepair}})
    }

    function printMassDsRepair(){
        submitAjaxValid('form-print-mass-ds-repair', {action:urlMassDs, data: { print_delivery: rowsSelectedRepair}})
    }

    function initializeFieldsWithJavascript(container) {
      var selector;
      if (container instanceof jQuery) {
        selector = container;
      } else {
        selector = $(container);
      }
      selector.find("[data-init-function]").not("[data-initialized=true]").each(function () {
        var element = $(this);
        var functionName = element.data('init-function');

        if (typeof window[functionName] === "function") {
          window[functionName](element);

          // mark the element as initialized, so that its function is never called again
          element.attr('data-initialized', 'true');
        }
      });
    }

	if (typeof deleteEntry != 'function') {
	  $("[data-button-type=delete]").unbind('click');

	  function deleteEntry(button) {
		// ask for confirmation before deleting an item
		// e.preventDefault();
		var route = $(button).attr('data-route');

		swal({
		  title: "{!! trans('backpack::base.warning') !!}",
		  text: "{!! trans('backpack::crud.delete_confirm') !!}",
		  icon: "warning",
		  buttons: ["{!! trans('backpack::crud.cancel') !!}", "{!! trans('backpack::crud.delete') !!}"],
		  dangerMode: true,
		}).then((value) => {
			if (value) {
				$.ajax({
			      url: route,
			      type: 'DELETE',
			      success: function(result) {
			          if (result == 1) {
						  // Redraw the table
						  if (typeof crud != 'undefined' && typeof crud.table != 'undefined') {
							  // Move to previous page in case of deleting the only item in table
							  if(crud.table.rows().count() === 1) {
							    crud.table.page("previous");
							  }

							  crud.table.draw(false);
						  }

			          	  // Show a success notification bubble
			              new Noty({
		                    type: "success",
		                    text: "{!! '<strong>'.trans('backpack::crud.delete_confirmation_title').'</strong><br>'.trans('backpack::crud.delete_confirmation_message') !!}"
		                  }).show();

			              // Hide the modal, if any
			              $('.modal').modal('hide');
                          location.reload()
			          } else {
			              // if the result is an array, it means 
			              // we have notification bubbles to show
			          	  if (result instanceof Object) {
			          	  	// trigger one or more bubble notifications 
			          	  	Object.entries(result).forEach(function(entry, index) {
			          	  	  var type = entry[0];
			          	  	  entry[1].forEach(function(message, i) {
					          	  new Noty({
				                    type: type,
				                    text: message
				                  }).show();
			          	  	  });
			          	  	});
			          	  } else {// Show an error alert
				              swal({
				              	title: "{!! trans('backpack::crud.delete_confirmation_not_title') !!}",
	                            text: "{!! trans('backpack::crud.delete_confirmation_not_message') !!}",
				              	icon: "error",
				              	timer: 4000,
				              	buttons: false,
				              });
			          	  }			          	  
			          }
			      },
			      error: function(result) {
                    var defaultText = "{!! trans('backpack::crud.delete_confirmation_not_message') !!}";
                      if(result.status != 500 && result.responseJSON != null && result.responseJSON.message != null && result.responseJSON.message.length != 0){
						  defaultText = result.responseJSON.message;
					  }
			          swal({
		              	title: "{!! trans('backpack::crud.delete_confirmation_not_title') !!}",
                        text: defaultText,
		              	icon: "error",
		              	timer: 4000,
		              	buttons: false,
		              });
			      }
			  });
			}
		});

      }
	}

	// make it so that the function above is run after each DataTable draw event
	// crud.addFunctionToDataTablesDrawEventQueue('deleteEntry');
</script>
@include('vendor.backpack.crud.extendscript-outhouse')
@endsection