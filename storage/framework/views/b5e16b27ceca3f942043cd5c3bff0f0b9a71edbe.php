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
  <br/>
  <br/>
  <div class="row">
    <div class="col">
      <h3>
        <span class="text-capitalize">History Tax Payment</span>
        <small id="datatable2_info_stack"></small>
      </h3>
    </div>
  </div>
  <div class="row">

    <!-- THE ACTUAL CONTENT -->
    <div class="<?php echo e($crud->getListContentClass()); ?>">

        <div class="row mb-0">
          <div class="col-sm-6">
            <?php if( $crud->buttons()->where('stack', 'top')->count() ||  $crud->exportButtons()): ?>
              <div class="d-print-none <?php echo e($crud->hasAccess('create')?'with-border':''); ?>">

                

              </div>
            <?php endif; ?>
          </div>
          <div class="col-sm-6">
            <div id="datatable_search_stack2" class="mt-sm-0 mt-2 d-print-none"></div>
          </div>
        </div>

        
        <?php if($crud->filtersEnabled()): ?>
          <?php echo $__env->make('crud::inc.filters_navbar_custom', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endif; ?>

        <table id="crudTable2" class="bg-white table table-striped table-hover nowrap rounded shadow-xs border-xs mt-2" cellspacing="0">
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

                <?php if( $crud->buttons()->where('stack', 'line_2')->count() ): ?>
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

                <?php if( $crud->buttons()->where('stack', 'line_2')->count() ): ?>
                  <th><?php echo e(trans('backpack::crud.actions')); ?></th>
                <?php endif; ?>
              </tr>
            </tfoot>
          </table>

    </div>

  </div>
<?php $__env->stopSection(); ?>
<?php
  // dd($crud->buttons()->where('stack', 'line_2'));
?>

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
            id_tax_payment = $('.comment-modal').attr('data-id-tax-invoice'),
            route = $('.comment-modal').attr('data-route');

        $.ajax({
              url: route,
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
  <script>
    // NEW JS FILE
    window.crud2 = jQuery.extend(true, {}, window.crud);
    window.crud2.dataTableConfiguration.ajax.url = "<?php echo url($crud->route.'/search2').'?'.Request::getQueryString(); ?>";
    window.crud2.dataTableConfiguration.ajax.method = "POST"

    jQuery(document).ready(function($) {
      window.crud2.table = $("#crudTable2").DataTable(window.crud2.dataTableConfiguration);
      // move search bar
      $("#crudTable2_filter").appendTo($('#datatable_search_stack2' ));
      $("#crudTable2_filter input").removeClass('form-control-sm');

      // move "showing x out of y" info to header
      // <?php if($crud->getSubheading()): ?>
      // $('#crudTable2_info').hide();
      // <?php else: ?>
      // $("#datatable_info_stack").html($('#crudTable2_info')).css('display','inline-flex').addClass('animated fadeIn');
      // <?php endif; ?>
      $('#crudTable2_info').hide();
      $("#datatable2_info_stack").html($('#crudTable2_info')).css('display','inline-flex').addClass('animated fadeIn');

      // move the bottom buttons before pagination
      $("#bottom_buttons").insertBefore($('#crudTable2_wrapper .row:last-child' ));

      // override ajax error message
      $.fn.dataTable.ext.errMode = 'none';
      $('#crudTable2').on('error.dt', function(e, settings, techNote, message) {
          new Noty({
              type: "error",
              text: "<strong><?php echo e(trans('backpack::crud.ajax_error_title')); ?></strong><br><?php echo e(trans('backpack::crud.ajax_error_text')); ?>"
          }).show();
      });

        // when changing page length in datatables, save it into localStorage
        // so in next requests we know if the length changed by user
        // or by developer in the controller.
        $('#crudTable2').on( 'length.dt', function ( e, settings, len ) {
            localStorage.setItem('DataTables_crudTable_/<?php echo e($crud->getRoute()); ?>_pageLength', len);
        });

      // make sure AJAX requests include XSRF token
      $.ajaxPrefilter(function(options, originalOptions, xhr) {
          var token = $('meta[name="csrf_token"]').attr('content');

          if (token) {
                return xhr.setRequestHeader('X-XSRF-TOKEN', token);
          }
      });

      // on DataTable draw event run all functions in the queue
      // (eg. delete and details_row buttons add functions to this queue)
      $('#crudTable2').on( 'draw.dt',   function () {
         crud2.functionsToRunOnDataTablesDrawEvent.forEach(function(functionName) {
            crud2.executeFunctionByName(functionName);
         });
      } ).dataTable();

      // when datatables-colvis (column visibility) is toggled
      // rebuild the datatable using the datatable-responsive plugin
      $('#crudTable').on( 'column-visibility.dt',   function (event) {
         crud2.table.responsive.rebuild();
      } ).dataTable();

      <?php if($crud->getResponsiveTable()): ?>
        // when columns are hidden by reponsive plugin,
        // the table should have the has-hidden-columns class
        crud2.table.on( 'responsive-resize', function ( e, datatable, columns ) {
            if (crud2.table.responsive.hasHidden()) {
              $("#crudTable2").removeClass('has-hidden-columns').addClass('has-hidden-columns');
             } else {
              $("#crudTable2").removeClass('has-hidden-columns');
             }
        } );
      <?php else: ?>
        // make sure the column headings have the same width as the actual columns
        // after the user manually resizes the window
        var resizeTimer;
        function resizeCrudTableColumnWidths() {
          clearTimeout(resizeTimer);
          resizeTimer = setTimeout(function() {
            // Run code here, resizing has "stopped"
            crud2.table.columns.adjust();
          }, 250);
        }
        $(window).on('resize', function(e) {
          resizeCrudTableColumnWidths();
        });
        $('.sidebar-toggler').click(function() {
          resizeCrudTableColumnWidths();
        });
      <?php endif; ?>
    });
  </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(backpack_view('blank'), \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/vendor/backpack/crud/list-payment.blade.php ENDPATH**/ ?>