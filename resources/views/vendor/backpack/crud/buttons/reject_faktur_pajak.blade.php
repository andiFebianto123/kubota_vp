@if (isset($entry->confirm_flag) && $entry->confirm_flag == 0)
<a href="javascript:void(0)" 
    onclick="rejectFakturPajak(this)" 
    data-route="{{ url('confirm-reject-faktur-pajak/'.$entry->id) }}"
    data-id-tax-invoice="{{ $entry->id }}"
    class="btn btn-sm btn-link" 
    data-button-type="rejectFakturPajak"
>
    <i class="la la-times"></i> Reject
</a>

@push('after_scripts') @if (request()->ajax()) @endpush @endif
<script>
    if (typeof rejectFakturPajak != 'function') {
      $("[data-button-type=rejectFakturPajak]").unbind('click');

      function rejectFakturPajak(button) {
          // ask for confirmation before deleting an item
          var button = $(button);
          var route = button.attr('data-route');
          $('.reject-modal').removeAttr('data-id-tax-invoice');
            let tax_id = button.attr('data-id-tax-invoice');
            if(tax_id !== undefined){
                $('.reject-modal').attr('data-id-tax-invoice', tax_id);
                $('.reject-modal').attr('data-route', route);
            }
          $('.reject-modal').modal('show');
        //   $.ajax({
        //       url: route,
        //       type: 'GET',
        //       success: function(result) {
        //           // Show an alert with the result
        //           if(result){
        //               new Noty({
        //                 text: 'Berhasil melakukan reject',
        //                 type: 'success'
        //               }).show();
        //           }
        //           // Hide the modal, if any
        //           // $('.modal').modal('hide');

        //           crud.table.ajax.reload();
        //       },
        //       error: function(result) {
        //           // Show an alert with the result
        //           new Noty({
        //               text: "The new entry could not be created. Please try again.",
        //               type: "warning"
        //           }).show();
        //       }
        //   });
      }
    }
</script> 
@if (!request()->ajax()) @endpush @endif
@endif