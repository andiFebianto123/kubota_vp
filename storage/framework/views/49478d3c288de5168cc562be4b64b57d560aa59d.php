<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excel</title>
</head>
<body>
    <table>
        <thead>
        <tr>
            <th>No</th>
            <?php if(backpack_auth()->user()->hasRole('Admin PTKI')): ?>
                <th>Vendor Number</th>
            <?php endif; ?>
            <th>PO Number</th>
            <th>PO Date</th>
            <th>Email Flag</th>
            <th>PO Change</th>
        </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $purchase_orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $po): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($key+1); ?></td>
                <?php if(backpack_auth()->user()->hasRole('Admin PTKI')): ?>
                    <td><?php echo e($po->vendor_number); ?></td>
                <?php endif; ?>
                <td><?php echo e($po->number); ?></td>
                <td><?php echo e(date("Y-m-d", strtotime($po->po_date))); ?></td>
                <td><?php echo e(($po->email_flag) ? "✓":"-"); ?></td>
                <td><?php echo e($po->po_change); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/exports/excel/purchaseorder.blade.php ENDPATH**/ ?>