@extends(backpack_view('blank'))

@php
  $defaultBreadcrumbs = [
    trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
    $crud->entity_name_plural => url($crud->route),
    trans('backpack::crud.list') => false,
  ];

  $arr_filter_forecasts = ['day', 'week', 'month'];

  // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
  <div class="container-fluid">
    <h2>
      <span class="text-capitalize">{!! $crud->getHeading() ?? $crud->entity_name_plural !!}</span>
      <small id="datatable_info_stack">{!! $crud->getSubheading() ?? '' !!}</small>
    </h2>
  </div>
@endsection

@section('content')
  
  <!-- Default box -->
  <div class="row">
      <div class="col-md-12">
          <center class="mt-4">
            <img src="{{asset('img/undeconstruction.png')}}" class="img-responsive" alt="">
            <hr>
            <h5>This page is under construction</h5>
          </center>
      </div>
  </div>
@endsection