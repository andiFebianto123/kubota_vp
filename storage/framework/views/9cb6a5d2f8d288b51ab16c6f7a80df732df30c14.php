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
            <th>PO Number</th>
            <th>Item</th>
            <th>Description</th>
            <th>Qty</th>
            <th>UM</th>
            <th>Unit Price</th>
            <th>Total Price</th>
        </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $purchase_order_lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $po): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($key+1); ?></td>
                <td><?php echo e($po->number); ?> - <?php echo e($po->po_line); ?></td>
                <td><?php echo e($po->item); ?></td>
                <td><?php echo e($po->description); ?></td>
                <td><?php echo e($po->order_qty); ?></td>
                <td><?php echo e($po->u_m); ?></td>
                <td><?php echo e($po->unit_price); ?></td>
                <td><?php echo e($po->order_qty*$po->unit_price); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/exports/pdf/purchaseorderline-accept.blade.php ENDPATH**/ ?>