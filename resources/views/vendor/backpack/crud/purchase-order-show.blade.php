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
        <span class="text-capitalize">{{$entry->number}}</span>
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
                    <td>PO Number</td>
                    <td>: {{$entry->number}}</td>
                </tr>
                <tr>
                    <td>Vendor</td>
                    <td>: {{$entry->vendor->number}}</td>
                </tr>
                <tr>
                    <td>PO Date</td>
                    <td>: {{$entry->po_date}}</td>
                </tr>
                <tr>
                    <td>Email Sent</td>
                    <td>: {{$entry->email_flag}}</td>
                </tr>
            </table>
        </div><!-- /.box-body -->
    </div><!-- /.box -->
    

    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary-vp">
               <label class="font-weight-bold mb-0">PO Line</label> 
            </div>
            <div class="card-body">
                @if(sizeof($po_lines) > 0)
                <div>
                    <a class="btn btn-sm btn-primary-vp" target="_blank" href="{{url('admin/purchase-order-line-export-excel-accept')}}"><i class="la la-file-excel"></i> Excel</a>
                    <a class="btn btn-sm btn-danger" target="_blank" href="{{url('admin/purchase-order-line-export-pdf-accept')}}"><i class="la la-file-pdf"></i> PDF</a>
                    <button class="btn btn-sm btn-default" type="button" data-toggle="modal" data-target="#importMassDS"><i class="la la-cloud-upload-alt"></i> Import (<span class="total-mass">0</span>)</button>
                </div>
                <table class="table table-striped mb-0 table-responsive">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="check-all-cb-read" class="check-all-read"></th>
                            <th>PO Number</th>
                            <th>Status</th>
                            <th>Item</th>
                            <th>Vendor Name</th>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>UM</th>
                            <th>Due Date</th>
                            <th>Tax (%)</th>
                            <th>Unit Price</th>
                            <th>Total Price</th>
                            <th>Status Accept</th>
                            <th>Read By</th>
                            <th>Read At</th>
                            @if(backpack_auth()->user()->role->name == 'admin')
                            <th>Created At</th>
                            @endif
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($po_lines as $key => $po_line)
                        <tr>
                            <td>
                                @if($po_line->status == 'O')
                                <input type="checkbox" class="check-read-po-lines check-read-{{$po_line->id}}">
                                @endif
                            </td>
                            <td class="text-nowrap">{{$entry->number}}-{{$po_line->po_line}}</td>
                            <td>
                                <span class="{{$arr_po_line_status[$po_line->status]['color']}}">
                                    {{$arr_po_line_status[$po_line->status]['text']}}
                                </span>
                            </td>
                            <td>{{$po_line->item}}</td>
                            <td>{{$po_line->vendor_name}}</td>
                            <td>{{$po_line->description}}</td>
                            <td>{!! $po_line->change_order_qty !!}</td>
                            <td>{{$po_line->u_m}}</td>
                            <td>{!! $po_line->change_due_date !!}</td>
                            <td>{{$po_line->tax}}</td>
                            <td class="text-nowrap">{!! $po_line->change_unit_price !!}</td>
                            <td class="text-nowrap">{!! $po_line->change_total_price !!}</td>
                            <td class="{{['','text-success', 'text-danger'][$po_line->accept_flag]}}">{{['','Accept', 'Reject'][$po_line->accept_flag]}}</td>
                            <td>{{$po_line->read_by_user}}</td>
                            <td>{{$po_line->read_at}}</td>
                            @if(backpack_auth()->user()->role->name == 'admin')
                            <td>{{$po_line->created_at}}</td>
                            @endif
                            <td class="text-nowrap"><!-- Single edit button -->
                                @if($po_line->read_at)
                                    @if($po_line->status == "O" && $po_line->accept_flag == 1)
                                        <a href="{{url('admin/delivery/create')}}" class="btn btn-sm btn-link"><i class="la la-plus"></i> Create</a>
                                    @endif
                                    @if(backpack_auth()->user()->role->name == 'admin' && sizeof($po_line->delivery) == 0)
                                        <a href="{{url('admin/purchase-order-line')}}/{{$po_line->id}}/unread" class="btn btn-sm btn-link"><i class="la la-book"></i> Unread</a>
                                    @endif    
                                    <a href="{{url('admin/purchase-order-line')}}/{{$po_line->id}}/show" class="btn btn-sm btn-link"><i class="la la-eye"></i> View</a>
                                @else
                                    @if(backpack_auth()->user()->role->name != 'admin')
                                    <button class="btn btn-sm btn-link"  type="button" data-toggle="modal" data-target="#modalAccept"><i class="la la-check"></i> Accept</button>
                                    <button class="btn btn-sm btn-link"  type="button" data-toggle="modal" data-target="#modalReject"><i class="la la-times"></i> Reject</button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
                @else
                <p class="text-center">
                    No Data Available
                </p>
                @endif
                {{-- $po_lines->links() --}}
            </div>

        </div><!-- /.box-body -->
    </div>

    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary-vp">
               <label class="font-weight-bold mb-0">PO Change History</label> 
            </div>
            <div class="card-body">
                @if(sizeof($po_changes_lines) > 0)
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>PO Number</th>
                            <th>PO Line</th>
                            <th>Issued Date</th>
                            <th>Change</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($po_changes_lines as $key => $po_line)
                        <tr>
                            <td>{{$po_line->number}}</td>
                            <td>{{$po_line->po_line}}</td>
                            <td>{{date('Y-m-d', strtotime($po_line->po_change_date))}}</td>
                            <td>{{$po_line->po_change}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p class="text-center">
                    No Data Available
                </p>
                @endif
            </div>

        </div><!-- /.box-body -->
    </div>
</div>


@endsection

@section('after_styles')
<link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/crud.css').'?v='.config('backpack.base.cachebusting_string') }}">
<link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/show.css').'?v='.config('backpack.base.cachebusting_string') }}">
@endsection

@section('after_scripts')

<!-- Modal -->
<div id="importMassDS" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Import Mass Delivery Sheet</h5>
        </div>
        <div class="modal-body">
            <p>Silahkan menggunakan template di bawah ini untuk mengimport <br><a href="{{asset('docs/template-delivery-sheet.xlsx')}}">template-delivery-sheet.xlsx</a></p>
            <form id="form-import-ds" action="{{url('admin/purchase-order-import-ds')}}" method="post">
                @csrf
                <input type="file" name="file_po" class="form-control py-1 rect-validation">

                <div class="mt-4 text-right">
                    <button id="btn-for-form-import-ds" type="button" class="btn btn-sm btn-outline-primary" onclick="submitAfterValid('form-import-ds')">Import</a>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-dismiss="modal">Close</button>
                </div>      
            </form>
        </div>
    </div>
  </div>
</div>

<div id="modalAccept" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Accept PO Line</h5>
        </div>
        <div class="modal-body">
            <form id="form-import-ds" action="{{url('admin/purchase-order-import-ds')}}" method="post">
                @csrf
                <div class="mt-4 text-right">
                    <button id="btn-for-form-import-ds" type="button" class="btn btn-sm btn-outline-primary" onclick="submitAfterValid('form-import-ds')">Submit</a>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-dismiss="modal">Close</button>
                </div>      
            </form>
        </div>
    </div>
  </div>
</div>

<div id="modalReject" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Reject PO Line</h5>
        </div>
        <div class="modal-body">
            <form id="form-import-ds" action="{{url('admin/purchase-order-import-ds')}}" method="post">
                @csrf
                <label for="">Write Reason</label>
                <textarea name="reason" class="form-control" id="" cols="30" rows="10"></textarea>

                <div class="mt-4 text-right">
                    <button id="btn-for-form-import-ds" type="button" class="btn btn-sm btn-outline-primary" onclick="submitAfterValid('form-import-ds')">Submit</a>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-dismiss="modal">Close</button>
                </div>      
            </form>
        </div>
    </div>
  </div>
</div>

<script src="{{ asset('packages/backpack/crud/js/crud.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
<script src="{{ asset('packages/backpack/crud/js/show.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
<script>
var anyChecked = false
var anyReadChecked = false
var totalPoLine = $('.check-po-lines').length
var totalPoLineRead = $('.check-read-po-lines').length
var totalChecked = 0
var totalCheckedRead = 0
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})
$('#check-all-cb').change(function () {
    totalChecked = 0
    $(".check-po-lines").prop('checked', $(this).prop('checked'))
    anyChecked = $(this).prop('checked')
    if ($(this).prop('checked')) {
        totalChecked = totalPoLine
    }
    
    callButton(anyChecked)
})

