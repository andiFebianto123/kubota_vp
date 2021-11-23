<a href="javascript:void(0)" onclick="acceptPoAll(this)" data-routee="{{ url($crud->route.'/accept_vendor') }}" data-route="{{ url('admin/accept-all-po') }}" class="btn btn-sm btn-primary-vp" data-button-type="acceptPo">
    <i class="la la-check"></i> Send Mail New PO
</a>

@push('after_scripts')
<script>
    if (typeof acceptPoAll != 'function') {
      $("[data-button-type=acceptPo]").unbind('click');

      function acceptPoAll(button) {
          // ask for confirmation before deleting an item
          // e.preventDefault();
          var button = $(button);
          var route = button.attr('data-route');
          $.ajax({
              url: route,
              type: 'GET',
              success: function(result) {
                  // Show an alert with the result
                  // console.log(result,route);
                  new Noty({
                      text: result.message,
                      type: result.alert
                  }).show();

                  // Hide the modal, if any
                  $('.modal').modal('hide');

                  crud.table.ajax.reload();
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