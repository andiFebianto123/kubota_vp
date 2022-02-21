@extends(backpack_view('blank'))

@php
  $defaultBreadcrumbs = [
    trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
    $crud->entity_name_plural => url($crud->route),
    trans('backpack::crud.list') => false,
  ];

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

        <table id="crudTable" class="bg-white table table-striped table-hover nowrap rounded shadow-xs border-xs mt-2" cellspacing="0">
            <thead>
              <tr>
                {{-- Table columns --}}
                @foreach ($crud->columns() as $column)
                  <th
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
                    {!! $column['label'] !!}
                  </th>
                @endforeach

                @if ( $crud->buttons()->where('stack', 'line')->count() )
                  <th data-orderable="false"
                      data-priority="{{ $crud->getActionsColumnPriority() }}"
                      data-visible-in-export="false"
                      >{{ trans('backpack::crud.actions') }}</th>
                @endif
              </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
              <tr>
                {{-- Table columns --}}
                @foreach ($crud->columns() as $column)
                  <th>{!! $column['label'] !!}</th>
                @endforeach

                @if ( $crud->buttons()->where('stack', 'line')->count() )
                  <th>{{ trans('backpack::crud.actions') }}</th>
                @endif
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

@endsection

@section('after_styles')
  <!-- DATA TABLES -->
  <link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-fixedheader-bs4/css/fixedHeader.bootstrap4.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}">

  <link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/crud.css').'?v='.config('backpack.base.cachebusting_string') }}">
  <link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/form.css').'?v='.config('backpack.base.cachebusting_string') }}">
  <link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/list.css').'?v='.config('backpack.base.cachebusting_string') }}">

  <!-- CRUD LIST CONTENT - crud_list_styles stack -->
  @stack('crud_list_styles')
@endsection

@section('after_scripts')
  @include('crud::inc.datatables_logic')
  <script src="{{ asset('packages/backpack/crud/js/crud.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
  <script src="{{ asset('packages/backpack/crud/js/form.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
  <script src="{{ asset('packages/backpack/crud/js/list.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>

  <!-- CRUD LIST CONTENT - crud_list_scripts stack -->
  @stack('crud_list_scripts')
    @include('bulk_error_table')
  <script>
    $( document ).ajaxStop(function() {
      var crudTableInfo = $("#crudTable_info").text()
      crudTableInfo = crudTableInfo.split("entries")[0]
      $("#crudTable_info").text(crudTableInfo+" entries.")
    });

  </script>
  <div class="modal fade" id="upload-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Upload User</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Silahkan menggunakan template di bawah ini untuk mengimport 
            <br><a href="{{url('admin/template-users')}}">template-users-sheet.xlsx</a>
          </p>
          <form id="form-upload-user" action="{{ backpack_url('user-import') }}" method="POST" enctype="multipart/form-data">
          <div class="form-group" style="border: 1px solid gray; padding: 6px;">
            <input type="file" name="file"/>
          </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button> 
          <button type="button" id="send-file" class="btn btn-primary">
            Submit
          </button>
        </div>
      </div>
    </div>
  </div>
  <script>
    $(function(){
      crudBulkMessages.table = $('#crudTableBulkMessage').DataTable(crudBulkMessages.dataTableConfiguration);
      $('#modal-error-bulk').on('shown.bs.modal', function () {
              crudBulkMessages.table.columns.adjust();
      });

      $('#send-file').click(function(e){
        e.preventDefault();
        $('#form-upload-user').submit();
      });
                        $('#form-upload-user').submit(function(e){
                                e.preventDefault();
                                $('#send-file').attr('disabled','disabled');
                                var form = $(this);
                                var actionUrl = form.attr('action');
                                var dataForm = new FormData();

                                $('#send-file').html(
                                  `
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    Loading...
                                  `
                                );

                                var files = $(this)[0][0].files;

                                if(files.length > 0){
                                        dataForm.append('file', files[0]);
                                }
                                $.ajax({
                                        type: "POST",
                                        url: actionUrl,
                                        data: dataForm, // serializes the form's elements.
                                        processData: false,  // tell jQuery not to process the data
                                        contentType: false,
                                        success: function(result){ 
                                                $(form)[0].reset();
                                                $('#send-file').removeAttr('disabled');
                                                $('#send-file').html(
                                                  `
                                                  Submit
                                                  `
                                                );
                                                if(result.validator){
                                                        new Noty({
                                                            type: "danger",
                                                            text: result.message,
                                                        }).show();
                                                }else{
                                                        if(result.status){
                                                                new Noty({
                                                                        type: "success",
                                                                        text: result.notification,
                                                                }).show();
                                                                $('#upload-modal').modal('hide');
                                                        }else{
                                                                // jika ada data yang error
                                                                crudBulkMessages.table.clear();
                                                                $.each(result.data, function(index, value){
                                                                        crudBulkMessages.table.row.add(value);
                                                                });
                                                                crudBulkMessages.table.draw();
                                                                $('#modal-error-bulk').modal('show');
                                                                new Noty({
                                                                        type: "warning",
                                                                        text: result.notification,
                                                                }).show();
                                                        }
                                                }
                                                
                                        },
                                        error: function (xhr, desc, err)
                                        {
                                                $('#send-file').removeAttr('disabled');
                                                $('#send-file').html(
                                                  `
                                                  Submit
                                                  `
                                                );
                                                new Noty({
                                                        type: 'danger',
                                                        text: err
                                                }).show();
                                        }
                                });
                        });                   

                });
  </script>
@endsection