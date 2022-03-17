<button type="button" id="btn-for-form-send-mail-new-po" class="btn btn-sm btn-primary-vp" onclick="sendMail(this)">
	<i class="la la-envelope"></i> <span>Send Mail</span>
</button>

@push('after_scripts')
<script>
    var urlMass = "{{url('admin/send-mail-new-po')}}"

	function sendMail(button) {
	    if (typeof crud.checkedItems === 'undefined' || crud.checkedItems.length == 0)
	    {
  	        new Noty({
	          type: "warning",
	          text: "<strong>{!! trans('backpack::crud.bulk_no_entries_selected_title') !!}</strong><br>{!! trans('backpack::crud.bulk_no_entries_selected_message') !!}"
	        }).show();

	      	return;
	    }
        submitAsyncPost('form-send-mail-new-po', {action:urlMass, data: { ids: crud.checkedItems }})
    }
</script>
@endpush