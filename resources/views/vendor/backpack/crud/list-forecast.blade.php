@inject('constant', 'App\Helpers\Constant')
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
  <!-- Filter box -->
  @if($filter_vendor)
    <div class="row">
      <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-secondary">
                <strong>Filter</strong>
            </div>
            <div class="card-body">
              <form action="" method="GET">
                <div class="form-group">
                  <label>Filter By Vendor</label>
                  <select 
                    class="form-control select2 select2_filter_vendor" 
                    style="width: 100;"
                    name="filter_vendor"
                  >
                    @if(Session::get('vendor_name'))
                      <option value="{{ Session::get('vendor_name') }}" selected>{{ Session::get('vendor_text') }}</option>
                    @else
                      <option value="hallo" selected>-</option>
                    @endif
                  </select>
                </div>
                <button type="submit" name="vendor_submit" value='1' class="btn btn-sm btn-primary">Submit</button>
              </form>
              </div>
          </div>
      </div>
    </div>
  @endif

  <div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-secondary">
                <strong>Filter</strong>
            </div>
            <div class="card-body">
                <form action="" method="get">
                    <div class="form-group">
                        <label>Filter By</label>
                        <select class="form-control" name="filter_forecast_by" id="">
                            @foreach($arr_filter_forecasts as $aff)
                              @if(isset($crud->type))
                                @if($aff == $crud->type)
                                  <option value="{{$aff}}" selected>{{strtoupper($aff)}}</option>
                                @else
                                  <option value="{{$aff}}" >{{strtoupper($aff)}}</option>
                                @endif
                              @endif
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
  </div>
  
  <div class="row">

    <!-- THE ACTUAL CONTENT -->
    <div class="{{ $crud->getListContentClass() }}">

        <div class="row mb-0">
          <div class="col-sm-6">
            @if ( $crud->buttons()->where('stack', 'top')->count() ||  $crud->exportButtons())
              <div class="d-print-none {{ $crud->hasAccess('create')?'with-border':'' }}">

                @include('crud::inc.button_stack', ['stack' => 'top'])

              </div>
            @endif
          </div>
          <div class="col-sm-6">
            <div id="datatable_search_stack" class="mt-sm-0 mt-2 d-print-none"></div>
          </div>
        </div>

        {{-- Backpack List Filters --}}
        @if ($crud->filtersEnabled())
          @include('crud::inc.filters_navbar')
        @endif

        <div>
            <h5>Data Forecast <b> {{Session::get("week")}} {{Session::get("month")}} {{Session::get("year")}}</b></h5>
        </div>
        <table id="crudTable" class="bg-white table table-striped table-hover nowrap rounded shadow-xs border-xs mt-2" style="border-collapse: collapse;" cellspacing="0">
            <thead>
              @if($crud->type == 'week')
                <tr>
                  <th style="background-color: #F5F5F5!important; position: -webkit-sticky; position: sticky; left: 0px;"></th>
                @foreach($crud->columnHeader as $header)
                  <th colspan="4" style="text-align:center; border:1px solid #ddd;">
                      {!! $header !!}
                  </th>
                @endforeach
                 {{-- <th></th> --}}
                </tr>
              @endif
              @if($crud->type == 'days')
                <tr>
                  <th style="background-color: #F5F5F5!important; position: -webkit-sticky; position: sticky; left: 0px;"></th>
                <?php
                  $z_index = 100;
                ?>
                @foreach($crud->columnHeader->all() as $header)
                  <?php
                    $key = $header['key'].'-01';
                    $newDate = new DateTime($key);
                    $key = $newDate->format('F Y');
                    $colspan = count($header['data']);
                  ?>
                  <th colspan="{{ $colspan }}" class="" style="border:1px solid #DADADA; z-index: {{ $z_index }}; text-align: center;">
                      {!! $key !!}
                  </th>
                  <?php
                    $z_index += 10;
                  ?>
                @endforeach
                 {{-- <th></th> --}}
                </tr>
              @endif
              <tr>
                {{-- Table columns --}}
                @foreach ($crud->columns() as $column)
                  @if($column['label'])
                    <?php
                      $style = "";
                      if($type_forecast == 'week'){
                        if($column['type'] == 'forecast'){
                          if($column['rome_symbol'] == 'I'){
                            $style = "border-left: 1px solid #ddd;";
                          }else if($column['rome_symbol'] == 'IV'){
                            $style = "border-right: 1px solid #ddd;";
                          }
                        }
                      }else if($type_forecast == 'days'){
                        $getKey = explode('-', $column['label']);
                        if($column['label'] != "Nama Item"){
                            $search = $constant::getColumnHeaderDays($crud->columnHeader, "{$getKey[0]}-{$getKey[1]}", $column['label']);
                            if($search['search'] == 0){
                              $style = "border-left: 1px solid #ddd;";
                            }
                            $column['label'] = "{$getKey[2]}";
                        }
                      }
                    ?>
                    <?php
                      $classHeaderNameItem = ($column['label'] == 'Nama Item') ? 'fixedHead' : '';
                    ?>
                    <th
                      class="{{ $classHeaderNameItem }}"
                      style="{{ $style }}"
                      data-orderable="{{ var_export($column['orderable'], true) }}"
                      data-priority="{{ $column['priority'] }}"
                      {{--
                          data-visible-in-table => if developer forced field in table with 'visibleInTable => true'
                          data-visible => regular visibility of the field
                          data-can-be-visible-in-table => prevents the column to be loaded into the table (export-only)
                          data-visible-in-modal => if column apears on responsive modal
                          data-visible-in-export => if this field is exportable
                          data-force-export => force export even if field are hidden
                      --}}

                      {{-- If it is an export field only, we are done. --}}
                      @if(isset($column['exportOnlyField']) && $column['exportOnlyField'] === true)
                        data-visible="false"
                        data-visible-in-table="false"
                        data-can-be-visible-in-table="false"
                        data-visible-in-modal="false"
                        data-visible-in-export="true"
                        data-force-export="true"
                      @else
                        data-visible-in-table="{{var_export($column['visibleInTable'] ?? false)}}"
                        data-visible="{{var_export($column['visibleInTable'] ?? true)}}"
                        data-can-be-visible-in-table="true"
                        data-visible-in-modal="{{var_export($column['visibleInModal'] ?? true)}}"
                        @if(isset($column['visibleInExport']))
                          @if($column['visibleInExport'] === false)
                            data-visible-in-export="false"
                            data-force-export="false"
                          @else
                            data-visible-in-export="true"
                            data-force-export="true"
                          @endif
                        @else
                          data-visible-in-export="true"
                          data-force-export="false"
                        @endif
                      @endif
                    >
                      @if(isset($column['link']))
                      <a href="{{url('admin/forecast')}}{{$column['link']}}{{$column['label']}}">{{$column['label']}}</a>
                      @else
                      {!! $column['label'] !!}
                      @endif
                    </th>
                    @endif
                @endforeach

                {{-- @if ( $crud->buttons()->where('stack', 'line')->count() )
                  <th data-orderable="false"
                      data-priority="{{ $crud->getActionsColumnPriority() }}"
                      data-visible-in-export="false"
                      >{{ trans('backpack::crud.actions') }}</th>
                @endif --}}
              </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
              <tr>
                {{-- Table columns --}}
                @foreach ($crud->columns() as $column)
                  <?php
                    if($type_forecast == 'days'){
                      $getKey = explode('-', $column['label']);
                      if($column['label'] != "Nama Item"){
                          $column['label'] = "{$getKey[2]}";
                      }
                    }
                  ?>
                  @if($column['label'] == 'Nama Item')
                    <th style="background-color: #F5F5F5!important; position: -webkit-sticky; position: sticky; left: 0px;">{!! $column['label'] !!}</th>
                  @else
                    <th> {!! $column['label'] !!} </th>
                  @endif
                @endforeach

                {{-- @if ( $crud->buttons()->where('stack', 'line')->count() )
                  <th>{{ trans('backpack::crud.actions') }}</th>
                @endif --}}
              </tr>
            </tfoot>
          </table>

          @if ( $crud->buttons()->where('stack', 'bottom')->count() )
          <div id="bottom_buttons" class="d-print-none text-center text-sm-left">
            @include('crud::inc.button_stack', ['stack' => 'bottom'])

            <div id="datatable_button_stack" class="float-right text-right hidden-xs"></div>
          </div>
          @endif

    </div>

  </div>
  <script>
    var jobs = {!! json_encode($crud) !!};
  </script>
