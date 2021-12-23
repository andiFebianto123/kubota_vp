<?php
  $defaultBreadcrumbs = [
    trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
    $crud->entity_name_plural => url($crud->route),
    trans('backpack::crud.list') => false,
  ];

  $arr_filter_forecasts = ['day', 'week', 'month'];

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
  <!-- Filter Box -->
  <?php if($filter_vendor): ?>
    <div class="row">
      <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-secondary">
                <strong>Filter</strong>
            </div>
            <div class="card-body">
              <form action="" method="GET">
                <div class="form-group">
                  <label>Filter By Vendor</label>
                  <select 
                    class="form-control select2 select2_filter_vendor" 
                    style="width: 100;"
                    name="filter_vendor"
                  >
                    <?php if(Session::get('vendor_name')): ?>
                      <option value="<?php echo e(Session::get('vendor_name')); ?>" selected><?php echo e(Session::get('vendor_text')); ?></option>
                    <?php else: ?>
                      <option value="hallo" selected>-</option>
                    <?php endif; ?>
                  </select>
                </div>
                <button type="submit" name="vendor_submit" value='1' class="btn btn-sm btn-primary">Submit</button>
              </form>
              </div>
          </div>
      </div>
    </div>
  <?php endif; ?>
  <!-- Default box -->
  <div class="row">

    <!-- THE ACTUAL CONTENT -->
    <div class="<?php echo e($crud->getListContentClass()); ?>">

        <div class="row mb-0">
          <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-secondary">
                    <strong>Filter</strong>
                </div>
                <div class="card-body">
                    <form action="" method="get">
                        <div class="form-group">
                            <label>Filter By</label>
                            <select class="form-control" name="filter_forecast_by" id="">
                                <!-- <option value="-">Pilih</option> -->
                                <?php $__currentLoopData = $arr_filter_forecasts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $aff): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($aff); ?>"  <?php if(request("filter_forecast_by") == $aff): ?> selected <?php endif; ?>><?php echo e(strtoupper($aff)); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <!-- <div class="form-group">
                            <label>Quantity</label>
                            <input type="number" name="quantity" class="form-control">
                        </div> -->
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
          </div>
        
          <div class="col-md-6">
            <div id="datatable_search_stack" class="mt-sm-0 mt-2 d-print-none"></div>
          </div>
        </div>

        
        <?php if($crud->filtersEnabled()): ?>
          <?php echo $__env->make('crud::inc.filters_navbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endif; ?>
        <?php
          // dd($crud->columns()); 
        ?>
        <div>
            <h5>Data Forecast <b> <?php echo e(Session::get("week")); ?> <?php echo e(Session::get("month")); ?> <?php echo e(Session::get("year")); ?></b></h5>
        </div>
        <table id="crudTable" class="bg-white table table-striped table-hover nowrap rounded shadow-xs border-xs mt-2" style="border-collapse: collapse;" cellspacing="0">
            <thead>
              <?php if($crud->type == 'week'): ?>
                <tr>
                  <th></th>
                <?php $__currentLoopData = $crud->columnHeader; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <th colspan="4" style="text-align:center; border:1px solid #ddd;">
                      <?php echo $header; ?>

                  </th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                 
                </tr>
              <?php endif; ?>
              <tr>
                
                <?php $__currentLoopData = $crud->columns(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php if($column['label']): ?>
                    <?php
                      $style = "";
                      if($column['type'] == 'forecast'){
                        if($column['rome_symbol'] == 'I'){
                          $style = "border-left: 1px solid #ddd;";
                        }else if($column['rome_symbol'] == 'IV'){
                          $style = "border-right: 1px solid #ddd;";
                        }
                      }
                    ?>
                    <th
                      style="<?php echo e($style); ?>"
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
                      <?php if(isset($column['link'])): ?>
                      <a href="<?php echo e(url('admin/forecast')); ?><?php echo e($column['link']); ?><?php echo e($column['label']); ?>"><?php echo e($column['label']); ?></a>
                      <?php else: ?>
                      <?php echo $column['label']; ?>

                      <?php endif; ?>
                    </th>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                
              </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
              <tr>
                
                <?php $__currentLoopData = $crud->columns(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <th><?php echo $column['label']; ?></th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                
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
  <script>
    var jobs = <?php echo json_encode($crud); ?>;
  </script>

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
  <?php echo $__env->make('crud::inc.datatables_logic', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
  <!-- include select2 js-->
  <script src="<?php echo e(asset('packages/select2/dist/js/select2.full.min.js')); ?>"></script>
  <?php if(app()->getLocale() !== 'en'): ?>
  <script src="<?php echo e(asset('packages/select2/dist/js/i18n/' . str_replace('_', '-', app()->getLocale()) . '.js')); ?>"></script>
  <?php endif; ?>
  <script type="text/javascript">
    $(function(){
       $('[data-toggle="tooltip"]').tooltip();
       $('.select2_filter_vendor').select2({
           minimumInputLength: 3,
           allowClear: true,
           placeholder: 'Select Vendor',
           ajax: {
              dataType: 'json',
              url: jobs.urlAjaxFilterVendor,
              delay: 500,
              data: function(params) {
                return {
                  term: params.term
                }
              },
              processResults: function (data, page) {
              return {
                results: $.map(data, function(item, key){
                    return {
                      text:item,
                      id:key
                    }
                })
              };
            },
          }
      }).on('select2:select', function (evt) {
         // var data = $(".select2 option:selected").text();
         // alert("Data yang dipilih adalah "+data);
      });
    });
  </script>
  <script src="<?php echo e(asset('packages/backpack/crud/js/crud.js').'?v='.config('backpack.base.cachebusting_string')); ?>"></script>
  <script src="<?php echo e(asset('packages/backpack/crud/js/form.js').'?v='.config('backpack.base.cachebusting_string')); ?>"></script>
  <script src="<?php echo e(asset('packages/backpack/crud/js/list.js').'?v='.config('backpack.base.cachebusting_string')); ?>"></script>
  <!-- CRUD LIST CONTENT - crud_list_scripts stack -->
  <?php echo $__env->yieldPushContent('crud_list_scripts'); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make(backpack_view('blank'), \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/vendor/backpack/crud/forecast-list.blade.php ENDPATH**/ ?>