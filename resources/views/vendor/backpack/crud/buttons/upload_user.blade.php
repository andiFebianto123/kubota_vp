<a href="javascript:void(0)" 
    class="btn btn-light-vp" 
    data-button-type="updateRole"
    data-toggle="modal" data-target="#upload-modal">
    <i class="la la-upload"></i> Import User
</a>

@push('after_scripts')
<script>
    if (typeof acceptPoAll != 'function') {
      $("[data-button-type=updateRole]").unbind('click');

      $('#updateRoleModal').on('show.bs.modal', function (event) {
          var button = $(event.relatedTarget) // Button that triggered the modal
          // console.log(button);
      });

      $('#updateRoleModal').on('hidden.bs.modal', function(event){
        $('.select2-role').val(null).trigger('change');
        $('#table-permissions tbody').html('');
        $('#checkAllRole')[0].checked = false;
      });
        
    }
</script>
<script>
      $(function(){

      });
  </script>
@endpush