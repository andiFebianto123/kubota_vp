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
            id_tax_payment = $('.comment-modal').attr('data-id-tax-invoice');

        $.ajax({
              url: "{{ url('admin/send-comments') }}",
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
@endsection