$('.check-po-lines').change(function () {
    if ($(this).prop('checked')==true){
        $(this).prop('checked', true) 
        totalChecked ++
    }else{
        $(this).prop('checked', false)
        totalChecked --
    }
    
    if (totalChecked > 0) {
        callButton(true) 
        if (totalChecked == totalPoLine) {
            $('#check-all-cb').prop('checked', true)
        }
    }else{
        $('#check-all-cb').prop('checked', false)
        callButton(false) 
    }
})

$('#check-all-cb-read').change(function () {
    totalCheckedRead = 0
    $(".check-read-po-lines").prop('checked', $(this).prop('checked'))
    anyReadChecked = $(this).prop('checked')
    if ($(this).prop('checked')) {
        totalCheckedRead = totalPoLineRead
    }

    $(".total-mass").text(totalCheckedRead)
})

$('.check-read-po-lines').change(function () {
    if ($(this).prop('checked')==true){
        $(this).prop('checked', true) 
        totalCheckedRead ++
    }else{
        $(this).prop('checked', false)
        totalCheckedRead --
    }
    
    if (totalCheckedRead > 0) {
        if (totalCheckedRead == totalPoLineRead) {
            $('#check-all-cb-read').prop('checked', true)
        }
    }else{
        $('#check-all-cb-read').prop('checked', false)
    }
    $(".total-mass").text(totalCheckedRead)

})

 function callButton(anyChecked){
    var htmlBtnAccOrder = "<input type='radio' name='flag_accept' value='1' checked> Accept "
    htmlBtnAccOrder += "<input type='radio' name='flag_accept' value='2'> Reject <br>"
    htmlBtnAccOrder += "<button id='btn-for-form-mass-read' type='button' onclick='submitAfterValid(\"form-mass-read\")' class='btn btn-sm btn-primary-vp'><i class='la la-check-circle'></i> Submit</button>"
    if (anyChecked) {
        $(".section-buttons").html(htmlBtnAccOrder)
    }else{
        $(".section-buttons").html("")
    }
 }
 
</script>
@endsection