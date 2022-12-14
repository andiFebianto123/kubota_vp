@inject('constant', 'App\Helpers\Constant')
@extends(backpack_view('blank'))

@php
$defaultBreadcrumbs = [
trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
$crud->entity_name_plural => url($crud->route),
trans('backpack::crud.preview') => false,
];
$action = 'create';

// if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
$breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
<section class="container-fluid d-print-none">
    <a href="javascript: window.print();" class="btn float-right"><i class="la la-print"></i></a>
    <h2>
        <span class="text-capitalize">{{$entry->po_num}}-{{$entry->po_line}}</span>
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
                    <td>PO</td>
                    <td width="2px">:</td>
                    <td>{{$entry->po_num}}-{{$entry->po_line}}</td>
                </tr>
                <tr>
                    <td>DS</td>
                    <td>:</td>
                    <td>{{$entry->ds_num}}-{{$entry->ds_line}}</td>
                </tr>
                <tr>
                    <td>Item</td>
                    <td>:</td>
                    <td>{{$entry->item}}<br>{{$entry->description}}</td>
                </tr>
                <tr>
                    <td>Shipped Qty</td>
                    <td>:</td>
                    <td>{{$entry->shipped_qty}}</td>
                </tr>
                <tr>
                    <td>Received Qty</td>
                    <td>:</td>
                    <td>{{$entry->received_qty}}</td>
                </tr>
                <tr>
                    <td>Rejected Qty</td>
                    <td>:</td>
                    <td>{{$entry->rejected_qty}}</td>
                </tr>
                <tr>
                    <td>Repair Qty</td>
                    <td>:</td>
                    <td>{{$deliveryRepair->repair_qty}}</td>
                </tr>
                <tr>
                    <td>Available Qty</td>
                    <td>:</td>
                    <td>{{$availableQty}}</td>
                </tr>
                
            </table>
        </div><!-- /.box-body -->
    </div><!-- /.box -->

    <div class="col-md-8">
        @if($constant::checkPermission('Create Delivery Sheet'))
        <div class="card-header bg-secondary">
            <label class="font-weight-bold mb-0">Create Delivery Sheet</label> 
        </div>
        <div class="card no-padding no-border">
                @if(sizeof($unfinished_po_line['datas']) > 0)
                <div class="m-4 p-2" style="border:1px solid #ff9800; color:#ff9800;">
                    <b> PO Line yang belum selesai:</b><br>
                    @foreach($unfinished_po_line['datas'] as $key => $upl)
                        {{$key+1}}. {{$upl->po_num."-".$upl->po_line}} ({{date('Y-m-d',strtotime($upl->due_date))}}) {{($upl->total_shipped_qty)?$upl->total_shipped_qty:"0"}}/{{$upl->order_qty}}<br>
                    @endforeach
                </div>
                @endif
                <form id="form-delivery-return" method="post"
                        action="{{ url('delivery-return') }}"
                        >
                    {!! csrf_field() !!}
                    <div class="m-2">
                    @include('crud::inc.show_fields', ['fields' => $crud->fields()])
                    </div>
                    @if(sizeof($unfinished_po_line['datas']) > 0)
                    <button id="btn-for-form-delivery" class="btn btn-sm btn-primary-vp mx-4 mb-4 mt-0" data-toggle="modal" data-target="#modalAlertDueDate" type="button">Submit</button>
                    @else
                    <button id="btn-for-form-delivery" class="btn btn-sm btn-primary-vp mx-4 mb-4 mt-0"  type="button" onclick="submitNewDs()">Submit</button>
                    @endif
                </form>
        </div>
        @endif
    </div>

    <div class="col-md-12">
        @if($constant::checkPermission('Read PO Line Detail'))
        <div class="card">
            <div class="card-header bg-secondary">
               <label class="font-weight-bold mb-0">Delivery Sheet Detail</label> 
            </div>
            <div class="card-body">
                @if(sizeof($deliveryReturns) > 0)

                    <table id="ds-table" class="table table-striped mb-0 table-responsive">
                        <thead>
                            <tr>
                                <th>DS Num</th>
                                <th>DS Line</th>
                                <th>DS Created</th>
                                <th>DS Closed</th>
                                <th>Type Ds</th>
                                <th>No Surat Jalan Vendor</th>
                                <th>Petugas Vendor</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $total_price = 0;
                                $total_amount = 0;
                                $total_qty = 0;
                            @endphp
                            @foreach ($deliveryReturns as $key => $dr)
                            <tr>
                                <td>{{$dr->ds_num}}</td>
                                <td>{{$dr->ds_line}}</td>
                                <td>{{$dr->shipped_qty}}</td>
                                <td>{{$dr->qty}}</td>
                                <td>{{$dr->ds_type}}</td>
                                <td>{{$dr->no_surat_jalan_vendor}}</td>
                                <td>{{$dr->petugas_vendor}}</td>
                                <td style="white-space: nowrap;">
                                    <a href="{{url('delivery-detail/'.$dr->ds_num.'/'.$dr->ds_line)}}" class="btn btn-sm btn-outline-primary" data-toggle='tooltip' data-placement='top' title="DS Detail"><i class="la la-qrcode"></i></a>
                                    <button type="button" id="btn-for-form-print-label-{{$dr->id}}" class="btn btn-sm btn-outline-primary" onclick="printLabelInstant('{{$dr->id}}')"" data-toggle='tooltip'  data-placement='top' title="Print Label"><i class="la la-tag"></i></button>
                                    @if($constant::checkPermission('Delete Delivery Return'))
                                    <a href="javascript:void(0)" onclick="deleteEntry(this)" data-route="{{ url('delivery-return/'.$dr->id) }}" class="btn btn-sm btn-outline-danger" data-toggle='tooltip' data-placement='top' data-button-type="delete" title="Delete"><i class="la la-trash"></i></a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        
                    </table>
                <p>No Data Available</p>
                @endif
            </div>

        </div><!-- /.box-body -->
        @endif
    </div>
