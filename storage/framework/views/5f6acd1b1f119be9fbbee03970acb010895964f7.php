<?php $__env->startSection('content'); ?>

<div class="row mt-2">
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stats" style="background:#f06060; color:#ffffff;">
            <div class="card-body">
                <div class="row">
                    <div class="col-5 col-md-4">
                        <div class="icon-big text-center icon-warning">
                            <i class="la la-book"></i>
                        </div>
                    </div>
                    <div class="col-7 col-md-8">
                        <div class="numbers">
                        <label class="strong">Total PO</label>
                            <h2><?php echo e($count_po_all); ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stats" style="background:#477197; color:#ffffff;">
            <div class="card-body">
                <div class="row">
                    <div class="col-5 col-md-4">
                        <div class="icon-big text-center icon-warning">
                            <i class="la la-newspaper"></i>
                        </div>
                    </div>
                    <div class="col-7 col-md-8">
                        <div class="numbers">
                            <label class="strong">Unread PO Line</label>
                            <h2><?php echo e($count_po_line_unreads); ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stats" style="background:#41a1b1; color:#ffffff;">
            <div class="card-body">
                <div class="row">
                    <div class="col-5 col-md-4">
                        <div class="icon-big text-center icon-warning">
                            <i class="la la-file"></i>
                        </div>
                    </div>
                    <div class="col-7 col-md-8">
                        <div class="numbers">
                            <label class="strong">Delivery Sheet</label>
                            <h2><?php echo e($count_delivery); ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stats" style="background:#837070; color:#ffffff;">
            <div class="card-body">
                <div class="row">
                    <div class="col-5 col-md-4">
                        <div class="icon-big text-center icon-warning">
                            <i class="la la-flag"></i>
                        </div>
                    </div>
                    <div class="col-7 col-md-8">
                        <div class="numbers">
                            <label class="strong">Delivery Status</label>
                            <h2><?php echo e($count_delivery_status); ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="background: #48abac;">
                <h5 class="text-white text-bold mb-0"><i class="la la-folder"></i> Quick Shortcuts</h5>
            </div>
            <div class="card-body">
                <h2>Hi, <?php echo e(Auth::guard('backpack')->user()->username); ?></h2>
                Selamat Datang di Vendor Portal PT. Kubota Indonesia
                <br>Perhatian : Data di Website ini terupdate setiap harinya jam 12.00 WIB dan 18.00 WIB.

                
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: #48abac;">
                <h5 class="text-white text-bold mb-0"><i class="la la-question-circle"></i> Help</h5>
            </div>
            <div class="card-body">
                <div class="accordion" id="accordionExample">
                    <?php $__currentLoopData = $general_message_help; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $gm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="card mb-2">
                        <div class="card-header" id="heading-<?php echo e($key); ?>">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" style="font-weight: bold;" type="button" data-toggle="collapse" data-target="#collapse-<?php echo e($key); ?>" aria-expanded="true" aria-controls="collapse-<?php echo e($key); ?>">
                                    <i class="la la-angle-down"></i>
                                    <?php echo e($gm->title); ?> 
                                </button>
                            </h2>
                        </div>

                        <div id="collapse-<?php echo e($key); ?>" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
                            <div class="card-body">
                            <?php echo e($gm->content); ?>                        
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background: #48abac;">
                <h5 class="text-white text-bold mb-0"> <i class="la la-info-circle"></i> Information</h5>
            </div>
            <div class="card-body">
                <?php $__currentLoopData = $general_message_info; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $gm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="information-section mb-2">
                    <h6><?php echo e($gm->title); ?></h6>
                    <?php echo e($gm->content); ?>

                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                
            </div>
        </div>
    </div>

</div>


<?php $__env->stopSection(); ?>
<?php echo $__env->make(backpack_view('blank'), \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/office/kubota-vendor-portal/resources/views/vendor/backpack/base/dashboard.blade.php ENDPATH**/ ?>