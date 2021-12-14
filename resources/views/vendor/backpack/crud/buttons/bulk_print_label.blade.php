<button type="button" id="btn-for-form-print-label" class="btn btn-sm btn-danger" onclick="printLabel(this)"><i class="la la-file-pdf"></i> <span>PDF Label</span></button>

@push('after_scripts')
<script>
    var urlPrintLabel = "{{url('admin/delivery-print-label-post')}}"

	function printLabel(button) {

	    if (typeof crud.checkedItems === 'undefined' || crud.checkedItems.length == 0)
	    {
  	        new Noty({
	          type: "warning",
	          text: "<strong>{!! trans('backpack::crud.bulk_no_entries_selected_title') !!}</strong><br>{!! trans('backpack::crud.bulk_no_entries_selected_message') !!}"
	        }).show();

	      	return;
	    }
        submitAjaxValid('form-print-label', {action:urlPrintLabel, data: { print_delivery: crud.checkedItems }})
    }
</script>
@endpush