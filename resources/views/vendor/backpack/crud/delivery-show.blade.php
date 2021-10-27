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
            <label class="font-weight-bold mb-0">Detail</label> 
        </div>
        <div class="card no-padding no-border p-4">
            <h1>Delivery Sheet</h1>
            <span>PT KUBOTA INDONESIA</span>
            <div>
                <div style="float:left; position:relative; width: 80%;">
                    <table border="1px" width="98%" class="pdf-table">
                        <tbody>
                            <tr>
                                <td>Delivery Sheet No.<br><strong>{{$entry->ds_num}}</strong></td>
                                <td colspan="2"></td>
                            </tr>
                            <tr>
                                <td>Dlv.Date<br><strong>{{$entry->ds_num}}</strong></td>
                                <td colspan="2">P/O Due Date<br><strong>{{$entry->due_date}}</strong></td>
                            </tr>
                            <tr>
                                <td>Vend. No<br><strong>V018073</strong></td>
                                <td>Vend. Name<br><strong>RECT MEDIA KOMPUTINDO, PT</strong></td>
                                <td>Vendor Dlv. No<br><strong></strong></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="first">
                                <table class="inline" width="100%">
                                    <tbody>
                                        <tr>
                                            <td width="25%" style="border-right: 1px solid black ;">Order No.<br><strong>PU00011716.1</strong></td>
                                            <td width="25%" style="border-right: 1px solid black ;" style="padding-right:5px;">Order QTY<br><strong style="text-align: right;">1</strong></td>
                                            <td width="25%" style="border-right: 1px solid black ;">Dlv.QTY<br><strong style="text-align: right;">1</strong></td>
                                            <td width="25%">Unit Price<br><strong class="right">29,250,000.00</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="first">
                                    <table class="inline">
                                        <tbody>
                                            <tr>
                                                <td width="25%" style="border-right: 1px solid black ;">Part No.<br><strong>SP.18.CSWDV.03.241017.REV01</strong></td>
                                                <td width="20%" style="border-right: 1px solid black ;">Currency<br><strong>IDR</strong></td>
                                                <td width="15%" style="border-right: 1px solid black ;">Tax Status<br><strong class="right">PPN0</strong></td>
                                                <td width="25%">Amount<br><strong class="right">29,250,000.00</strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>Part Name<br><strong>Pengembangan Website dan Security PT KI</strong></td>
                                <td>WH<br><strong>P1</strong></td>
                                <td>Location<br><strong></strong></td>
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
                     {{QRCode::size(200)->generate($qr_code)}}
                    </div>
                    <div style="border:1px solid #000; margin-top: 10px; width: 100%; padding: 5px 10px 0 10px;">
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
                <a href="#" class="btn btn-danger"><i class="la la-file-pdf"></i> + Harga</a>
                <a href="#" class="btn btn-secondary"><i class="la la-file-pdf"></i> - Harga</a>
            </div>
            
            
        </div><!-- /.box-body -->
    </div><!-- /.box -->
    
</div>
@endsection


@section('after_styles')
<link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/crud.css').'?v='.config('backpack.base.cachebusting_string') }}">
<link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/show.css').'?v='.config('backpack.base.cachebusting_string') }}">
<style>
    .pdf-table tbody tr td{
        padding: 4px;
    }
</style>
@endsection

@section('after_scripts')
<script src="{{ asset('packages/backpack/crud/js/crud.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
<script src="{{ asset('packages/backpack/crud/js/show.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
@endsection