</div>

@endsection


@section('after_styles')
<link rel="stylesheet" type="text/css" href="{{asset('packages/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{asset('packages/datatables.net-fixedheader-bs4/css/fixedHeader.bootstrap4.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{asset('packages/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}">

<link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/crud.css').'?v='.config('backpack.base.cachebusting_string') }}">
<link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/show.css').'?v='.config('backpack.base.cachebusting_string') }}">
<link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/form.css').'?v='.config('backpack.base.cachebusting_string') }}">
<link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/create.css').'?v='.config('backpack.base.cachebusting_string') }}">

@stack('crud_fields_styles')
    <style>
      .form-group.required label:not(:empty):not(.form-check-label)::after {
        content: '';
      }
      .form-group.required > label:not(:empty):not(.form-check-label)::after {
        content: ' *';
        color: #ff0000;
      }
    </style>
@endsection

@section('after_scripts')

<script type="text/javascript" src="{{ asset('packages/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('packages/datatables.net-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('packages/datatables.net-responsive/js/dataTables.responsive.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('packages/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('packages/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('packages/datatables.net-fixedheader-bs4/js/fixedHeader.bootstrap4.min.js')}}"></script>

<script src="{{ asset('packages/backpack/crud/js/crud.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
<script src="{{ asset('packages/backpack/crud/js/show.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
<script src="{{ asset('packages/backpack/crud/js/form.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
<script src="{{ asset('packages/backpack/crud/js/create.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
<div id="modalWarningQty" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Peringatan!</h5>
        </div>
        <div class="modal-body">
            <p class="text-accept">
               Apakah Anda yakin akan melanjutkan pembuatan DS?
               <p class="list-error"></p>
            </p>
            <div class="mt-4 text-right">
                <button type="button" class="btn btn-sm btn-outline-vp-primary" onclick="submitAfterValid('form-delivery')">Ya</button>
                <button type="button" class="btn btn-sm btn-outline-danger" data-dismiss="modal">Tidak</button>
            </div>
        </div>
    </div>
  </div>
</div>
<div id="modalAlertDueDate" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title text-danger">Peringatan!</h5>
        </div>
        <div class="modal-body">
            <p class="text-accept">
                {{$unfinished_po_line['message']}}
            </p>
            <button type="button" class="btn btn-sm btn-outline-danger" data-dismiss="modal">Tutup</button>
        </div>
    </div>
  </div>
