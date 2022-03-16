<button onclick="insertPrintToDb(this)" type="button" data-redirect="{{url('admin/purchase-order')}}" data-route="{{ url($crud->route.'/print-insert-to-db') }}" class="btn btn-sm btn-primary-vp" data-button-type="insertprintfromtemp">
    <i class="la la-file-pdf"></i> Insert + PDF
</button>

@push('after_scripts')
<script>
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
                new Noty({
                      text: response.message,
                      type: response.alert
                  }).show();
                  
                if (response.status) {
                    if (response.redirect_to) {
                        if (response.newtab) {
                            window.open(response.redirect_to, '_blank');
                        }else{
                            window.location.href = response.redirect_to
                        }
                    }else{
                        setTimeout(function() { 
                            location.reload(true)
                        }, 3000);
                    } 
                    setTimeout(
                    function() 
                    {
                        window.location.replace(redirectTo);
                    }, 2000); 
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