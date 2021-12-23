<?php $__env->startComponent('mail::message'); ?>
# <?php echo e($details['title']); ?>


<h3><?php echo e($details['message']); ?></h3>

<?php if($details['type'] == 'reminder_po'): ?>
<?php $__env->startComponent('mail::button', ['url' => $details['url_button']]); ?>
    Detail PO
<?php echo $__env->renderComponent(); ?>
<?php elseif($details['type'] == 'forgot_password'): ?>
<?php $__env->startComponent('mail::button', ['url' => $details['fp_url']]); ?>
    Reset Password
<?php echo $__env->renderComponent(); ?>
<?php elseif($details['type'] == 'otp'): ?>
<?php $__env->startComponent('mail::button', ['url' => $details['otp_url'], 'type' => 'OTP']); ?>
    <?php echo $details['otp_code']; ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>

Thanks,<br>
<?php echo e(config('app.name')); ?>

<?php echo $__env->renderComponent(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/office/kubota-vendor-portal/resources/views/emails/sample-mail.blade.php ENDPATH**/ ?>