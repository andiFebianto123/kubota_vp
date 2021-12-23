<?php $__env->startSection('content'); ?>
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-4">
            <img src="<?php echo e(asset('img/logo-kubota.png')); ?>" style="width: 100px;" class="img img-fluid" alt="">
            <div class="card">
                <div class="card-body">
                    <form id="form-login" class="col-md-12 p-t-10"  method="post" action="<?php echo e(route('rectmedia.auth.authenticate')); ?>">
                        <?php echo csrf_field(); ?>


                        <div class="form-group">
                            <label class="control-label">Username</label>

                            <div>
                                <input type="text" class="form-control rect-validation" name="username" id="username">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="password">Password</label>

                            <div>
                                <input type="password" class="form-control rect-validation" name="password" id="password">
                            </div>
                        </div>

                        <div class="form-group">
                            <div>
                                <button type="button" id="btn-for-form-login" onclick="submitAfterValid('form-login')" class="btn btn-block btn-primary-vp">
                                    Login
                                </button>
                            </div>
                            <div class="mt-2">
                                <a href="<?php echo e(route('rectmedia.auth.forgotpassword')); ?>">Forgot Your Password?</a> 
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
                submitAfterValid('form-login')
                return false;    //<---- Add this line
            }
        });
    </script>
    <?php $__env->stopSection(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(backpack_view('layouts.plain'), \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/vendor/backpack/base/auth/login.blade.php ENDPATH**/ ?>