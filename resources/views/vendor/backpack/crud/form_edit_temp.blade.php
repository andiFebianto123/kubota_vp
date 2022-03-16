@extends(backpack_view('blank'))

@php
$defaultBreadcrumbs = [
trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
$crud->entity_name_plural => url($crud->route),
trans('backpack::crud.preview') => false,
];
$action = 'update';

// if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
$breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
<section class="container-fluid d-print-none">
    <a href="javascript: window.print();" class="btn float-right"><i class="la la-print"></i></a>
    <h2>
        <span class="text-capitalize">Temporary</span>
        <small>Preview</small>
        @if ($crud->hasAccess('list'))
        <small class=""><a href="{{ url($crud->route) }}" class="font-sm"><i class="la la-angle-double-left"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></a></small>
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
                    <td>{{$po_line->item}}<br>{{$po_line->description}}</td>
                </tr>
                <tr>
                    <td>Qty Order</td>
                    <td>:</td>
                    <td>{{$po_line->order_qty}}</td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>:</td>
                    <td>    <span class="{{$arr_po_line_status['O']['color']}}">
                            {{$arr_po_line_status['O']['text']}}
                            </span>
                    </td>
                </tr>
                
            </table>
        </div><!-- /.box-body -->
    </div><!-- /.box -->

    <div class="col-md-8">
        <div class="card-header bg-secondary">
            <label class="font-weight-bold mb-0">Create Delivery Sheet</label> 
        </div>
        <div class="card no-padding no-border">
                @if(sizeof($unfinished_po_line['datas']) > 0)
                <div class="m-4 p-2" style="border:1px solid #ff9800; color:#ff9800;">
                    <b> PO Line yang belum selesai:</b><br>
                    @foreach($unfinished_po_line['datas'] as $key => $upl)
                        @if($key == 0)
                            {{$key+1}}. {{$upl->po_num."-".$upl->po_line}} ({{date('Y-m-d',strtotime($upl->due_date))}}) {{($upl->total_shipped_qty)?$upl->total_shipped_qty:"0"}}/{{$upl->order_qty}}<br>
                        @endif
                    @endforeach
                </div>
                @endif
                <form id="form-delivery" method="post"
                        action="{{ url('admin/temp-upload-delivery/'.$entry->id) }}"
                        @if ($crud->hasUploadFields('create'))
                        enctype="multipart/form-data"
                        @endif
                        >
                    {!! csrf_field() !!}
                    @method('PUT')
                    <!-- load the view from the application if it exists, otherwise load the one in the package -->
                    <div class="m-2">
                    @include('crud::inc.show_fields', ['fields' => $crud->fields()])
                    </div>
                    @if(sizeof($unfinished_po_line['datas']) > 0)
                    <button id="btn-for-form-delivery" class="btn btn-primary-vp ml-4 mb-4 mt-0" data-toggle="modal" data-target="#modalAlertDueDate" type="button">Submit</button>
                    @else
                    <button id="btn-for-form-delivery" class="btn btn-primary-vp ml-4 mb-4 mt-0"  type="button" onclick="submitNewDs()">Submit</button>
                    @endif
                    <button class="btn btn-danger mb-4 mt-0"  type="button" onclick="window.history.go(-1); return false;">Cancel</button>

                </form>
        </div>
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
            <p class="text-warning-qty"></p>
            <button type="button" class="btn btn-sm btn-outline-primary" data-dismiss="modal" onclick="submitAfterValid('form-delivery')">Ya</a>
            <button type="button" class="btn btn-sm btn-outline-danger" data-dismiss="modal">Tidak</button>
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
    $(document).ready( function () {
        initializeFieldsWithJavascript('form');
    } );

    function submitNewDs(){
        var showWarning = false
        var htmlMessage = ""
        if($('#current-qty').val() > $('#current-qty').data('max')){
            showWarning = true
            htmlMessage += "<li>Jumlah quantity melebihi maksimum</li>"
        }
        if($('*').hasClass('form-issued')){
            showWarning = false

            // $.each($('.form-issued'), function( k, v ) {
            //     var num = k+1
            //     var lotqty = parseFloat($('.form-issued:eq('+k+')').data('lotqty'))
            //     var currentQty = parseFloat($('.form-issued:eq('+k+')').val())
                
            //     if(currentQty > lotqty){
            //         showWarning = true
            //         htmlMessage += "<li>Jumlah material issue #"+num+" melebihi maksimum</li>"
            //     }
            // })
        } 

        if(showWarning){
            htmlMessage += "<span>Apakah Anda yakin akan melanjutkan?</span>"
            $('.text-warning-qty').html(htmlMessage)
            $('#modalWarningQty').modal("show")
        }else{
            submitAfterValid('form-delivery')
        }
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
</script>
@include('vendor.backpack.crud.extendscript-outhouse')
@endsection