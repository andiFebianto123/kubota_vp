<button class="btn btn-sm btn-link" type="button" id="btn-for-form-reset-password-{{$entry->getKey()}}" data-button-type="reset-password" onclick="resetPassword(this)"  data-username="{{ $entry->username }}" data-email="{{ $entry->email }}" data-route="{{ url('forgot-password') }}">
    <i class="la la-lock"></i> Reset Password
</button>

@push('after_scripts') @if (request()->ajax()) @endpush @endif
<script>

	if (typeof resetPassword != 'function') {
	  $("[data-button-type=reset-password]").unbind('click');

	  function resetPassword(button) {
		// ask for confirmation before deleting an item
		// e.preventDefault();
		var id = $(button).attr('id');
		var route = $(button).attr('data-route');
		var username = $(button).attr('data-username');
		var email = $(button).attr('data-email');

		swal({
		  title: "Reset Password",
		  text: "Are you sure want to reset password for user "+username,
		  icon: "warning",
		  buttons: ["Cancel", "Reset"],
		  dangerMode: true,
		}).then((value) => {
			if (value) {
                submitAsyncPost(id.replace('btn-for-', ''), {action:route, preventReload:true, data: {email: email}})
			}
		});
      }
	}

</script>
@if (!request()->ajax()) @endpush @endif
