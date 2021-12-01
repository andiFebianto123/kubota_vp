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
        <span class="text-capitalize">{{$entry->purchaseOrder->number}}</span>
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
                    <td>PO</td>
                    <td width="2px">:</td>
                    <td>{{$entry->po_num}}</td>
                </tr>
                <tr>
                    <td>PO Line</td>
                    <td>:</td>
                    <td>{{$entry->po_line}}</td>
                </tr>
                <tr>
                    <td>Item</td>
                    <td>:</td>
                    <td>{{$entry->item}}<br>{{$entry->description}}</td>
                </tr>
                <tr>
                    <td>Qty Order</td>
                    <td>:</td>
                    <td>{{$entry->order_qty}}</td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>:</td>
                    <td>    <span class="{{$arr_po_line_status[$entry->status]['color']}}">
                            {{$arr_po_line_status[$entry->status]['text']}}
                            </span>
                    </td>
                </tr>
                <tr>
                    <td>Unit Price</td>
                    <td>:</td>
                    <td>{{$entry->currency}} {{number_format($entry->unit_price,0,',','.')}}</td>
                </tr>
            </table>
        </div><!-- /.box-body -->
    </div><!-- /.box -->

    <div class="col-md-8">
        <div class="card-header bg-secondary">
            <label class="font-weight-bold mb-0">Create Delivery Sheet</label> 
        </div>
        <div class="card no-padding no-border">
                <form id="form-delivery" method="post"
                        action="{{ url('admin/delivery') }}"
                        @if ($crud->hasUploadFields('create'))
                        enctype="multipart/form-data"
                        @endif
                        >
                    {!! csrf_field() !!}
                    <!-- load the view from the application if it exists, otherwise load the one in the package -->
                    <div class="m-2">
                    @include('crud::inc.show_fields', ['fields' => $crud->fields()])
                    </div>

                    <button id="btn-for-form-delivery" class="btn btn-primary-vp mx-4 mb-4 mt-0" type="button" onclick="submitAfterValid('form-delivery')">Submit</button>
                </form>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-secondary">
               <label class="font-weight-bold mb-0">Delivery Sheet Detail</label> 
            </div>
            <div class="card-body">
                @if(sizeof($deliveries) > 0)
                <form id="form-print-mass-ds" action="{{url('admin/delivery-export-mass-pdf-post')}}" method="post">
                    @csrf
                    <input type="hidden" name="po_num"  value="{{$entry->po_num}}" >
                    <input type="hidden" name="po_line"  value="{{$entry->po_line}}" >

                    <table id="ds-table" class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="check-all-cb" name="print_deliveries" class="check-all" data-delivery="{{sizeof($deliveries)}}" >
                                </th>
                                <th>PO</th>
                                <th>PO Line</th>
                                <th>DS Number</th>
                                <th>DS Line</th>
                                <th>Shipped Date</th>
                                <th>Qty</th>
                                <th>Amount</th>
                                <th>DO Number</th>
                                <th>Operator</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $total_price = 0;
                                $total_qty = 0;
                            @endphp
                            @foreach ($deliveries as $key => $delivery)
                            <tr>
                                <td>
                                    <input type="checkbox" value="{{$delivery->id}}" name="print_delivery[]" class="check-delivery check-{{$delivery->id}}">
                                </td>
                                <td>{{$delivery->po_num}}</td>
                                <td>{{$delivery->po_line}}</td>
                                <td>{{$delivery->ds_num}}</td>
                                <td>{{$delivery->ds_line}}</td>
                                <td>{{date('Y-m-d',strtotime($delivery->shipped_date))}}</td>
                                <td>{{$delivery->order_qty}}</td>
                                <td>{{$delivery->currency}} {{number_format($delivery->unit_price,0,',','.')}}</td>
                                <td>{{$delivery->no_surat_jalan_vendor}}</td>
                                <td>{{$delivery->petugas_vendor}}</td>
                                <td>
                                    <!-- <a href="#" class="btn btn-sm btn-danger"><i class="la la-file-pdf"></i> + Harga</a>
                                    <a href="#" class="btn btn-sm btn-secondary"><i class="la la-file-pdf"></i> - Harga</a> -->
                                    <a href="{{url('admin/delivery/'.$delivery->id.'/show')}}" class="btn btn-sm btn-outline-primary" data-toggle='tooltip' data-placement='top' title="Detail"><i class="la la-qrcode"></i></a>
                                    <a href="javascript:void(0)" onclick="deleteEntry(this)" data-route="{{ url('admin/delivery/'.$delivery->id) }}" class="btn btn-sm btn-outline-danger" data-toggle='tooltip' data-placement='top' data-button-type="delete" title="Delete"><i class="la la-trash"></i></a>
                                </td>
                            </tr>
                            @php
                                $total_qty += $delivery->order_qty;
                                $total_price += $delivery->unit_price*$delivery->order_qty;
                            @endphp
                            @endforeach

                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-center font-weight-bold">
                                    Total
                                </td>
                                <td>
                                    {{$total_qty}}</td>
                                </td>
                                <td>
                                {{$delivery->currency}} {{ number_format($total_price,0,',','.')}}</td>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    <button type="button" id="btn-for-form-print-mass-ds" class="btn btn-sm btn-danger" onclick="submitAfterValid('form-print-mass-ds')"><i class="la la-file-pdf"></i> <span>PDF</span></button>
                </form>

                @else
                <p>No Data Available</p>
                @endif
            </div>

        </div><!-- /.box-body -->
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

@stack('crud_fields_scripts')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
    $(document).ready( function () {
        $('#ds-table').DataTable();
        initializeFieldsWithJavascript('form');
    } );

    var totalChecked = 0

    $('#check-all-cb').change(function () {
        totalChecked = 0
        $(".check-delivery").prop('checked', $(this).prop('checked'))
        anyChecked = $(this).prop('checked')
        $(this).val($(this).prop('checked'))
        if ($(this).prop('checked')) {
            totalChecked = $(this).data('delivery')
        }
        $('#btn-for-form-print-mass-ds span').text('PDF ('+totalChecked+')')
    })

    $('.check-delivery').change(function () {
        if ($(this).prop('checked')==true){
            $(this).prop('checked', true) 
            totalChecked ++
        }else{
            $(this).prop('checked', false)
            totalChecked --
        }

        $('#btn-for-form-print-mass-ds span').text('PDF ('+totalChecked+')')
    })

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
@endsection