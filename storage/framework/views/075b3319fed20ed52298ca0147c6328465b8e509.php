<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
    <title><?php echo "Print Label"; ?></title>
    <style type="text/css">
        #header {
            font-size: 26px;
            color: black;
            font-weight: 800;
        }
        /* #tableA {
            width: 100%; 
            border-collapse: collapse;
        }
        #tableA tr th {
            border: 1px solid gray;
            text-align: left;
        }
        */
        #tableA tr td, th{
            box-sizing:border-box;
            padding-left: 5px;
        }
    </style>
    <style type="text/css">
        /* #tableB tr th {
            border: 1px solid gray;
            text-align: left;
        } */
    </style>
</head>

<?php
    $background = "background-color: red;";
?>

<body style="padding:0px; margin:0px">
      <div class="container" style="width:100%; height: auto; background-color:transparent; display:block;">
        <!-- <div class="box" style="
            width: 47%; 
            height: 240px; 
            border: 1px solid black;
            display: block;
            margin-bottom: 10px;
            margin-right: 10px;
            float:left;"
        >
            <p>Hallo andi</p>
        </div>
        <div class="box" style="
            width: 47%; 
            height: 240px; 
            border: 1px solid black;
            margin-bottom: 10px;
            display: block;
            float:left; "
        >
            <p>Hallo Irfan</p>
        </div>
        <div style="clear:both;"></div> -->
        <?php
            $tipe = 'ganjil';
            foreach($data as $delivery){
                # nomor label
                $increment = 1;
                # jumlah pembagiannya
                $dbagi = $delivery->qty_per_box;
                # jumlah qty per ds delivery
                $jumlahQtyData = $delivery->qty;
                # lakukan pembagian agar mengetahui jumlah looping sebanyak
                $jumlah = ceil($jumlahQtyData / $dbagi);
                # reset totalQty
                $qtyTotal = 0;

                for($i = 0; $i<$jumlah; $i++){
                    # melakukan penambahan per qty
                    $qtyTotal += $dbagi;
                    # convert qty asli
                    $qtyAsli = $dbagi;

                    if($qtyTotal > $jumlahQtyData){
                        /*
                            Jika jumlah Total qty lebih dari jumlah qty dari data yang asli
                            akan dilakukan pengurangan dan mengambil sisa pengurangan tersebut
                        */
                        $qtyTotal -= $dbagi;
                        $qtyTotal = $jumlahQtyData - $qtyTotal;
                        $qtyAsli = $qtyTotal;
                    }
        ?>
        <?php
            if($tipe == 'genap'){
                // jika increment adalah bilangan ganjil
        ?>
                <div class="box" style="
                width: 49%; 
                height: 240px; 
                border: 1px solid black;
                display: block;
                margin-bottom: 8px;
                float:left;"
            >
                
            <table id="tableA" style="width: 100%; border-spacing:0px;" cellpadding="0">
                    <tr>
                        <th colspan="2"><div id="header" style="text-align: left; height:32px;">No. <?php echo e($increment); ?></div></th>
                    </tr>
                    <tr>
                        <td style="border: 1px solid gray; height: 39.1px;">
                            <div><strong>PO</strong></div>
                        </td>
                        <td style="border: 1px solid gray; height: 39.1px;">
                            <div><?php echo e($delivery->po_num); ?>-<?php echo e($delivery->po_line); ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid gray; height: 39.1px;">
                            <div><strong>DS Number</strong></div>
                        </td>
                        <td style="border: 1px solid gray; height: 39.1px;">
                            <div><?php echo e($delivery->ds_num); ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid gray; height: 39.1px;">
                            <div><strong>Item Code</strong></div>
                        </td>
                        <td style="border: 1px solid gray; height: 39.1px;">
                            <div><?php echo e($delivery->item); ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid gray; height: 39.1px;">
                            <div><strong>Item Description</strong></div>
                        </td>
                        <td style="border: 1px solid gray; height: 39.1px;">
                            <div><?php echo e($delivery->description); ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid gray; height: 41.1px;">
                            <div><strong>Qty</strong></div>
                        </td>
                        <td style="border: 1px solid gray; height: 41.1px;">
                            <div><?php echo e($qtyAsli); ?></div>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="clear:both;"></div>
        <?php
            }else{
                // jika increment adalah bilangan ganjil
        ?>
            <div class="box" style="
                width: 49%; 
                height: 240px; 
                border: 1px solid black;
                display: block;
                margin-bottom: 8px;
                margin-right: 8px;
                float:left;"
            >
                
                <table id="tableA" style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <th colspan="2"><div id="header" style="text-align: left;">No. <?php echo e($increment); ?></div></th>
                    </tr>
                    <tr>
                        <td style="border: 1px solid gray; height: 38.1px;">
                            <div><strong>PO</strong></div>
                        </td>
                        <td style="border: 1px solid gray; height: 38.1px;">
                            <div><?php echo e($delivery->po_num); ?>-<?php echo e($delivery->po_line); ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid gray; height: 38.1px;">
                            <div><strong>DS Number</strong></div>
                        </td>
                        <td style="border: 1px solid gray; height: 38.1px;">
                            <div><?php echo e($delivery->ds_num); ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid gray; height: 38.1px;">
                            <div><strong>Item Code</strong></div>
                        </td>
                        <td style="border: 1px solid gray; height: 38.1px;">
                            <div><?php echo e($delivery->item); ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid gray; height: 38.1px;">
                            <div><strong>Item Description</strong></div>
                        </td>
                        <td style="border: 1px solid gray; height: 38.1px;">
                            <div><?php echo e($delivery->description); ?></div>
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid gray; height: 38.7px;">
                            <div><strong>Qty</strong></div>
                        </td>
                        <td style="border: 1px solid gray; height: 38.7px;">
                            <div><?php echo e($qtyAsli); ?></div>
                        </td>
                    </tr>
                </table>
            </div>
        <?php } ?>
        <?php
                    $tipe = ($tipe == 'ganjil') ? 'genap' : 'ganjil';
                    $increment++;
                }
            }
        ?>
      </div>  
</body>

</html><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/kubota_vp/kubota-vendor-portal/resources/views/exports/pdf/delivery-sheet-label.blade.php ENDPATH**/ ?>