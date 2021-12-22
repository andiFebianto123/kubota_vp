<tr>
<td class="header">
<a href="<?php echo e($url); ?>" style="display: inline-block;">
<?php if(trim($slot) === 'Laravel'): ?>
<!-- <img src="https://laravel.com/img/notification-logo.png" class="logo" alt="Laravel Logo"> -->
<img src="<?php echo e(asset('img/logo-kubota.png')); ?>" class="logo" alt="Kubota Logo">
<?php else: ?>
<?php echo e($slot); ?>

<?php endif; ?>
</a>
</td>
</tr>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/office/kubota-vendor-portal/resources/views/vendor/mail/html/header.blade.php ENDPATH**/ ?>