@endsection

@section('after_styles')
  <!-- DATA TABLES -->
  <link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-fixedheader-bs4/css/fixedHeader.bootstrap4.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}">

  <link href="{{ asset('packages/select2/dist/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
  <link href="{{ asset('packages/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />

  <link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/crud.css').'?v='.config('backpack.base.cachebusting_string') }}">
  <link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/form.css').'?v='.config('backpack.base.cachebusting_string') }}">
  <link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/list.css').'?v='.config('backpack.base.cachebusting_string') }}">

  <!-- CRUD LIST CONTENT - crud_list_styles stack -->

  <style>
    #crudTable tbody tr td:first-child {
      position: sticky;
      left: 0;
      background-color: #F5F5F5!important;
    }
    .sticky {
      position: -webkit-sticky;
      position: sticky;
      left: 0px;
      background-color: #F5F5F5!important;
    }
    .fixedHead{
      position: -webkit-sticky;
      position: sticky;
      left: 0px;
      background-color: #F5F5F5!important;
    }
  </style>
  @stack('crud_list_styles')
@endsection

@section('after_scripts')
  @include('crud::inc.datatables_logic')
  <!-- include select2 js-->
  <script src="{{ asset('packages/select2/dist/js/select2.full.min.js') }}"></script>
  @if (app()->getLocale() !== 'en')
  <script src="{{ asset('packages/select2/dist/js/i18n/' . str_replace('_', '-', app()->getLocale()) . '.js') }}"></script>
  @endif
  <script type="text/javascript">
    $(function(){
       $('[data-toggle="tooltip"]').tooltip();
       $('.select2_filter_vendor').select2({
           minimumInputLength: 3,
           allowClear: true,
           placeholder: 'Select Vendor',
           ajax: {
              dataType: 'json',
              url: jobs.urlAjaxFilterVendor,
              delay: 500,
              data: function(params) {
                return {
                  term: params.term
                }
              },
              processResults: function (data, page) {
              return {
                results: $.map(data, function(item, key){
                    return {
                      text:item,
                      id:key
                    }
                })
              };
            },
          }
      }).on('select2:select', function (evt) {
         // var data = $(".select2 option:selected").text();
         // alert("Data yang dipilih adalah "+data);
      });
    });
  </script>
  <script src="{{ asset('packages/backpack/crud/js/crud.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
  <script src="{{ asset('packages/backpack/crud/js/form.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
  <script src="{{ asset('packages/backpack/crud/js/list.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
  <!-- CRUD LIST CONTENT - crud_list_scripts stack -->
  @stack('crud_list_scripts')
@endsection
