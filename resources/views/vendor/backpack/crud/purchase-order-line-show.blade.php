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
                    <td>{{$entry->purchaseOrder->vendor->currency}} {{number_format($entry->unit_price,0,',','.')}}</td>
                </tr>
            </table>
        </div><!-- /.box-body -->
    </div><!-- /.box -->

    <div class="col-md-8">
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

                    @if(sizeof($unfinished_po_line['datas']) > 0)
                    <button id="btn-for-form-delivery" class="btn btn-primary-vp mx-4 mb-4 mt-0" data-toggle="modal" data-target="#modalAlertDueDate" type="button">Submit</button>
                    @else
                    <button id="btn-for-form-delivery" class="btn btn-primary-vp mx-4 mb-4 mt-0"  type="button" onclick="submitNewDs()">Submit</button>
                    @endif
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
                <form id="form-print-mass-ds" action="{{url('admin/delivery-print-label-all')}}" method="post">
                    @csrf
                    <input type="hidden" name="po_num"  value="{{$entry->po_num}}" >
                    <input type="hidden" name="po_line"  value="{{$entry->po_line}}" >

                    <table id="ds-table" class="table table-striped mb-0 table-responsive">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="check-all-cb" name="print_deliveries" class="check-all" data-delivery="{{sizeof($deliveries)}}" >
                                </th>
                                <th>PO</th>
                                <th>DS Number</th>
                                <th>DS Line</th>
                                <th>Group DS</th>
                                <th>Shipped Date</th>
                                <th>Qty</th>
                                <th>Amount ({{$entry->purchaseOrder->vendor->currency}})</th>
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
                                <td style="white-space: nowrap;">{{$delivery->po_num}}-{{$delivery->po_line}}</td>
                                <td>{{$delivery->ds_num}}</td>
                                <td>{{$delivery->ds_line}}</td>
                                <td>{{$delivery->group_ds_num}}</td>
                                <td>{{date('Y-m-d',strtotime($delivery->shipped_date))}}</td>
                                <td>{{$delivery->shipped_qty}}</td>
                                <td>{{number_format($delivery->unit_price,0,',','.')}}</td>
                                <td>{{$delivery->no_surat_jalan_vendor}}</td>
                                <td>{{$delivery->petugas_vendor}}</td>
                                <td style="white-space: nowrap;">
                                    <!-- <a href="#" class="btn btn-sm btn-danger"><i class="la la-file-pdf"></i> + Harga</a>
                                    <a href="#" class="btn btn-sm btn-secondary"><i class="la la-file-pdf"></i> - Harga</a> -->
                                    <a href="{{url('admin/delivery/'.$delivery->id.'/show')}}" class="btn btn-sm btn-outline-primary" data-toggle='tooltip' data-placement='top' title="Detail"><i class="la la-qrcode"></i></a>
                                    <a href="{{url('admin/delivery/'.$delivery->id.'/print_label')}}" class="btn btn-sm btn-outline-primary" data-toggle='tooltip' data-placement="top" title="Print Label"><i class="la la-print"></i></a>
                                    <a href="javascript:void(0)" onclick="deleteEntry(this)" data-route="{{ url('admin/delivery/'.$delivery->id) }}" class="btn btn-sm btn-outline-danger" data-toggle='tooltip' data-placement='top' data-button-type="delete" title="Delete"><i class="la la-trash"></i></a>
                                </td>
                            </tr>
                            @php
                                $total_qty += $delivery->shipped_qty;
                                $total_price += $delivery->unit_price*$delivery->shipped_qty;
                            @endphp
                            @endforeach

                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-center font-weight-bold">
                                    Total
                                </td>
                                <td>
                                    {{$total_qty}}</td>
                                </td>
                                <td>
                                 {{ number_format($total_price,0,',','.')}}</td>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    <button type="button" id="btn-for-form-print-label" class="btn btn-sm btn-danger" onclick="printLabel()"><i class="la la-file-pdf"></i> <span>PDF Label</span></button>
                    <button type="button" id="btn-for-form-print-mass-ds" class="btn btn-sm btn-danger" onclick="printMassDs()"><i class="la la-file-pdf"></i> <span>PDF DS</span></button>
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
            <button type="button" class="btn btn-sm btn-outline-primary" data-dismiss="modal" onclick="submitAfterValid('form-delivery')">Ya</a>
            <button type="button" class="btn btn-sm btn-outline-danger" data-dismiss="modal">Tidak</button>
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
    var urlMassDs = "{{url('admin/delivery-export-mass-pdf-post')}}"
    var urlPrintLabel = "{{url('admin/delivery-print-label-post')}}"

    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
    $(document).ready( function () {
        $('#ds-table').DataTable();
        initializeFieldsWithJavascript('form');
    } );

    function printLabel(){
        $("#form-print-mass-ds").attr('action', urlPrintLabel)
        submitAfterValid('form-print-mass-ds')
    }

    function printMassDs(){
        $("#form-print-mass-ds").attr('action', urlMassDs)
        submitAfterValid('form-print-mass-ds')
    }

    function submitNewDs(){
        var showModal = false
        var htmlErrorMsg = ""
        if($('#current-qty').val() > $('#current-qty').data('max')){
            showModal = true
            htmlErrorMsg += "<li>[DS] Jumlah Qty melebihi batas (max. "+$('#current-qty').val()+")</li>"
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
            submitAfterValid('form-delivery')
        }
    }

    function outhouseTableManager(currentQty){
        $.each($('.form-issued'), function( k, v ) {
            var lotqty = parseFloat($('.form-issued:eq('+k+')').data('lotqty'))
            var qtyper = parseFloat($('.form-issued:eq('+k+')').data('qtyper'))
            var totalQtyPer = parseFloat($('.form-issued:eq('+k+')').data('totalqtyper'))
            var issuedQty =  currentQty*qtyper
            var fixedIssuedQty = (lotqty > issuedQty) ? issuedQty : lotqty
                fixedIssuedQty = parseFloat(fixedIssuedQty).toFixed(2);
            $('.form-issued:eq('+k+')').val(fixedIssuedQty)
            $('.qty-requirement:eq('+k+')').text(fixedIssuedQty)
            
            $( '.form-issued:eq('+k+')' ).keyup(function() {
                var messageErrorHtml = "<br>"
                var messageError = ""
                var anyError = false
                if ($(this).val() > lotqty) {
                    anyError = true
                    messageError += "Lot ("+lotqty+") & "
                }
                if ($(this).val() > issuedQty) {
                    anyError = true
                    messageError += "Req ("+fixedIssuedQty+") & "
                }
                if (anyError) {
                    messageErrorHtml = "<span class='has-error-form-issued'>Qty melebihi "+messageError.slice(0, -2)+"</span>"
                }
                $( '.error-form-issued:eq('+k+')' ).html(messageErrorHtml)
            })
         })        
    }

    var totalChecked = 0

    $('#check-all-cb').change(function () {
        totalChecked = 0
        $(".check-delivery").prop('checked', $(this).prop('checked'))
        anyChecked = $(this).prop('checked')
        $(this).val($(this).prop('checked'))
        if ($(this).prop('checked')) {
            totalChecked = $(this).data('delivery')
        }
        $('#btn-for-form-print-label span').text('PDF Label ('+totalChecked+')')
        $('#btn-for-form-print-mass-ds span').text('PDF DS ('+totalChecked+')')
    })

    $('.check-delivery').change(function () {
        if ($(this).prop('checked')==true){
            $(this).prop('checked', true) 
            totalChecked ++
        }else{
            $(this).prop('checked', false)
            totalChecked --
        }

        $('#btn-for-form-print-label span').text('PDF Label ('+totalChecked+')')
        $('#btn-for-form-print-mass-ds span').text('PDF DS ('+totalChecked+')')
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