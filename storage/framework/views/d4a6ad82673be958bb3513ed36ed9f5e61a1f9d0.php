<button type="button" id="btn-for-form-print-label" class="btn btn-sm btn-danger" onclick="printLabel(this)"><i class="la la-file-pdf"></i> <span>PDF Label</span></button>

<?php $__env->startPush('after_scripts'); ?>
<script>
    var urlPrintLabel = "<?php echo e(url('admin/delivery-print-label-post')); ?>"

	function printLabel(button) {

	    if (typeof crud.checkedItems === 'undefined' || crud.checkedItems.length == 0)
	    {
  	        new Noty({
	          type: "warning",
	          text: "<strong><?php echo trans('backpack::crud.bulk_no_entries_selected_title'); ?></strong><br><?php echo trans('backpack::crud.bulk_no_entries_selected_message'); ?>"
	        }).show();

	      	return;
	    }
        submitAjaxValid('form-print-label', {action:urlPrintLabel, data: { print_delivery: crud.checkedItems }})
    }
</script>
<?php $__env->stopPush(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/vendor/backpack/crud/buttons/bulk_print_label.blade.php ENDPATH**/ ?>