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
        <span class="text-capitalize">{{$po_num}}</span>
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
                    <td>: {{$po_num}}</td>
                </tr>
               
                <tr>
                    <td>Change</td>
                    <td>: {{$po_change}}</td>
                </tr>
                <tr>
                    <td>Jumlah</td>
                    <td>: {{sizeof($po_lines)}}</td>
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
                <table class="table table-striped mb-0 table-responsive">
                    <thead>
                        <tr>
                            <th>PO Number</th>
                            <th>Status</th>
                            <th>Item</th>
                            <th>Vendor Name</th>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>UM</th>
                            <th>Due Date</th>
                            <th>Tax (%)</th>
                            <th>Unit Price (IDR)</th>
                            <th>Total Price (IDR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($po_lines as $key => $po_line)
                        <tr>
                            <td class="text-nowrap">{{$po_num}}-{{$po_line->po_line}}</td>
                            <td>
                                <span class="{{$arr_po_line_status[$po_line->status]['color']}}">
                                    {{$arr_po_line_status[$po_line->status]['text']}}
                                </span>
                            </td>
                            <td>{{$po_line->item}}</td>
                            <td>{{$po_line->vendor_name}}</td>
                            <td>{{$po_line->description}}</td>
                            <td>{!! $po_line->order_qty !!}</td>
                            <td>{{$po_line->u_m}}</td>
                            <td>{!! $po_line->due_date !!}</td>
                            <td>{{$po_line->tax}}</td>
                            <td class="text-nowrap">{!! $po_line->unit_price !!}</td>
                            <td class="text-nowrap">{!! $po_line->total_price !!}</td>
                            
                        </tr>
                        @endforeach

                    </tbody>
                </table>
                <div class="section-buttons"></div>

                @else
                <p class="text-center">
                    No Data Available
                </p>
                @endif
                {{-- $po_lines->links() --}}
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
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})
</script>
@endsection
