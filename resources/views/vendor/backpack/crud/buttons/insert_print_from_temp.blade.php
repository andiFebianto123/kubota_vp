<button onclick="insertPrintToDb(this)"  id="btn-for-form-print-mass-ds" type="button" data-redirect="{{url('admin/purchase-order')}}" data-route="{{ url($crud->route.'/print-insert-to-db') }}" class="btn btn-sm btn-primary-vp" data-button-type="insertprintfromtemp">
    <i class="la la-file-pdf"></i> Insert + PDF
</button>

@push('after_scripts')
<script>
    var urlMassDs = "{{url('admin/delivery-export-pdf-mass-ds-post')}}"
    
    function insertPrintToDb(button) {
          // ask for confirmation before deleting an item
          // e.preventDefault();
          var button = $(button);
          var route = button.attr('data-route');          
          var redirectTo = button.attr('data-redirect');

          $.ajax({
              url: route,
              type: 'POST',
              success: function(response) {                  
                if (response.status) {
                    submitAjaxValid('form-print-mass-ds', {action:urlMassDs, data: { print_delivery: response.arr_ids }})
                    new Noty({
                        text: response.message,
                        type: response.alert
                    }).show();
                    setTimeout(function() { 
                        window.location.href = redirectTo
                    }, 3000);

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
@endpush