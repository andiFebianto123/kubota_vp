<button type="button" id="btn-for-form-send-mail-new-po-with-attachment" class="btn btn-sm btn-primary-vp" onclick="sendMailWithAttachment(this)">
	<i class="la la-paperclip"></i> <span>Send Mail (With Attachment)</span>
</button>

@push('after_scripts')
<script>
    var urlMassAttachment = "{{url('admin/send-mail-new-po-with-attachment')}}"

	function sendMailWithAttachment(button) {
	    if (typeof crud.checkedItems === 'undefined' || crud.checkedItems.length == 0)
	    {
  	        new Noty({
	          type: "warning",
	          text: "<strong>{!! trans('backpack::crud.bulk_no_entries_selected_title') !!}</strong><br>{!! trans('backpack::crud.bulk_no_entries_selected_message') !!}"
	        }).show();

	      	return;
	    }
        submitAsyncPost('form-send-mail-new-po-with-attachment', {action:urlMassAttachment, data: { ids: crud.checkedItems }})
    }
</script>
@endpush