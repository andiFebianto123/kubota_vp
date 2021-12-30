<?php $constant = app('App\Helpers\Constant'); ?>


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
        <span class="text-capitalize"><?php echo e($entry->ds_numb); ?></span>
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

    <?php if($delivery_status): ?>
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-secondary">
                Delivery Status
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>ITEM DETAIL</strong>
                        <table class="table table-striped table-hover">
                            <tr>
                                <td>PO Number</td>
                                <td>: <?php echo e($delivery_status->po_num); ?></td>
                            </tr>
                            <tr>
                                <td>PO Line</td>
                                <td>: <?php echo e($delivery_status->po_line); ?></td>
                            </tr>
                            <tr>
                                <td>Item</td>
                                <td>: <?php echo e($delivery_status->item); ?></td>
                            </tr>
                            <tr>
                                <td>Description</td>
                                <td>: <?php echo e($delivery_status->description); ?></td>
                            </tr>
                        </table>

                    </div>
                    <div class="col-md-6" style="border-left: 1px solid #d9e2ef;">
                        <strong>DELIVERY STATUS</strong>
                        <table class="table table-striped table-hover">
                            <tr>
                                <td>Received</td>
                                <td>: 
                                    <?php if($delivery_status->received_flag == 1): ?>
                                    <i class="la la-check text-success font-weight-bold"></i>
                                    <?php else: ?>
                                    <i class="la la-times text-danger font-weight-bold"></i>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Received Date</td>
                                <td>: 
                                    <?php if($delivery_status->received_date): ?>
                                    <?php echo e($delivery_status->received_date); ?>

                                    <?php else: ?>
                                    -
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Received QTY</td>
                                <td>: <?php echo e($delivery_status->received_qty); ?></td>
                            </tr>
                            <tr>
                                <td>Shipped</td>
                                <td>: <?php echo e($delivery_status->shipped_qty); ?></td>
                            </tr>
                            <tr>
                                <td>Rejected QTY</td>
                                <td>: <span class="text-danger"> <?php echo e($delivery_status->rejected_qty); ?></span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <?php if(backpack_user()->roles->first()->hasPermissionTo('Show Payment Status DS')): ?>
            <div class="card">
                <div class="card-header bg-secondary">
                    Payment Status
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-striped table-hover">
                                <tr>
                                    <td>Unit Price</td>
                                    <td>: <?php echo e($delivery_show->vendor_currency); ?> <?php echo e(number_format($delivery_status->unit_price,0,',','.')); ?></td>
                                </tr>
                                <tr>
                                    <td>Vend. Dlv No</td>
                                    <td>: <?php echo e($delivery_status->no_surat_jalan_vendor); ?></td> 
                                </tr>
                                <tr>
                                    <td>No Faktur Pajak</td>
                                    <td>: <?php echo e($delivery_status->no_faktur_pajak); ?></td>
                                </tr>
                                <tr>
                                    <td>No Voucher</td>
                                    <td>: <?php echo e($delivery_status->no_voucher); ?></td>
                                </tr>
                                <tr>
                                    <td>Bank</td>
                                    <td>: <?php echo e($delivery_status->bank); ?></td>
                                </tr>
                                <tr>
                                    <td>Payment Ref Number</td>
                                    <td>: <?php echo e($delivery_status->payment_ref_num); ?></td>
                                </tr>
                                <tr>
                                    <td>Total</td>
                                    <td>: <?php echo e($delivery_show->vendor_currency); ?> <?php echo e(number_format($delivery_status->unit_price*$delivery_status->received_qty,0,',','.')); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-striped table-hover">
                                <tr>
                                    <td>Payment Plan Date</td>
                                    <td>: <?php echo e($delivery_status->payment_plan_date); ?></td>
                                </tr>
                                <tr>
                                    <td>Payment Est Date</td>
                                    <td>: <?php echo e(date('Y-m-d', strtotime($delivery_status->payment_plan_date))); ?></td>
                                </tr>
                                <tr>
                                    <td>Validated</td>
                                    <td>:
                                        <?php if($delivery_status->received_flag == 1): ?>
                                        <button type="button" class="btn btn-sm btn-success"><i class="la la-check"></i></button>
                                        <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-danger"><i class="la la-times"></i></button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Payment in Proses</td>
                                    <td>: 
                                        <?php if($delivery_status->payment_in_process_flag == 1): ?>
                                        <button type="button" class="btn btn-sm btn-success"><i class="la la-check"></i></button>
                                        <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-danger"><i class="la la-times"></i></button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Executed</td>
                                    <td> :
                                        <?php if($delivery_status->executed_flag == 1): ?>
                                        <button type="button" class="btn btn-sm btn-success"><i class="la la-check"></i></button>
                                        <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-danger"><i class="la la-times"></i></button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Faktur Pajak</td>
                                    <td> :
                                        <?php if(isset($delivery_status->file_faktur_pajak)): ?>
                                        <a class="btn btn-sm btn-link" target="_blank" href="<?php echo e($delivery_status->file_faktur_pajak); ?>" download><i class="la la-cloud-download-alt"></i> Download</a>
                                        <?php else: ?>
                                        Belum Ada
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <!-- <tr>
                                    <td>Invoice</td>
                                    <td> :
                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td>Surat Jalan</td>
                                    <td> :
                                       
                                    </td>
                                </tr> -->
                            </table>
                        </div>
                    </div>
                    
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-secondary">
                Delivery Status
            </div>
            <div class="card-body">
                Tidak Ada Data!
            </div
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('after_styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('packages/backpack/crud/css/crud.css').'?v='.config('backpack.base.cachebusting_string')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('packages/backpack/crud/css/show.css').'?v='.config('backpack.base.cachebusting_string')); ?>">
<style>
    .pdf-table tbody tr td {
        padding: 4px;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('after_scripts'); ?>
<script src="<?php echo e(asset('packages/backpack/crud/js/crud.js').'?v='.config('backpack.base.cachebusting_string')); ?>"></script>
<script src="<?php echo e(asset('packages/backpack/crud/js/show.js').'?v='.config('backpack.base.cachebusting_string')); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make(backpack_view('blank'), \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/vendor/backpack/crud/list-payment-show.blade.php ENDPATH**/ ?>