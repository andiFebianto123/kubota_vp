<button onclick="insertToDb(this)" data-route="<?php echo e(url($crud->route.'/print-insert-to-db')); ?>" class="btn btn-sm btn-primary-vp" data-button-type="insertfromtemp">
    <i class="la la-file-pdf"></i> Insert + PDF
</button>

<?php $__env->startPush('after_scripts'); ?>
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
              success: function(response) {
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
                            window.history.back()
                        }, 
                    3000); 
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
    }
</script>
<?php $__env->stopPush(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/vendor/backpack/crud/buttons/insertprintfromtemp.blade.php ENDPATH**/ ?>