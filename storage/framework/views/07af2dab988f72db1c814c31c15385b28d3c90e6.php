<a href="javascript:void(0)" onclick="acceptPoAll(this)" data-route="<?php echo e(url('admin/accept-all-po')); ?>" class="btn btn-sm btn-primary-vp" data-button-type="acceptPo">
    <i class="la la-check"></i> Send Mail New PO
</a>

<?php $__env->startPush('after_scripts'); ?>
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
<?php $__env->stopPush(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/office/kubota-vendor-portal/resources/views/vendor/backpack/crud/buttons/accept_vendor.blade.php ENDPATH**/ ?>