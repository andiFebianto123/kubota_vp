<?php
    $column['text'] = $column['value'] ?? '';
    $column['escaped'] = $column['escaped'] ?? false;
    $column['prefix'] = $column['prefix'] ?? '';
    $column['suffix'] = $column['suffix'] ?? '';

    if(!empty($column['text'])) {
        $column['text'] = $column['prefix'].$column['text'].$column['suffix'];
    }
?>

<?php if($column['text'] == 1): ?>
<span class="text-success">
    <i class="la la-check"></i>
</span>
<?php else: ?>
<span class="text-danger">
    <i class="la la-times"></i>
</span>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/vendor/backpack/crud/columns/flag_checked_html.blade.php ENDPATH**/ ?>