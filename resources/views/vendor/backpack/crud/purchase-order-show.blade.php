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
            <div class="card-header bg-danger">
               <label class="font-weight-bold mb-0">PO Line (Unread)</label> 
            </div>
            <div class="card-body">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="check-all-cb" class="check-all"></th>
                            <th>PO Number</th>
                            <th>Item</th>
                            <th>Description</th>
                            <th>Qty Order</th>
                            <th>Unit Price</th>
                            <th>Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $total = 0
                        @endphp
                        @foreach ($po_line_unreads as $key => $po_line)
                        <tr>
                            <td><input type="checkbox" class="check-po-lines check-{{$po_line->id}}"></td>
                            <td>{{$entry->number}}-{{$po_line->po_line}}</td>
                            <td>{{$po_line->item}}</td>
                            <td>{{$po_line->description}}</td>
                            <td>{{$po_line->order_qty}}</td>
                            <td>{{"IDR " . number_format($po_line->unit_price,0,',','.')}}</td>
                            <td>{{"IDR " . number_format($po_line->unit_price*$po_line->order_qty,0,',','.')}}</td>
                        </tr>
                        @php
                            $total += $po_line->unit_price*$po_line->order_qty
                        @endphp
                        @endforeach

                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-center font-weight-bold">
                                Total
                            </td>
                            <td>
                                {{"IDR " . number_format($total,0,',','.')}}</td>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <div class="section-buttons"></div>
            </div>

        </div><!-- /.box-body -->
    </div>

    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-secondary">
               <label class="font-weight-bold mb-0">PO Line (Read)</label> 
            </div>
            <div class="card-body">
                <div>
                    <a class="btn btn-sm btn-primary-vp" href="#"><i class="la la-file-excel"></i> Excel</a>
                    <a class="btn btn-sm btn-danger" href="#"><i class="la la-file-pdf"></i> PDF</a>
                </div>
                <table class="table table-striped mb-0 table-responsive">
                    <thead>
                        <tr>
                            <th>PO Number</th>
                            <th>Status</th>
                            <th>Item</th>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>UM</th>
                            <th>Due Date</th>
                            <th>Tax</th>
                            <th>Unit Price</th>
                            <th>Total Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($po_line_reads as $key => $po_line)
                        <tr>
                            <td class="text-nowrap">{{$entry->number}}-{{$po_line->po_line}}</td>
                            <td>
                                <span class="{{$arr_po_line_status[$po_line->status]['color']}}">
                                    {{$arr_po_line_status[$po_line->status]['text']}}
                                </span>
                            </td>
                            <td>{{$po_line->item}}</td>
                            <td>{{$po_line->description}}</td>
                            <td>{{$po_line->order_qty}}</td>
                            <td>{{$po_line->u_m}}</td>
                            <td>{{$po_line->due_date}}</td>
                            <td>{{$po_line->tax}}</td>
                            <td class="text-nowrap">{{"IDR " . number_format($po_line->unit_price,0,',','.')}}</td>
                            <td class="text-nowrap">{{"IDR " . number_format($po_line->unit_price*$po_line->order_qty,0,',','.')}}</td>
                            <td class="text-nowrap"><!-- Single edit button -->
                                @if($po_line->status == "O")
                                <a href="http://localhost/office/kubota-vendor-portal/public/admin/purchase-order/1/show" class="btn btn-sm btn-link"><i class="la la-plus"></i> Create</a>
                                @endif
                                <a href="{{url('admin/purchase-order-line')}}/{{$po_line->id}}/show" class="btn btn-sm btn-link"><i class="la la-eye"></i> View</a>
                            </td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
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
<script src="{{ asset('packages/backpack/crud/js/crud.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
<script src="{{ asset('packages/backpack/crud/js/show.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
<script>
var anyChecked = false
var totalPoLine = $('.check-po-lines').length
var totalChecked = 0

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

// $.each($('.check-po-lines'), function( index, value ) {

//     $('.check-'+index).change(function () {
//         if ($(this).prop('checked')==true){
//             $(this).prop('checked', true); 
//             totalPoLine ++
//         }else{
//             $(this).prop('checked', false); 
//             totalPoLine --
//         }
//         console.log(totalPoLine);
//         if (totalPoLine > 0) {
//             callButton(true) 
//         }else{
//             callButton(false) 
//         }
//     })
// })

 function callButton(anyChecked){
    var htmlBtnAccOrder = "<button id='btn-acc-order' class='btn btn-sm btn-primary-vp'><i class='la la-check-circle'></i> Accept Order</button>"
    if (anyChecked) {
        $(".section-buttons").html(htmlBtnAccOrder)
    }else{
        $(".section-buttons").html("")
    }
 }
 
</script>
@endsection