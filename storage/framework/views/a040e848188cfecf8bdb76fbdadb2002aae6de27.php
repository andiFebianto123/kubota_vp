<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td>
<?php if(isset($type)): ?>
<span><?php echo e($slot); ?></span>
<?php else: ?>
<a href="<?php echo e($url); ?>" class="button button-<?php echo e($color ?? 'primary'); ?>" target="_blank" rel="noopener"><?php echo e($slot); ?></a>
<?php endif; ?>
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/vendor/mail/html/button.blade.php ENDPATH**/ ?>