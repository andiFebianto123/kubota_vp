@inject('constant', 'App\Helpers\Constant')
@extends(backpack_view('blank'))

@php
$defaultBreadcrumbs = [
trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
$crud->entity_name_plural => url($crud->route),
trans('backpack::crud.preview') => false,
];
$file_count = 0;
// if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
$breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
<section class="container-fluid d-print-none">
    <a href="javascript: window.print();" class="btn float-right"><i class="la la-print"></i></a>
    <h2>
        <span class="text-capitalize">{{$ds_num}}</span>
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
                            <a class="dropdown-item" href="{{ url('delivery-detail/'.$ds_num.'/'.$ds_line) }}?locale={{ $key }}">{{ $locale }}</a>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="col-md-12">
    @if($constant::checkPermission('Read Delivery Sheet'))
        <div class="card-header bg-secondary">
            <label class="font-weight-bold mb-0">Delivery Sheet</label>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                    No Data Available for {{$ds_num}}-{{$ds_line}}
                    </div>
                </div>
            </div>
        </div>
    @endif
    </div><!-- /.box -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-secondary">
                <label class="font-weight-bold mb-0">Delivery Status</label>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        No Data Available
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        @if(backpack_user()->roles->first()->hasPermissionTo('Show Payment Status DS'))
            <div class="card">
                <div class="card-header bg-secondary">
                    <label class="font-weight-bold mb-0">Payment Status</label>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            No Data Available
                        </div>
                    </div>
            </div>
        @endif
    </div>
</div>
@endsection


@section('after_styles')
<link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/crud.css').'?v='.config('backpack.base.cachebusting_string') }}">
<link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/show.css').'?v='.config('backpack.base.cachebusting_string') }}">

@endsection
