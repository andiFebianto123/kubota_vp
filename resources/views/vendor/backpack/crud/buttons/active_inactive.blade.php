<button class="btn btn-sm btn-link" type="button" id="btn-for-active-inactive-{{$entry->getKey()}}" data-button-type="active-or-inactive" data-value="{{$entry->is_active}}" onclick="activeInactiveFunc(this)" data-route="{{ url('admin/active-inactive' . '/' . $entry->getkey()) }}">
    @if($entry->is_active)
    <i class="la la-user-slash"></i> Inactive
    @else
    <i class="la la-user"></i> Active
    @endif
</button>

@push('after_scripts') @if (request()->ajax()) @endpush @endif
<script>

	if (typeof activeInactiveFunc != 'function') {
	  $("[data-button-type=active-or-inactive]").unbind('click');

	  function activeInactiveFunc(button) {
		// ask for confirmation before deleting an item
		// e.preventDefault();
		var route = $(button).attr('data-route');
        var value = $(button).attr('data-value');
        var text;

        if(value == 1 || value === 1 || value === '1'){
            text = "{!! trans('validation.if_inactive') !!}";
        }else{
            text = "{!! trans('validation.if_active') !!}";
        }

        swal({
		  title: "{!! trans('backpack::base.warning') !!}",
		  text: text,
		  icon: "warning",
		  buttons: ["{!! trans('backpack::crud.cancel') !!}", "Yes"],
		}).then((value) => {
			if (value) {
				$.ajax({
			      url: route,
			      type: 'GET',
			      success: function(result) {
                      if(result.status){
                        crud.table.draw(false);
                        new Noty({
		                    type: "success",
		                    text: `<strong>Success</strong><br>${result.message}`
		                }).show();
                      }
			        //   if (result == 1) {
					// 	  // Redraw the table
					// 	  if (typeof crud != 'undefined' && typeof crud.table != 'undefined') {
					// 		  // Move to previous page in case of deleting the only item in table
					// 		  if(crud.table.rows().count() === 1) {
					// 		    crud.table.page("previous");
					// 		  }

					// 		  crud.table.draw(false);
					// 	  }

			        //   	  // Show a success notification bubble
			        //       new Noty({
		            //         type: "success",
		            //         text: "{!! '<strong>'.trans('backpack::crud.delete_confirmation_title').'</strong><br>'.trans('backpack::crud.delete_confirmation_message') !!}"
		            //       }).show();

			        //       // Hide the modal, if any
			        //       $('.modal').modal('hide');
			        //   } else {
			        //       // if the result is an array, it means 
			        //       // we have notification bubbles to show
			        //   	  if (result instanceof Object) {
			        //   	  	// trigger one or more bubble notifications 
			        //   	  	Object.entries(result).forEach(function(entry, index) {
			        //   	  	  var type = entry[0];
			        //   	  	  entry[1].forEach(function(message, i) {
					//           	  new Noty({
				    //                 type: type,
				    //                 text: message
				    //               }).show();
			        //   	  	  });
			        //   	  	});
			        //   	  } else {// Show an error alert
				    //           swal({
				    //           	title: "{!! trans('backpack::crud.delete_confirmation_not_title') !!}",
	                //             text: "{!! trans('backpack::crud.delete_confirmation_not_message') !!}",
				    //           	icon: "error",
				    //           	timer: 4000,
				    //           	buttons: false,
				    //           });
			        //   	  }			          	  
			        //   }
			      },
			      error: function(result) {
			          // Show an alert with the result
			          swal({
		              	title: "{!! trans('validation.failed') !!}",
                        text: "{!! trans('validation.error_message_active') !!}",
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

</script>
@if (!request()->ajax()) @endpush @endif
