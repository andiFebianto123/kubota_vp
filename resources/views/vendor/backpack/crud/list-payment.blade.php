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
  <br/>
  <br/>
  <div class="row">
    <div class="col">
      <h3>
        <span class="text-capitalize">History Tax Payment</span>
        <small id="datatable2_info_stack"></small>
      </h3>
    </div>
  </div>
  <div class="row">

    <!-- THE ACTUAL CONTENT -->
    <div class="{{ $crud->getListContentClass() }}">

        <div class="row mb-0">
          <div class="col-sm-6">
            @if ( $crud->buttons()->where('stack', 'top')->count() ||  $crud->exportButtons())
              <div class="d-print-none {{ $crud->hasAccess('create')?'with-border':'' }}">

                {{-- @include('crud::inc.button_stack', ['stack' => 'top']) --}}

              </div>
            @endif
          </div>
          <div class="col-sm-6">
            <div id="datatable_search_stack2" class="mt-sm-0 mt-2 d-print-none"></div>
          </div>
        </div>

        {{-- Backpack List Filters --}}
        @if ($crud->filtersEnabled())
          @include('crud::inc.filters_navbar_custom')
        @endif

        <table id="crudTable2" class="bg-white table table-striped table-hover nowrap rounded shadow-xs border-xs mt-2" cellspacing="0">
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

                @if ( $crud->buttons()->where('stack', 'line_2')->count() )
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

                @if ( $crud->buttons()->where('stack', 'line_2')->count() )
                  <th>{{ trans('backpack::crud.actions') }}</th>
                @endif
              </tr>
            </tfoot>
          </table>

    </div>

  </div>
@endsection
<?php
  // dd($crud->buttons()->where('stack', 'line_2'));
?>