</div>
@stack('crud_fields_scripts')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
    $(document).ready( function () {
        $('#ds-table').DataTable();
        initializeFieldsWithJavascript('form');
    } );

    function submitNewDs(){
        submitAfterValid('form-delivery-return')

        /*
        var showModal = false
        var htmlErrorMsg = ""
        if($('#current-qty').val() > $('#current-qty').data('max')){
            showModal = true
            htmlErrorMsg += "<li>[DS] Jumlah Qty melebihi batas (max. "+$('#current-qty').data('max')+")</li>"
        }

        if($('*').hasClass('form-issued')){
            $.each($('.has-error-form-issued'), function( k, v ) {
                var num = k+1
                showModal = true
                htmlErrorMsg += "<li>[MI-"+num+"] "+$(this).text()+"</li>"
            })
        } 

        if(showModal){
            $('.list-error').html(htmlErrorMsg)
            $('#modalWarningQty').modal("show")
        }else{
            submitAfterValid('form-delivery-return')
        }
        */
    }


    function initializeFieldsWithJavascript(container) {
      var selector;
      if (container instanceof jQuery) {
        selector = container;
      } else {
        selector = $(container);
      }
      selector.find("[data-init-function]").not("[data-initialized=true]").each(function () {
        var element = $(this);
        var functionName = element.data('init-function');

        if (typeof window[functionName] === "function") {
          window[functionName](element);

          // mark the element as initialized, so that its function is never called again
          element.attr('data-initialized', 'true');
        }
      });
    }

	if (typeof deleteEntry != 'function') {
	  $("[data-button-type=delete]").unbind('click');

	  function deleteEntry(button) {
		// ask for confirmation before deleting an item
		// e.preventDefault();
		var route = $(button).attr('data-route');

		swal({
		  title: "{!! trans('backpack::base.warning') !!}",
		  text: "{!! trans('backpack::crud.delete_confirm') !!}",
		  icon: "warning",
		  buttons: ["{!! trans('backpack::crud.cancel') !!}", "{!! trans('backpack::crud.delete') !!}"],
		  dangerMode: true,
		}).then((value) => {
			if (value) {
				$.ajax({
			      url: route,
			      type: 'DELETE',
			      success: function(result) {
			          if (result == 1) {
						  // Redraw the table
						  if (typeof crud != 'undefined' && typeof crud.table != 'undefined') {
							  // Move to previous page in case of deleting the only item in table
							  if(crud.table.rows().count() === 1) {
							    crud.table.page("previous");
							  }

							  crud.table.draw(false);
						  }

			          	  // Show a success notification bubble
			              new Noty({
		                    type: "success",
		                    text: "{!! '<strong>'.trans('backpack::crud.delete_confirmation_title').'</strong><br>'.trans('backpack::crud.delete_confirmation_message') !!}"
		                  }).show();

			              // Hide the modal, if any
			              $('.modal').modal('hide');
                          location.reload()
			          } else {
			              // if the result is an array, it means 
			              // we have notification bubbles to show
			          	  if (result instanceof Object) {
			          	  	// trigger one or more bubble notifications 
			          	  	Object.entries(result).forEach(function(entry, index) {
			          	  	  var type = entry[0];
			          	  	  entry[1].forEach(function(message, i) {
					          	  new Noty({
				                    type: type,
				                    text: message
				                  }).show();
			          	  	  });
			          	  	});
			          	  } else {// Show an error alert
				              swal({
				              	title: "{!! trans('backpack::crud.delete_confirmation_not_title') !!}",
	                            text: "{!! trans('backpack::crud.delete_confirmation_not_message') !!}",
				              	icon: "error",
				              	timer: 4000,
				              	buttons: false,
				              });
			          	  }			          	  
			          }
			      },
			      error: function(result) {
			          // Show an alert with the result
			          swal({
		              	title: "{!! trans('backpack::crud.delete_confirmation_not_title') !!}",
                        text: "{!! trans('backpack::crud.delete_confirmation_not_message') !!}",
		              	icon: "error",
		              	timer: 4000,
		              	buttons: false,
		              });
			      }
			  });
			}
		});

      }
	}

	// make it so that the function above is run after each DataTable draw event
	// crud.addFunctionToDataTablesDrawEventQueue('deleteEntry');
</script>
@include('vendor.backpack.crud.extendscript-outhouse')
@endsection