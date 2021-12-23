<a href="javascript:void(0)" 
    class="btn btn-primary btn-primary-vp" 
    data-button-type="updateRole"
    data-toggle="modal" data-target="#updateRoleModal">
    <i class="la la-universal-access"></i> Add Permission to Role
</a>

<?php $__env->startPush('after_scripts'); ?>
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
        $('.select2-role').select2({
           dropdownParent: $('#updateRoleModal'),
           placeholder: 'Select Role...'
        }).on('select2:select', function (evt) {
        //  console.log(evt)
            var route = $('.select2-role').attr('data-route');
            $('#table-permissions tbody').html('');
            $.ajax({
              url: route,
              type: 'POST',
              data: {
                  role: $('.select2-role').val()
              },
              success: function(result) {
                if(result.status){
                    // jika berhasil
                    // console.log(result);
                    $('#table-permissions tbody').html('');
                    $.each(result.result, function(index, permission){
                      var checked = (permission.slug) ? "checked" : "";
                      var tbody = $(`<tr>
                        <td>${permission.id}</td>
                        <td>${permission.name}</td>
                        <td>${permission.description}</td>
                        <td>
                          <div class="custom-control custom-checkbox mb-3" style="margin-bottom:0px!important;">
                            <input type="checkbox" class="custom-control-input check-role" value="${permission.id}" id="role${permission.id}" name="permission[]" ${checked}>
                            <label class="custom-control-label" for="role${permission.id}"></label>
                          </div>
                        </td>
                      </tr>`);
                      $('#table-permissions tbody').append(tbody);
                    });
                }else{
                    new Noty({
                        type: result.alert,
                        text: result.message
                    }).show();
                }
              },
              error: function(result) {
                  // Show an alert with the result
                  console.log(result)
                  if(result.responseJSON.status == false){
                    new Noty({
                        type: result.responseJSON.alert,
                        text: result.responseJSON.message
                    }).show();
                  }
                  
              }
          });
      });
        $('.select2-role').val(null).trigger('change');
        $('#checkAllRole').click(function(){
          if(this.checked){
            $('.check-role').each(function(){
              this.checked = true
            });
          }else{
            $('.check-role').each(function(){
              this.checked = false
            });
          }
        });

        // click save changes role permission
        $('#role-form').submit(function(e){
          e.preventDefault();
          $.ajax({
              url: $(this).attr('action'),
              type: 'POST',
              data: $(this).serialize(),
              success: function(result){
                if(result.status){
                  $('#updateRoleModal').modal('hide');
                }
                new Noty({
                      type: result.alert,
                      text: result.message
                  }).show();
              },
              error: function(result){
                if(result.responseJSON.status == false){
                    new Noty({
                        type: result.responseJSON.alert,
                        text: result.responseJSON.message
                    }).show();
                }
              }
          });
        });
        $('#change-role').click(function(){
          $('#role-form').submit();
        });

        // TODO : untuk modal permission pada list table

        $('#modalListPermission').on('show.bs.modal', function (event) {
          var role = $(event.relatedTarget).attr('data-permission');
          $.ajax({
            url: $(this).attr('action'),
            type: 'GET',
            data:{
              role: role
            },
            success:function(result){
              if(result.status){
                $('#table-list-permission tbody').html('');
                $('#modal-title-show-permission').html('Permission - '+result.role);
                $.each(result.result, function(i, permission){
                  var tbody = $(`<tr>
                        <td>${permission.id}</td>
                        <td>${permission.name}</td>
                        <td>${permission.description}</td>`);
                  $('#table-list-permission tbody').append(tbody);
                });
              }
            },
            error: function(result){
              if(result.responseJSON.status == false){
                    new Noty({
                        type: result.responseJSON.alert,
                        text: result.responseJSON.message
                    }).show();
              }
            }
          });
        });


      });
  </script>
<?php $__env->stopPush(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/vendor/backpack/crud/buttons/update_role.blade.php ENDPATH**/ ?>