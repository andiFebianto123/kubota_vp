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
  <?php // dd($option_role);?>

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

  <link href="<?php echo e(asset('packages/select2/dist/css/select2.min.css')); ?>" rel="stylesheet" type="text/css" />
  <link href="<?php echo e(asset('packages/select2-bootstrap-theme/dist/select2-bootstrap.min.css')); ?>" rel="stylesheet" type="text/css" />

  <link rel="stylesheet" href="<?php echo e(asset('packages/backpack/crud/css/crud.css').'?v='.config('backpack.base.cachebusting_string')); ?>">
  <link rel="stylesheet" href="<?php echo e(asset('packages/backpack/crud/css/form.css').'?v='.config('backpack.base.cachebusting_string')); ?>">
  <link rel="stylesheet" href="<?php echo e(asset('packages/backpack/crud/css/list.css').'?v='.config('backpack.base.cachebusting_string')); ?>">

  <!-- CRUD LIST CONTENT - crud_list_styles stack -->
  <?php echo $__env->yieldPushContent('crud_list_styles'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('after_scripts'); ?>
<div class="modal fade" id="updateRoleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Add Permission to Role</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
          <form id="role-form" action="<?php echo e(url('admin/role/change-role-permission')); ?>">
            <div class="form-group">
              <label><strong>Role :</strong></label>
              <select 
              class="form-control select2-role" 
              style="width: 100%;"
              name="role"
              data-route="<?php echo e(url('admin/role/get-role-permission')); ?>"
              >
                  <?php if($option_role->values()->count() > 0): ?>
                      <?php $__currentLoopData = $option_role; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                          <option value="<?php echo e($key); ?>"><?php echo e($role); ?></option>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  <?php endif; ?>
              </select>
            </div>
              <table class="table table-striped" id="table-permissions">
                <thead>
                  <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Permission</th>
                    <th scope="col">Description</th>
                    <th scope="col">
                    <div class="custom-control custom-checkbox mb-3" style="margin-bottom:0px!important;">
                      <input type="checkbox" class="custom-control-input" id="checkAllRole" name="example1">
                      <label class="custom-control-label" for="checkAllRole"></label>
                    </div>
                    </th>
                  </tr>
                </thead>
              <tbody>
                
              </tbody>
            </table>
          </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="change-role">Save changes</button>
        </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalListPermission" tabindex="-1" action="<?php echo e(url('admin/role/show-role-permission')); ?>" role="dialog" aria-labelledby="exampleModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modal-title-show-permission">Permission</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <table class="table table-striped" id="table-list-permission">
              <thead>
                <tr>
                  <th scope="col">ID</th>
                  <th scope="col">Permission</th>
                  <th scope="col">Description</th>
                </tr>
              </thead>
              <tbody>

              </tbody>
            </table>
        </div>
        </div>
    </div>
</div>
  <?php echo $__env->make('crud::inc.datatables_logic', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
  <script src="<?php echo e(asset('packages/select2/dist/js/select2.full.min.js')); ?>"></script>
  <?php if(app()->getLocale() !== 'en'): ?>
  <script src="<?php echo e(asset('packages/select2/dist/js/i18n/' . str_replace('_', '-', app()->getLocale()) . '.js')); ?>"></script>
  <?php endif; ?>

  <script src="<?php echo e(asset('packages/backpack/crud/js/crud.js').'?v='.config('backpack.base.cachebusting_string')); ?>"></script>
  <script src="<?php echo e(asset('packages/backpack/crud/js/form.js').'?v='.config('backpack.base.cachebusting_string')); ?>"></script>
  <script src="<?php echo e(asset('packages/backpack/crud/js/list.js').'?v='.config('backpack.base.cachebusting_string')); ?>"></script>

  <!-- CRUD LIST CONTENT - crud_list_scripts stack -->
  <?php echo $__env->yieldPushContent('crud_list_scripts'); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(backpack_view('blank'), \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/office/kubota-vendor-portal/resources/views/vendor/backpack/crud/role.blade.php ENDPATH**/ ?>