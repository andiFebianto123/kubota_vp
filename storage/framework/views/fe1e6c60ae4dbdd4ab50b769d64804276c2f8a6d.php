<?php
  $defaultBreadcrumbs = [
    trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
    $crud->entity_name_plural => url($crud->route),
    trans('backpack::crud.list') => false,
  ];

  // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
?>

<?php $__env->startSection('header'); ?>
  <div class="container-fluid">
    <h2>
      <span class="text-capitalize"><?php echo $crud->getHeading() ?? $crud->entity_name_plural; ?></span>
      <small id="datatable_info_stack"><?php echo $crud->getSubheading() ?? ''; ?></small>
    </h2>
  </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
  <!-- Default box -->
  <div class="row">

    <!-- THE ACTUAL CONTENT -->
    <div class="<?php echo e($crud->getListContentClass()); ?>">

        <div class="row mb-0">
          <div class="col-sm-6">
            <?php if( $crud->buttons()->where('stack', 'top')->count() ||  $crud->exportButtons()): ?>
              <div class="d-print-none <?php echo e($crud->hasAccess('create')?'with-border':''); ?>">

                <?php echo $__env->make('crud::inc.button_stack', ['stack' => 'top'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

              </div>
            <?php endif; ?>
          </div>
          <div class="col-sm-6">
            <div id="datatable_search_stack" class="mt-sm-0 mt-2 d-print-none"></div>
          </div>
        </div>

        
        <?php if($crud->filtersEnabled()): ?>
          <?php echo $__env->make('crud::inc.filters_navbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endif; ?>

        <table id="crudTable" class="bg-white table table-striped table-hover nowrap rounded shadow-xs border-xs mt-2" cellspacing="0">
            <thead>
              <tr>
                
                <?php $__currentLoopData = $crud->columns(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <th
                    data-orderable="<?php echo e(var_export($column['orderable'], true)); ?>"
                    data-priority="<?php echo e($column['priority']); ?>"
                     

                    
                    <?php if(isset($column['exportOnlyField']) && $column['exportOnlyField'] === true): ?>
                      data-visible="false"
                      data-visible-in-table="false"
                      data-can-be-visible-in-table="false"
                      data-visible-in-modal="false"
                      data-visible-in-export="true"
                      data-force-export="true"
                    <?php else: ?>
                      data-visible-in-table="<?php echo e(var_export($column['visibleInTable'] ?? false)); ?>"
                      data-visible="<?php echo e(var_export($column['visibleInTable'] ?? true)); ?>"
                      data-can-be-visible-in-table="true"
                      data-visible-in-modal="<?php echo e(var_export($column['visibleInModal'] ?? true)); ?>"
                      <?php if(isset($column['visibleInExport'])): ?>
                         <?php if($column['visibleInExport'] === false): ?>
                           data-visible-in-export="false"
                           data-force-export="false"
                         <?php else: ?>
                           data-visible-in-export="true"
                           data-force-export="true"
                         <?php endif; ?>
                       <?php else: ?>
                         data-visible-in-export="true"
                         data-force-export="false"
                       <?php endif; ?>
                    <?php endif; ?>
                  >
                    <?php echo $column['label']; ?>

                  </th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php if( $crud->buttons()->where('stack', 'line')->count() ): ?>
                  <th data-orderable="false"
                      data-priority="<?php echo e($crud->getActionsColumnPriority()); ?>"
                      data-visible-in-export="false"
                      ><?php echo e(trans('backpack::crud.actions')); ?></th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
              <tr>
                
                <?php $__currentLoopData = $crud->columns(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <th><?php echo $column['label']; ?></th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php if( $crud->buttons()->where('stack', 'line')->count() ): ?>
                  <th><?php echo e(trans('backpack::crud.actions')); ?></th>
                <?php endif; ?>
              </tr>
            </tfoot>
          </table>

          <?php if( $crud->buttons()->where('stack', 'bottom')->count() ): ?>
          <div id="bottom_buttons" class="d-print-none text-center text-sm-left">
            <?php echo $__env->make('crud::inc.button_stack', ['stack' => 'bottom'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <div id="datatable_button_stack" class="float-right text-right hidden-xs"></div>
          </div>
          <?php endif; ?>

    </div>

  </div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('after_styles'); ?>
  <!-- DATA TABLES -->
  <link rel="stylesheet" type="text/css" href="<?php echo e(asset('packages/datatables.net-bs4/css/dataTables.bootstrap4.min.css')); ?>">
  <link rel="stylesheet" type="text/css" href="<?php echo e(asset('packages/datatables.net-fixedheader-bs4/css/fixedHeader.bootstrap4.min.css')); ?>">
  <link rel="stylesheet" type="text/css" href="<?php echo e(asset('packages/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css')); ?>">

  <link rel="stylesheet" href="<?php echo e(asset('packages/backpack/crud/css/crud.css').'?v='.config('backpack.base.cachebusting_string')); ?>">
  <link rel="stylesheet" href="<?php echo e(asset('packages/backpack/crud/css/form.css').'?v='.config('backpack.base.cachebusting_string')); ?>">
  <link rel="stylesheet" href="<?php echo e(asset('packages/backpack/crud/css/list.css').'?v='.config('backpack.base.cachebusting_string')); ?>">

  <!-- CRUD LIST CONTENT - crud_list_styles stack -->
  <style>
    .comment-modal .modal-dialog .modal-content .modal-body .modal-message {
      height: 370px;
      overflow: auto;
    }
    .comment-modal .modal-dialog .modal-content .modal-body .modal-message {
      /* background-color: #DDDDDD;*/
    }
    .comment-modal .modal-dialog .modal-content .modal-body .modal-message .message{
      background: white;
      margin: 12px;
      padding: 5px 9px 5px 9px;
    }
    .comment-modal .modal-dialog .modal-content .modal-body .modal-message .message .message-footer{
      padding-top: 8px;
      font-size: 13px;
    }
    .comment-modal .modal-dialog .modal-content .modal-body .input-message {
      padding-top: 12px;
    }
  </style>
  <?php echo $__env->yieldPushContent('crud_list_styles'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('after_scripts'); ?>
  <?php echo $__env->make('crud::inc.datatables_logic', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
  <div class="modal fade comment-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Message</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="modal-message bg-light">
            
          </div>
          <div class="input-message">
            <div class="form-group">
              <label for="exampleFormControlTextarea1">Message</label>
              <textarea class="form-control" id="input_message" rows="3"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onClick="sendMessage(event)">Send Message</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    $('.comment-modal').on('shown.bs.modal', function (e) {
      var id_tax_payment = $(this).attr('data-id-tax-invoice');
      if(id_tax_payment !== undefined){
        loadMessage(id_tax_payment);
      }
    });

    function loadMessage(id){
      $.ajax({
          url: "<?php echo e(url('admin/get-comments')); ?>",
          type: 'POST',
          data: {
            id_payment: id
          },
          success: function(result) {
              // Show an alert with the result
            // console.log(result);
            if(result.status == 'success'){
              $.each(result.result, function(index, value){

                var footerDelete = `
                  <div class="message-footer">
                    <a href="#" class="text-danger" id="delete-message-link" data-id-payment=${value.id}>
                      <i class="la la-trash"></i> Delete
                    </a>
                  </div>`;
                footerDelete = '';

                var createCommentHtml = $(`
                <div class="message">
                  <div class="message-head">
                    <span style="font-size: 0.8rem; margin-right: 3px;" class="${value.style}"><strong>${value.user}</strong></span>
                    <span style="color: #AAAAAA;">${value.time}</span>
                  </div>
                  <div class="message-body">
                      <span>${value.comment}</span>
                  </div>
                  ${(value.status_user == 'You') ? footerDelete : ''}
                </div>`);
                $('.comment-modal .modal-dialog .modal-content .modal-body .modal-message').append(createCommentHtml);
              });
            }
            // membuat fungsi untuk delete message
            $('.comment-modal .modal-dialog .modal-content .modal-body .modal-message .message .message-footer #delete-message-link').on('click', function(e){
              e.preventDefault();
              // console.log($(e.target).parent().parent());
              var id_tax_payment = $(e.target).attr('data-id-payment');
              $.ajax({
                url: "<?php echo e(url('admin/delete-comments')); ?>",
                type: 'POST',
                data: {
                  id: id_tax_payment
                },
                success: function(result){
                  if(result.status == 'success'){
                    $(e.target).parent().parent().hide('fast');
                  }
                },
                error: function(result) {
                  // Show an alert with the result
                  new Noty({
                      text: "The new entry could not be created. Please try again.",
                      type: "warning"
                  }).show();
                }
              })
            });
          },
          error: function(result) {
              // Show an alert with the result
              new Noty({
                  text: "The new entry could not be created. Please try again.",
                  type: "warning"
              }).show();
          }
      });
    };

    $('.comment-modal').on('hidden.bs.modal', function (e) {
      $('.comment-modal .modal-dialog .modal-content .modal-body .modal-message').html('');

    })
  </script>
  <script>
      function sendMessage(e){
        e.preventDefault();
        var messageText = $('#input_message').val(),
            id_tax_payment = $('.comment-modal').attr('data-id-tax-invoice');

        $.ajax({
              url: "<?php echo e(url('admin/send-comments')); ?>",
              type: 'POST',
              data: {
                comment: messageText,
                id_payment: id_tax_payment
              },
              success: function(result) {
                  // Show an alert with the result
                // console.log(result);
                if(result.status == 'success'){
                  $('.comment-modal .modal-dialog .modal-content .modal-body .modal-message').html('');
                  $('#input_message').val('');
                  loadMessage(id_tax_payment);
                    new Noty({
                      text: 'Success send comment',
                      type: 'success'
                    }).show();
                }
                if(result.status == 'failed'){
                  $.each(result.message, function(i, message){
                    new Noty({
                      text: message,
                      type: result.type
                    }).show();
                  });
                }
              },
              error: function(result) {
                  // Show an alert with the result
                  new Noty({
                      text: "The new entry could not be created. Please try again.",
                      type: "warning"
                  }).show();
              }
        });
      }
  </script>
  <script src="<?php echo e(asset('packages/backpack/crud/js/crud.js').'?v='.config('backpack.base.cachebusting_string')); ?>"></script>
  <script src="<?php echo e(asset('packages/backpack/crud/js/form.js').'?v='.config('backpack.base.cachebusting_string')); ?>"></script>
  <script src="<?php echo e(asset('packages/backpack/crud/js/list.js').'?v='.config('backpack.base.cachebusting_string')); ?>"></script>

  <!-- CRUD LIST CONTENT - crud_list_scripts stack -->
  <?php echo $__env->yieldPushContent('crud_list_scripts'); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(backpack_view('blank'), \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/vendor/backpack/crud/list.blade.php ENDPATH**/ ?>