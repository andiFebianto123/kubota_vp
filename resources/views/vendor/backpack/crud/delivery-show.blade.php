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

    <div class="col-md-12">
        <div class="card-header bg-secondary">
            <label class="font-weight-bold mb-0">Delivery Sheet</label>
        </div>
        <div class="card no-padding no-border p-4">
            <h1>Delivery Sheet</h1>
            <span>PT KUBOTA INDONESIA</span>
            <div>
                <div style="float:left; position:relative; width: 80%;">
                    <table border="1px" width="98%" class="pdf-table">
                        <tbody>
                            <tr>
                                <td width="50%" colspan="2">Delivery Sheet No.<br><strong>{{$delivery_show->ds_num}}</strong></td>
                                <td width="50%" colspan="2"></td>
                            </tr>
                            <tr>
                                <td width="50%" colspan="2">Dlv.Date<br><strong>{{date("Y-m-d", strtotime($delivery_show->shipped_date))}}</strong></td>
                                <td width="50%" colspan="2">P/O Due Date<br><strong>{{date("Y-m-d", strtotime($delivery_show->due_date))}}</strong></td>
                            </tr>
                            <tr>
                                <td width="50%" colspan="2">Vend. No<br><strong>{{$delivery_show->vendor_number}}</strong></td>
                                <td width="25%">Vend. Name<br><strong>{{$delivery_show->vendor_name}}</strong></td>
                                <td width="25%">Vendor Dlv. No<br><strong>{{$delivery_show->no_surat_jalan_vendor}}</strong></td>
                            </tr>
                            <tr>
                                <td width="25%">Order No.<br><strong>{{$delivery_show->po_number}}-{{$delivery_show->po_line}}</strong></td>
                                <td width="25%">Order QTY<br><strong style="text-align: right;">{{$delivery_show->order_qty}}</strong></td>
                                <td width="25%">Dlv.QTY<br><strong style="text-align: right;">{{$delivery_show->shipped_qty}}</strong></td>
                                <td width="25%">Unit Price<br><strong class="right">{{$delivery_show->vendor_currency." " . number_format($delivery_show->unit_price,0,',','.')}}</strong></td>
                            </tr>

                            <tr>
                                <td width="25%">Part No.<br><strong>{{$delivery_show->item}}</strong></td>
                                <td width="25%">Currency<br><strong>{{$delivery_show->vendor_currency}}</strong></td>
                                <td width="25%">Tax Status<br><strong class="right">{{$delivery_show->tax_status}}</strong></td>
                                <td width="25%">Amount<br><strong class="right">{{$delivery_show->vendor_currency." " . number_format($delivery_show->shipped_qty*$delivery_show->unit_price,0,',','.')}}</strong></td>
                            </tr>
                            <tr>
                                <td width="50%" colspan="2">Part Name<br><strong>{{$delivery_show->description}}</strong></td>
                                <td width="25%">WH<br><strong>{{$delivery_show->wh}}</strong></td>
                                <td width="25%">Location<br><strong>{{$delivery_show->location}}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                    <table border="1px" width="98%" style="margin-top: 10px;" class="pdf-table">
                        <tbody>
                            <tr>
                                <td width="15%" align="center"><small>VENDOR</small></td>
                                <td rowspan="2" valign="top">
                                    <small>QC</small> : <strong>NO</strong><br>
                                    <small>NOTES</small> :
                                </td>
                            </tr>
                            <tr>
                                <td height="80px"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div style="float:right; position:relative; width:20%;">
                    <div>
                        {{QRCode::size(220)->generate($qr_code)}}
                    </div>
                    <div style="border:1px solid #000; margin-top: 10px; width: 100%; max-width:220px; padding: 5px 10px 0 10px;">
                        <strong>Document Requirements</strong>
                        <ul>
                            <li>Material Mill Sheet</li>
                            <li>Material Safety Data Sheet</li>
                            <li>Result of Inspection (Certificate)</li>
                            <li>Product Safaty Information Sheet</li>
                            <li>Instruction Operator Manual</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="{{url('admin/delivery-export-pdf?id='.$entry->id.'&wh=yes')}}" class="btn btn-danger"><i class="la la-file-pdf"></i> + Harga</a>
                <a href="{{url('admin/delivery-export-pdf?id='.$entry->id)}}" class="btn btn-secondary"><i class="la la-file-pdf"></i> - Harga</a>
            </div>
        </div>
    </div><!-- /.box -->
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
                                    <td>: {{$format_currency}} {{number_format($delivery_status->unit_price*$delivery_status->received_qty,0,',','.')}}</td>
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
                                        @if($delivery_status->received_flag == 1)
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
                                        <a class="btn btn-sm btn-link" target="_blank" href="{{$delivery_status->file_faktur_pajak}}" download><i class="la la-cloud-download-alt"></i> Download</a>
                                        @else
                                        Belum Ada
                                        @endif
                                    </td>
                                </tr>
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