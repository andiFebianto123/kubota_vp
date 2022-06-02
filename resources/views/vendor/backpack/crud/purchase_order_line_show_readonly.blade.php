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
                    <td>{{$entry->currency}} {{number_format($entry->unit_price,0,',','.')}}</td>
                </tr>
                @endif
            </table>
        </div><!-- /.box-body -->
    </div><!-- /.box -->

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
                                <th></th>
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
                                    <a href="{{url('admin/delivery-detail/'.$delivery->ds_num.'/'.$delivery->ds_line)}}" class="btn btn-sm btn-outline-primary" data-toggle='tooltip' data-placement='top' title="Detail"><i class="la la-qrcode"></i></a>
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
                                <td colspan="5" class="text-center font-weight-bold">
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
                                <td colspan='3'></td>
                            </tr>
                        </tfoot>
                    </table>
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
                                    <a href="{{url('admin/delivery-detail').'/'.$delivery->ds_num.'/'.$delivery->ds_line}}" class='btn-link'>
                                        {{$delivery_repair->ref_ds_num}}-{{$delivery_repair->ref_ds_line}}
                                    </a>
                                </td>
                                <td style="white-space: nowrap;">
                                    <!-- <a href="#" class="btn btn-sm btn-danger"><i class="la la-file-pdf"></i> + Harga</a>
                                    <a href="#" class="btn btn-sm btn-secondary"><i class="la la-file-pdf"></i> - Harga</a> -->
                                    <a href="{{url('admin/delivery-detail/'.$delivery_repair->ds_num.'/'.$delivery_repair->ds_line)}}" class="btn btn-sm btn-outline-primary" data-toggle='tooltip' data-placement='top' title="DS Detail"><i class="la la-qrcode"></i></a>
                                  
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
@endsection