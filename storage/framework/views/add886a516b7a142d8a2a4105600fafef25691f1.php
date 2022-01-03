<button type="button" id="btn-for-form-print-mass-ds" class="btn btn-sm btn-danger" onclick="printMassDs(this)"><i class="la la-file-pdf"></i> <span>PDF DS + Harga</span></button>

<?php $__env->startPush('after_scripts'); ?>
<script>
    var urlMassDs = "<?php echo e(url('admin/delivery-export-mass-pdf-post')); ?>"

	function printMassDs(button) {

	    if (typeof crud.checkedItems === 'undefined' || crud.checkedItems.length == 0)
	    {
  	        new Noty({
	          type: "warning",
	          text: "<strong><?php echo trans('backpack::crud.bulk_no_entries_selected_title'); ?></strong><br><?php echo trans('backpack::crud.bulk_no_entries_selected_message'); ?>"
	        }).show();

	      	return;
	    }
        submitAjaxValid('form-print-mass-ds', {action:urlMassDs, data: { print_delivery: crud.checkedItems }})
    }
</script>
<?php $__env->stopPush(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/office/kubota-vendor-portal/resources/views/vendor/backpack/crud/buttons/bulk_print_ds.blade.php ENDPATH**/ ?>