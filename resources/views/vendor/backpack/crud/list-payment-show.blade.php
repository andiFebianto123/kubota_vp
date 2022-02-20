@inject('constant', 'App\Helpers\Constant')
@extends(backpack_view('blank'))

@php
$defaultBreadcrumbs = [
trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
$crud->entity_name_plural => url($crud->route),
trans('backpack::crud.preview') => false,
];

// if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
$breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
<section class="container-fluid d-print-none">
    <a href="javascript: window.print();" class="btn float-right"><i class="la la-print"></i></a>
    <h2>
        <span class="text-capitalize">{{$entry->ds_numb}}</span>
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

    @if($delivery_status)
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-secondary">
                Delivery Status
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>ITEM DETAIL</strong>
                        <table class="table table-striped table-hover">
                            <tr>
                                <td>PO Number</td>
                                <td>: {{$delivery_status->po_num}}</td>
                            </tr>
                            <tr>
                                <td>PO Line</td>
                                <td>: {{$delivery_status->po_line}}</td>
                            </tr>
                            <tr>
                                <td>Item</td>
                                <td>: {{$delivery_status->item}}</td>
                            </tr>
                            <tr>
                                <td>Description</td>
                                <td>: {{$delivery_status->description}}</td>
                            </tr>
                        </table>

                    </div>
                    <div class="col-md-6" style="border-left: 1px solid #d9e2ef;">
                        <strong>DELIVERY STATUS</strong>
                        <table class="table table-striped table-hover">
                            <tr>
                                <td>Received</td>
                                <td>: 
                                    @if($delivery_status->received_flag == 1)
                                    <i class="la la-check text-success font-weight-bold"></i>
                                    @else
                                    <i class="la la-times text-danger font-weight-bold"></i>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Received Date</td>
                                <td>: 
                                    @if($delivery_status->received_date)
                                    {{$delivery_status->received_date}}
                                    @else
                                    -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Received QTY</td>
                                <td>: {{$delivery_status->received_qty}}</td>
                            </tr>
                            <tr>
                                <td>Shipped</td>
                                <td>: {{$delivery_status->shipped_qty}}</td>
                            </tr>
                            <tr>
                                <td>Rejected QTY</td>
                                <td>: <span class="text-danger"> {{$delivery_status->rejected_qty}}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        @if(backpack_user()->roles->first()->hasPermissionTo('Show Payment Status DS'))
            <div class="card">
                <div class="card-header bg-secondary">
                    Payment Status
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-striped table-hover">
                                <tr>
                                    <td>Unit Price</td>
                                    <td>: {{$delivery_show->vendor_currency}} {{number_format($delivery_status->unit_price,0,',','.')}}</td>
                                </tr>
                                <tr>
                                    <td>Vend. Dlv No</td>
                                    <td>: {{$delivery_status->no_surat_jalan_vendor}}</td> 
                                </tr>
                                <tr>
                                    <td>No Faktur Pajak</td>
                                    <td>: {{$delivery_status->no_faktur_pajak}}</td>
                                </tr>
                                <tr>
                                    <td>No Voucher</td>
                                    <td>: {{$delivery_status->no_voucher}}</td>
                                </tr>
                                <tr>
                                    <td>Bank</td>
                                    <td>: {{$delivery_status->bank}}</td>
                                </tr>
                                <tr>
                                    <td>Payment Ref Number</td>
                                    <td>: {{$delivery_status->payment_ref_num}}</td>
                                </tr>
                                <tr>
                                    <td>Total</td>
                                    <td>: {{$delivery_show->vendor_currency}} {{number_format($delivery_status->unit_price*$delivery_status->received_qty,0,',','.')}}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-striped table-hover">
                                <tr>
                                    <td>Payment Plan Date</td>
                                    <td>: {{$delivery_status->payment_plan_date}}</td>
                                </tr>
                                <tr>
                                    <td>Payment Est Date</td>
                                    <td>: {{date('Y-m-d', strtotime($delivery_status->payment_plan_date))}}</td>
                                </tr>
                                <tr>
                                    <td>Validated</td>
                                    <td>:
                                        @if($delivery_status->validate_by_fa_flag == 1)
                                        <button type="button" class="btn btn-sm btn-success"><i class="la la-check"></i></button>
                                        @else
                                        <button type="button" class="btn btn-sm btn-danger"><i class="la la-times"></i></button>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Payment in Proses</td>
                                    <td>: 
                                        @if($delivery_status->payment_in_process_flag == 1)
                                        <button type="button" class="btn btn-sm btn-success"><i class="la la-check"></i></button>
                                        @else
                                        <button type="button" class="btn btn-sm btn-danger"><i class="la la-times"></i></button>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Executed</td>
                                    <td> :
                                        @if($delivery_status->executed_flag == 1)
                                        <button type="button" class="btn btn-sm btn-success"><i class="la la-check"></i></button>
                                        @else
                                        <button type="button" class="btn btn-sm btn-danger"><i class="la la-times"></i></button>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Faktur Pajak</td>
                                    <td> :
                                        @if(isset($delivery_status->file_faktur_pajak))
                                        <a class="btn btn-sm btn-link" target="_blank" href="{{str_replace("files/","file-invoices/",asset($delivery_status->file_faktur_pajak))}}" download><i class="la la-cloud-download-alt"></i> Download</a>
                                        @else
                                        Belum Ada
                                        @endif
                                    </td>
                                </tr>
                                <!-- <tr>
                                    <td>Invoice</td>
                                    <td> :
                                        {{-- @if(isset($delivery_status->invoice) && $delivery_status->invoice != null)
                                            <a class="btn btn-sm btn-link" target="_blank" href="{{$delivery_status->invoice}}" download><i class="la la-cloud-download-alt"></i> Download</a>
                                        @else
                                            Belum Ada
                                        @endif --}}
                                    </td>
                                </tr>
                                <tr>
                                    <td>Surat Jalan</td>
                                    <td> :
                                       {{-- @if(isset($delivery_status->file_surat_jalan) && $delivery_status->file_surat_jalan != null)
                                            <a class="btn btn-sm btn-link" target="_blank" href="{{$delivery_status->file_surat_jalan}}" download><i class="la la-cloud-download-alt"></i> Download</a>
                                        @else
                                            Belum Ada
                                        @endif --}}
                                    </td>
                                </tr> -->
                            </table>
                        </div>
                    </div>
                    
                </div>
            </div>
        @endif
    </div>
    @else
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-secondary">
                Delivery Status
            </div>
            <div class="card-body">
                Tidak Ada Data!
            </div
        </div>
    </div>
    @endif
</div>
@endsection


@section('after_styles')
<link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/crud.css').'?v='.config('backpack.base.cachebusting_string') }}">
<link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/show.css').'?v='.config('backpack.base.cachebusting_string') }}">
<style>
    .pdf-table tbody tr td {
        padding: 4px;
    }
</style>
@endsection

@section('after_scripts')
<script src="{{ asset('packages/backpack/crud/js/crud.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
<script src="{{ asset('packages/backpack/crud/js/show.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
@endsection