@section('after_styles')
  <!-- DATA TABLES -->
  <link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-fixedheader-bs4/css/fixedHeader.bootstrap4.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('packages/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}">

  <link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/crud.css').'?v='.config('backpack.base.cachebusting_string') }}">
  <link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/form.css').'?v='.config('backpack.base.cachebusting_string') }}">
  <link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/list.css').'?v='.config('backpack.base.cachebusting_string') }}">

  <!-- CRUD LIST CONTENT - crud_list_styles stack -->
  <style>
    .comment-modal .modal-dialog .modal-content .modal-body .modal-message {
      height: 370px;
      overflow: auto;
    }
    .comment-modal .modal-dialog .modal-content .modal-body .modal-message {
      /* background-color: #DDDDDD;*/
    }
    .comment-modal .modal-dialog .modal-content .modal-body .modal-message .message{
      background: white;
      margin: 12px;
      padding: 5px 9px 5px 9px;
    }
    .comment-modal .modal-dialog .modal-content .modal-body .modal-message .message .message-footer{
      padding-top: 8px;
      font-size: 13px;
    }
    .comment-modal .modal-dialog .modal-content .modal-body .input-message {
      padding-top: 12px;
    }
  </style>
  @stack('crud_list_styles')
@endsection

@section('after_scripts')
  @include('crud::inc.datatables_logic')
  <div class="modal fade comment-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Message</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="modal-message bg-light">
            
          </div>
          <div class="input-message">
            <div class="form-group">
              <label for="exampleFormControlTextarea1">Message</label>
              <textarea class="form-control" id="input_message" rows="3"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onClick="sendMessage(event)">Send Message</button>
        </div>
      </div>
    </div>
  </div>
  <!-- ini adalah batasan untuk layout modal antara comment dan reject -->
  <div class="modal fade reject-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Reject Payment</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <h6>Reject Payment ?</h6>
          <div class="form-group">
            <label for="exampleFormControlTextarea1">Write Reason :</label>
            <textarea class="form-control" id="reject-comment-textarea" rows="5"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onClick="sendReject(event)" >Submit</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    $('.comment-modal').on('shown.bs.modal', function (e) {
      var id_tax_payment = $(this).attr('data-id-tax-invoice');
      if(id_tax_payment !== undefined){
        loadMessage(id_tax_payment);
      }
    });

    function loadMessage(id){
      $.ajax({
          url: "{{ url('admin/get-comments') }}",
          type: 'POST',
          data: {
            id_payment: id
          },
          success: function(result) {
              // Show an alert with the result
            // console.log(result);
            if(result.status == 'success'){
              $.each(result.result, function(index, value){

                var footerDelete = `
                  <div class="message-footer">
                    <a href="#" class="text-danger" id="delete-message-link" data-id-payment=${value.id}>
                      <i class="la la-trash"></i> Delete
                    </a>
                  </div>`;
                footerDelete = '';

                var createCommentHtml = $(`
                <div class="message">
                  <div class="message-head">
                    <span style="font-size: 0.8rem; margin-right: 3px;" class="${value.style}"><strong>${value.user}</strong></span>
                    <span style="color: #AAAAAA;">${value.time}</span>
                  </div>
                  <div class="message-body">
                      <span>${value.comment}</span>
                  </div>
                  ${(value.status_user == 'You') ? footerDelete : ''}
                </div>`);
                $('.comment-modal .modal-dialog .modal-content .modal-body .modal-message').append(createCommentHtml);
              });
            }
            // membuat fungsi untuk delete message
            $('.comment-modal .modal-dialog .modal-content .modal-body .modal-message .message .message-footer #delete-message-link').on('click', function(e){
              e.preventDefault();
              // console.log($(e.target).parent().parent());
              var id_tax_payment = $(e.target).attr('data-id-payment');
              $.ajax({
                url: "{{ url('admin/delete-comments') }}",
                type: 'POST',
                data: {
                  id: id_tax_payment
                },
                success: function(result){
                  if(result.status == 'success'){
                    $(e.target).parent().parent().hide('fast');
                  }
                },
                error: function(result) {
                  // Show an alert with the result
                  new Noty({
                      text: "The new entry could not be created. Please try again.",
                      type: "warning"
                  }).show();
                }
              })
            });
          },
          error: function(result) {
              // Show an alert with the result
              new Noty({
                  text: "The new entry could not be created. Please try again.",
                  type: "warning"
              }).show();
          }
      });
    };

    $('.comment-modal').on('hidden.bs.modal', function (e) {
      $('.comment-modal .modal-dialog .modal-content .modal-body .modal-message').html('');

    })
  </script>
  <script>
      function sendMessage(e){
        e.preventDefault();
        var messageText = $('#input_message').val(),
            id_tax_payment = $('.comment-modal').attr('data-id-tax-invoice'),
            route = $('.comment-modal').attr('data-route');

        $.ajax({
              url: route,
              type: 'POST',
              data: {
                comment: messageText,
                id_payment: id_tax_payment
              },
              success: function(result) {
                  // Show an alert with the result
                // console.log(result);
                if(result.status == 'success'){
                  $('.comment-modal .modal-dialog .modal-content .modal-body .modal-message').html('');
                  $('#input_message').val('');
                  loadMessage(id_tax_payment);
                    new Noty({
                      text: 'Success send comment',
                      type: 'success'
                    }).show();
                    var ajax_table = $("#crudTable").DataTable();
                    ajax_table.ajax.reload(null, false);
                }
                if(result.status == 'failed'){
                  $.each(result.message, function(i, message){
                    new Noty({
                      text: message,
                      type: result.type
                    }).show();
                  });
                }
              },
              error: function(result) {
                  // Show an alert with the result
                  new Noty({
                      text: "The new entry could not be created. Please try again.",
                      type: "warning"
                  }).show();
              }
        });
      }
  </script>
  <script>
   function sendReject(e){
      e.preventDefault();
      var messageText = $('#reject-comment-textarea').val(),
            id_tax_payment = $('.reject-modal').attr('data-id-tax-invoice'),
            route = $('.reject-modal').attr('data-route');
            $.ajax({
              url: route,
              type: 'POST',
              data: {
                comment: messageText,
                id_payment: id_tax_payment
              },
              success: function(result) {
                  // Show an alert with the result
                // console.log(result);
                if(result.status == 'success'){
                  $('#reject-comment-textarea').val('');
                    new Noty({
                      text: 'Success send Reason',
                      type: 'success'
                    }).show();
                    $('.reject-modal').modal('hide');
                    var ajax_table = $("#crudTable").DataTable();
                    ajax_table.ajax.reload(null, false);
                }
                if(result.status == 'failed'){
                  $.each(result.message, function(i, message){
                    new Noty({
                      text: message,
                      type: result.type
                    }).show();
                  });
                }
              },
              error: function(result) {
                  // Show an alert with the result
                  new Noty({
                      text: "The new entry could not be created. Please try again.",
                      type: "warning"
                  }).show();
              }
        });
   }
  </script>
  <script src="{{ asset('packages/backpack/crud/js/crud.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
  <script src="{{ asset('packages/backpack/crud/js/form.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
  <script src="{{ asset('packages/backpack/crud/js/list.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>

  <!-- CRUD LIST CONTENT - crud_list_scripts stack -->
  @stack('crud_list_scripts')
  <script>
    // NEW JS FILE
    window.crud2 = jQuery.extend(true, {}, window.crud);
    window.crud2.dataTableConfiguration.ajax.url = "{!! url($crud->route.'/search2').'?'.Request::getQueryString() !!}";
    window.crud2.dataTableConfiguration.ajax.method = "POST"

    jQuery(document).ready(function($) {
      window.crud2.table = $("#crudTable2").DataTable(window.crud2.dataTableConfiguration);
      // move search bar
      $("#crudTable2_filter").appendTo($('#datatable_search_stack2' ));
      $("#crudTable2_filter input").removeClass('form-control-sm');

      // move "showing x out of y" info to header
      // @if($crud->getSubheading())
      // $('#crudTable2_info').hide();
      // @else
      // $("#datatable_info_stack").html($('#crudTable2_info')).css('display','inline-flex').addClass('animated fadeIn');
      // @endif
      $('#crudTable2_info').hide();
      $("#datatable2_info_stack").html($('#crudTable2_info')).css('display','inline-flex').addClass('animated fadeIn');

      // move the bottom buttons before pagination
      $("#bottom_buttons").insertBefore($('#crudTable2_wrapper .row:last-child' ));

      // override ajax error message
      $.fn.dataTable.ext.errMode = 'none';
      $('#crudTable2').on('error.dt', function(e, settings, techNote, message) {
          new Noty({
              type: "error",
              text: "<strong>{{ trans('backpack::crud.ajax_error_title') }}</strong><br>{{ trans('backpack::crud.ajax_error_text') }}"
          }).show();
      });

        // when changing page length in datatables, save it into localStorage
        // so in next requests we know if the length changed by user
        // or by developer in the controller.
        $('#crudTable2').on( 'length.dt', function ( e, settings, len ) {
            localStorage.setItem('DataTables_crudTable_/{{$crud->getRoute()}}_pageLength', len);
        });

      // make sure AJAX requests include XSRF token
      $.ajaxPrefilter(function(options, originalOptions, xhr) {
          var token = $('meta[name="csrf_token"]').attr('content');

          if (token) {
                return xhr.setRequestHeader('X-XSRF-TOKEN', token);
          }
      });

      // on DataTable draw event run all functions in the queue
      // (eg. delete and details_row buttons add functions to this queue)
      $('#crudTable2').on( 'draw.dt',   function () {
         crud2.functionsToRunOnDataTablesDrawEvent.forEach(function(functionName) {
            crud2.executeFunctionByName(functionName);
         });
      } ).dataTable();

      // when datatables-colvis (column visibility) is toggled
      // rebuild the datatable using the datatable-responsive plugin
      $('#crudTable').on( 'column-visibility.dt',   function (event) {
         crud2.table.responsive.rebuild();
      } ).dataTable();

      @if ($crud->getResponsiveTable())
        // when columns are hidden by reponsive plugin,
        // the table should have the has-hidden-columns class
        crud2.table.on( 'responsive-resize', function ( e, datatable, columns ) {
            if (crud2.table.responsive.hasHidden()) {
              $("#crudTable2").removeClass('has-hidden-columns').addClass('has-hidden-columns');
             } else {
              $("#crudTable2").removeClass('has-hidden-columns');
             }
        } );
      @else
        // make sure the column headings have the same width as the actual columns
        // after the user manually resizes the window
        var resizeTimer;
        function resizeCrudTableColumnWidths() {
          clearTimeout(resizeTimer);
          resizeTimer = setTimeout(function() {
            // Run code here, resizing has "stopped"
            crud2.table.columns.adjust();
          }, 250);
        }
        $(window).on('resize', function(e) {
          resizeCrudTableColumnWidths();
        });
        $('.sidebar-toggler').click(function() {
          resizeCrudTableColumnWidths();
        });
      @endif
        //$('#crudTable2').ajax.reload( null, false ); // user paging is not reset on reload
        // var ajax_table = $("#crudTable2").DataTable();
        // ajax_table.ajax.reload(null, false);
    });
    
    $( document ).ajaxStop(function() {
      var crudTableInfo = $("#crudTable_info").text()
      crudTableInfo = crudTableInfo.split("entries")[0]
      $("#crudTable_info").text(crudTableInfo+" entries.")
    });
  </script>
@endsection
