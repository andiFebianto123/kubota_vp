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
            <th>PO</th>
            <th>PO LINE</th>
            <th>Item</th>
            <th>Description</th>
            <th>Unit Price</th>
            <th>Order Qty</th>
            <th>Qty</th>
            <th>DS Delivery Date</th>
            <th>Petugas Vendor</th>
            <th>No Surat Jalan</th>
        </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $po_lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $po_line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($key+1); ?></td>
                <td><?php echo e($po_line->po_num); ?></td>
                <td><?php echo e($po_line->po_line); ?></td>
                <td><?php echo e($po_line->item); ?></td>
                <td><?php echo e($po_line->description); ?></td>
                <td><?php echo e($po_line->unit_price); ?></td>
                <td><?php echo e($po_line->order_qty); ?></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</body>
</html><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/exports/excel/template-mass-ds.blade.php ENDPATH**/ ?>