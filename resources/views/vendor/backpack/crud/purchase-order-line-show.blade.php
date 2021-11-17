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
                    <td>Item</td>
                    <td>: {{$entry->item}}</td>
                </tr>
                <tr>
                    <td>Qty Order</td>
                    <td>: {{$entry->order_qty}}</td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>:   <span class="{{$arr_po_line_status[$entry->status]['color']}}">
                            {{$arr_po_line_status[$entry->status]['text']}}
                            </span>
                    </td>
                </tr>
                <tr>
                    <td>Description</td>
                    <td>: {{$entry->description}}</td>
                </tr>
                <tr>
                    <td>Unit Price</td>
                    <td>: {{"IDR " . number_format($entry->unit_price,0,',','.')}}</td>
                </tr>
            </table>
        </div><!-- /.box-body -->
    </div><!-- /.box -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-secondary">
               <label class="font-weight-bold mb-0">Delivery Sheet Detail</label> 
            </div>
            <div class="card-body">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>DS Number</th>
                            <th>Shipped Date</th>
                            <th>Shipped Qty</th>
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
                            <td>{{$delivery->ds_num}}</td>
                            <td>{{$delivery->shipped_date}}</td>
                            <td>{{$delivery->shipped_qty}}</td>
                            <td>{{"IDR " . number_format($delivery->unit_price,0,',','.')}}</td>
                            <td>{{$delivery->petugas_vendor}}</td>
                            <td>{{$delivery->no_surat_jalan_vendor}}</td>
                            <td>
                                <a href="#" class="btn btn-sm btn-danger"><i class="la la-file-pdf"></i> + Harga</a>
                                <a href="#" class="btn btn-sm btn-secondary"><i class="la la-file-pdf"></i> - Harga</a>
                                <a href="{{url('admin/delivery/'.$delivery->id.'/show')}}" class="btn btn-sm btn-primary"><i class="la la-qrcode"></i> Detail</a>
                                <a href="javascript:void(0)" onclick="deleteEntry(this)" data-route="{{ url('admin/delivery/'.$delivery->id) }}" class="btn btn-sm btn-link" data-button-type="delete"><i class="la la-trash"></i> {{ trans('backpack::crud.delete') }}</a>
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
                            <td colspan="2" class="text-center font-weight-bold">
                                Total
                            </td>
                            <td>
                                {{$total_qty}}</td>
                            </td>
                            <td>
                                {{"IDR " . number_format($total_price,0,',','.')}}</td>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div><!-- /.box-body -->
    </div>
    
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-secondary">
               <label class="font-weight-bold mb-0">Delivery Sheet, Receive dan Reject</label> 
            </div>
            <div class="card-body">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>DS Number</th>
                            <th>Shipped Qty</th>
                            <th>Received Qty</th>
                            <th>Reject Qty</th>
                            <th>On Process Qty (DS)</th>
                            <th>Due Date PO</th>
                            <th>DO Number</th>
                            <th>Operator</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $total_price = 0;
                            $total_qty = 0;
                        @endphp
                        @foreach ($delivery_statuses as $key => $delivery)
                        <tr>
                            <td>{{$delivery->ds_num}}</td>
                            <td>{{$delivery->shipped_qty}}</td>
                            <td>{{$delivery->received_qty}}</td>
                            <td>{{$delivery->rejected_qty}}</td>
                            <td>{{$delivery->no_surat_jalan_vendor}}</td>
                            <td>{{$delivery->no_surat_jalan_vendor}}</td>
                           
                        </tr>
                        @php
                            $total_qty += $delivery->order_qty;
                            $total_price += $delivery->unit_price*$delivery->order_qty;
                        @endphp
                        @endforeach

                    </tbody>
                    
                </table>
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