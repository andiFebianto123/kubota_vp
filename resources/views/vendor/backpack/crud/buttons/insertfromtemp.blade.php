<a href="javascript:void(0)" onclick="insertToDb(this)" data-route="{{ url($crud->route.'/insert-to-db') }}" class="btn btn-sm btn-primary-vp" data-button-type="insertfromtemp">
    <i class="la la-cloud"></i> Insert
</a>

@push('after_scripts')
<script>
    if (typeof insertToDb != 'function') {
      $("[data-button-type=insertfromtemp]").unbind('click');

      function insertToDb(button) {
          // ask for confirmation before deleting an item
          // e.preventDefault();
          var button = $(button);
          var route = button.attr('data-route');

          $.ajax({
              url: route,
              type: 'POST',
              success: function(result) {
                  // Show an alert with the result
                  console.log(result,route);
                  new Noty({
                      text: "Data has been imported",
                      type: "success"
                  }).show();
                  crud.table.ajax.reload();
                  // Hide the modal, if any
                  $('.modal').modal('hide');
                  setTimeout(
                    function() 
                    {
                        window.history.back()
                    }, 3000);                 
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
    }
</script>
@endpush