<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF</title>
</head>
<style>
    table{
        font-size: 12px;
    }
</style>
<body>
    <span>Order Sheet PT KUBOTA INDONESIA</span>
    <div>
        <table class="table">
            <tr>
                <td>PO Number</td>
                <td>: <?php echo e($po->po_num); ?></td>
            </tr>
            <tr>
                <td>Vendor</td>
                <td>: <?php echo e($po->vend_num); ?></td>
            </tr>
            <tr>
                <td>PO Date</td>
                <td>: <?php echo e(date('Y-m-d', strtotime($po->po_date))); ?></td>
            </tr>
            <tr>
                <td>Email Sent</td>
                <td>: <?php echo e(($po->email_flag) ? "✓":"-"); ?></td>
            </tr>
        </table>
    </div>

    <div>
        <h5>List PO Line</h5>
        <table class="table table-striped mb-0 table-responsive" >
            <thead  style="border-top: 1px solid #000000 ; border-bottom: 1px solid #000000 ;">
                <tr>
                    <th>PO Number</th>
                    <th>Status</th>
                    <th>Item</th>
                    <th>Vendor Name</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>UM</th>
                    <th>Due Date</th>
                    <th>Tax (%)</th>
                    <th>Unit Price</th>
                    <th>Total Price</th>
                    <th>Status Accept</th>
                    <th>Read By</th>
                    <th>Read At</th>
                    <?php if(backpack_auth()->user()->hasRole('Admin PTKI')): ?>
                        <th>Created At</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $po_lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $po_line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="text-nowrap"><?php echo e($po_line->po_num); ?>-<?php echo e($po_line->po_line); ?></td>
                    <td>
                        <span class="<?php echo e($arr_po_line_status[$po_line->status]['color']); ?>">
                            <?php echo e($arr_po_line_status[$po_line->status]['text']); ?>

                        </span>
                    </td>
                    <td><?php echo e($po_line->item); ?></td>
                    <td><?php echo e($po_line->vendor_name); ?></td>
                    <td><?php echo e($po_line->description); ?></td>
                    <td><?php echo $po_line->order_qty; ?></td>
                    <td><?php echo e($po_line->u_m); ?></td>
                    <td><?php echo $po_line->due_date; ?></td>
                    <td><?php echo e($po_line->tax); ?></td>
                    <td class="text-nowrap"><?php echo $po_line->unit_price; ?></td>
                    <td class="text-nowrap"><?php echo $po_line->total_price; ?></td>
                    <td><?php echo $po_line->flag_accept; ?></td>
                    <td><?php echo e($po_line->read_by_user); ?></td>
                    <td><?php echo e($po_line->read_at); ?></td>
                    <?php if(backpack_auth()->user()->hasRole('Admin PTKI')): ?>
                        <td><?php echo e($po_line->created_at); ?></td>
                    <?php endif; ?>
                   
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            </tbody>
        </table>

    </div>

    
</body>

</html><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/exports/excel/order-sheet.blade.php ENDPATH**/ ?>