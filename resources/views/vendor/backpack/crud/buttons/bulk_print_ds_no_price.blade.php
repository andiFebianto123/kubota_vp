<button type="button" id="btn-for-form-print-mass-ds2" class="btn btn-sm btn-danger" onclick="printMassDs2(this)"><i class="la la-file-pdf"></i> <span>PDF DS</span></button>

@push('after_scripts')
<script>
    var urlMassDs2 = "{{url('admin/delivery-export-mass-pdf-post2')}}"

	function printMassDs2(button) {

		if (typeof crud.checkedItems === 'undefined' || crud.checkedItems.length == 0)
		{
			new Noty({
			type: "warning",
			text: "<strong>{!! trans('backpack::crud.bulk_no_entries_selected_title') !!}</strong><br>{!! trans('backpack::crud.bulk_no_entries_selected_message') !!}"
			}).show();

			return;
		}
		submitAjaxValid('form-print-mass-ds2', {action:urlMassDs2, data: { print_delivery: crud.checkedItems }})
	}
</script>
@endpush