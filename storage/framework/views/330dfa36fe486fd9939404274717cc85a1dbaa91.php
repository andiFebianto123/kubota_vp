<?php
$defaultBreadcrumbs = [
trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
$crud->entity_name_plural => url($crud->route),
trans('backpack::crud.preview') => false,
];

// if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
$breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
?>

<?php $__env->startSection('header'); ?>
<section class="container-fluid d-print-none">
    <a href="javascript: window.print();" class="btn float-right"><i class="la la-print"></i></a>
    <h2>
        <span class="text-capitalize"><?php echo e($po->po_num); ?></span>
        <small>Preview</small>
        <?php if($crud->hasAccess('list')): ?>
        <small class=""><a href="<?php echo e(url($crud->route)); ?>" class="font-sm"><i class="la la-angle-double-left"></i> <?php echo e(trans('backpack::crud.back_to_all')); ?> <span><?php echo e($crud->entity_name_plural); ?></span></a></small>
        <?php endif; ?>
    </h2>
</section>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="<?php echo e($crud->getShowContentClass()); ?>">
        <!-- Default box -->
        <div class="">
            <?php if($crud->model->translationEnabled()): ?>
            <div class="row">
                <div class="col-md-12 mb-2">
                    <!-- Change translation button group -->
                    <div class="btn-group float-right">
                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?php echo e(trans('backpack::crud.language')); ?>: <?php echo e($crud->model->getAvailableLocales()[request()->input('locale')?request()->input('locale'):App::getLocale()]); ?> &nbsp; <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <?php $__currentLoopData = $crud->model->getAvailableLocales(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $locale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a class="dropdown-item" href="<?php echo e(url($crud->route.'/'.$entry->getKey().'/show')); ?>?locale=<?php echo e($key); ?>"><?php echo e($locale); ?></a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card-header bg-secondary">
            <label class="font-weight-bold mb-0">Detail</label> 
        </div>
        <div class="card no-padding no-border">
            <table class="table">
                <tr>
                    <td>PO Number</td>
                    <td>: <?php echo e($po->po_num); ?></td>
                </tr>
               
                <tr>
                    <td>Change</td>
                    <td>: <?php echo e($po->po_change); ?></td>
                </tr>
            </table>
        </div><!-- /.box-body -->
    </div><!-- /.box -->
    

    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary-vp">
               <label class="font-weight-bold mb-0">PO Line</label> 
            </div>
            <div class="card-body">
                <?php if(sizeof($po_lines) > 0): ?>
                <table class="table table-striped mb-0 table-responsive">
                    <thead>
                        <tr>
                            <th>PO Number</th>
                            <th>Status</th>
                            <th>Item</th>
                            <th>Vendor Name</th>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>UM</th>
                            <th>Due Date</th>
                            <th>Tax (%)</th>
                            <th>Unit Price</th>
                            <th>Total Price</th>
                            <th>Status Accept</th>
                            <th>Read By</th>
                            <th>Read At</th>
                            <?php if(backpack_auth()->user()->hasRole('Admin PTKI')): ?>
                            <th>Created At</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $po_lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $po_line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="text-nowrap"><?php echo e($po->po_num); ?>-<?php echo e($po_line->po_line); ?></td>
                            <td>
                                <span class="<?php echo e($arr_po_line_status[$po_line->status]['color']); ?>">
                                    <?php echo e($arr_po_line_status[$po_line->status]['text']); ?>

                                </span>
                            </td>
                            <td><?php echo e($po_line->item); ?></td>
                            <td><?php echo e($po_line->vendor_name); ?></td>
                            <td><?php echo e($po_line->description); ?></td>
                            <td><?php echo $po_line->change_order_qty; ?></td>
                            <td><?php echo e($po_line->u_m); ?></td>
                            <td><?php echo $po_line->change_due_date; ?></td>
                            <td><?php echo e($po_line->tax); ?></td>
                            <td class="text-nowrap"><?php echo $po_line->change_unit_price; ?></td>
                            <td class="text-nowrap"><?php echo $po_line->change_total_price; ?></td>
                            <td><?php echo $po_line->reformat_flag_accept; ?></td>
                            <td><?php echo e($po_line->read_by_user); ?></td>
                            <td><?php echo e($po_line->read_at); ?></td>
                            <?php if(backpack_auth()->user()->hasRole('Admin PTKI')): ?>
                            <td><?php echo e($po_line->created_at); ?></td>
                            <?php endif; ?>
                            
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </tbody>
                </table>
                <div class="section-buttons"></div>

                <?php else: ?>
                <p class="text-center">
                    No Data Available
                </p>
                <?php endif; ?>
                
            </div>

        </div><!-- /.box-body -->
    </div>

   
</div>


<?php $__env->stopSection(); ?>

<?php $__env->startSection('after_styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('packages/backpack/crud/css/crud.css').'?v='.config('backpack.base.cachebusting_string')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('packages/backpack/crud/css/show.css').'?v='.config('backpack.base.cachebusting_string')); ?>">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('after_scripts'); ?>

<script src="<?php echo e(asset('packages/backpack/crud/js/crud.js').'?v='.config('backpack.base.cachebusting_string')); ?>"></script>
<script src="<?php echo e(asset('packages/backpack/crud/js/show.js').'?v='.config('backpack.base.cachebusting_string')); ?>"></script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make(backpack_view('blank'), \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/vendor/backpack/crud/purchase-order-detail-change.blade.php ENDPATH**/ ?>