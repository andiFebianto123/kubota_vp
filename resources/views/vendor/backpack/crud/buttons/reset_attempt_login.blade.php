<button class="btn btn-sm btn-link" type="button" id="btn-for-form-reset-attempt-login-{{$entry->getKey()}}" data-button-type="reset-attempt-login" onclick="resetAttemptLogin(this)"  data-username="{{ $entry->username }}" data-email="{{ $entry->email }}" data-route="{{ backpack_url('user/reset-attempt-login') . '/' . $entry->getKey() }}">
    <i class="la la-dice-five"></i> Reset Attempt Login
</button>

@push('after_scripts') @if (request()->ajax()) @endpush @endif
<script>

	if (typeof resetAttemptLogin != 'function') {
	  $("[data-button-type=reset-attempt-login]").unbind('click');

	  function resetAttemptLogin(button) {
		// ask for confirmation before deleting an item
		// e.preventDefault();
		var id = $(button).attr('id');
		var route = $(button).attr('data-route');
		var username = $(button).attr('data-username');
		var email = $(button).attr('data-email');

		swal({
		  title: "Reset Attempt Login",
		  text: "Are you sure want to reset attempt login for user "+username,
		  icon: "warning",
		  buttons: ["Cancel", "Reset"],
		  dangerMode: true,
		}).then((value) => {
			if (value) {
                submitAsyncPost(id.replace('btn-for-', ''), {action:route, preventReload:true, data: null})
			}
		});
      }
	}

</script>
@if (!request()->ajax()) @endpush @endif
