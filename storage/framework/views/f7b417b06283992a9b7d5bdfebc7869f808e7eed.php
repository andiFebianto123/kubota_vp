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
                <th>Serial Number</th>
            </tr>
        </thead>
        <tbody>
            <?php if($qty > 0): ?>
            <?php for($i = 0; $i < $qty; $i++): ?>
            <tr>
                <td><?php echo e($i+1); ?></td>
            </tr>
            <?php endfor; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/office/kubota-vendor-portal/resources/views/exports/excel/template-serial-number.blade.php ENDPATH**/ ?>