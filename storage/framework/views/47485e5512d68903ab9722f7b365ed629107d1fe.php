<?php $__env->startSection('content'); ?>
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-4">
            <img src="<?php echo e(asset('img/logo-kubota.png')); ?>" style="width: 100px;" class="img img-fluid" alt="">
            <div class="card">
                <div class="card-body">
                    <form class="col-md-12 p-t-10" id="form-forgot-password" role="form" method="POST" action="<?php echo e(route('forgotpassword.sendlink')); ?>">
                        <?php echo csrf_field(); ?>


                        <div class="form-group">
                            <label class="control-label">Masukkan Email Anda</label>
                            <div>
                                <input type="email" class="form-control rect-validation" name="email">
                            </div>
                        </div>

                        <div class="form-group">
                            <div>
                                <button type="button" id="btn-for-form-forgot-password" onclick="submitAfterValid('form-forgot-password')" class="btn btn-block btn-primary-vp">
                                    Kirim
                                </button>
                                <small>Anda akan menerima email berisi link untuk mengupdate password </small>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php $__env->startSection('after_scripts'); ?>
    <script>
        $('input').keypress(function (e) {
            if (e.which == 13) {
                submitAfterValid('form-forgot-password')
                return false;    //<---- Add this line
            }
        });
    </script>
    <?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>



<?php echo $__env->make(backpack_view('layouts.plain'), \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/vendor/backpack/base/auth/forgot-password.blade.php ENDPATH**/ ?>