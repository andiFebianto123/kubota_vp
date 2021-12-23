<a href="javascript:void(0)" onclick="cancelTemp(this)" data-route="<?php echo e(url($crud->route.'/cancel-to-db')); ?>" class="btn btn-sm btn-danger" data-button-type="insertfromtemp">
    <i class="la la-times"></i> Cancel
</a>

<?php $__env->startPush('after_scripts'); ?>
<script>
    if (typeof cancelTemp != 'function') {
      $("[data-button-type=canceltemp]").unbind('click');

      function cancelTemp(button) {
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
                      text: "Cancel Import!",
                      type: "success"
                  }).show();

                  // Hide the modal, if any
                  $('.modal').modal('hide');

                  crud.table.ajax.reload();
                  setTimeout(
                    function() 
                    {
                        window.history.back()
                    }, 3000);

              },
              error: function(result) {
                  // Show an alert with the result
                  new Noty({
                      text: "There's a problem",
                      type: "warning"
                  }).show();
              }
          });
      }
    }
</script>
<?php $__env->stopPush(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/vendor/backpack/crud/buttons/canceltemp.blade.php ENDPATH**/ ?>