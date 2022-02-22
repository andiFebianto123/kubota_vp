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
        #tableA tr td, th{
            box-sizing:border-box;
            padding-left: 4px;
            padding-right: 4px;
        }
        td div{
            font-size: 12px;
        }
    </style>
    <style type="text/css">
        /* #tableB tr th {
            border: 1px solid gray;
            text-align: left;
        } */
    </style>
</head>

@php
    $background = "background-color: red;";
@endphp

<body style="padding:0px; margin:0px">
      <div class="container" style="width:100%; height: auto; background-color:transparent; display:block;">
        
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
                $jumlah = 0;
                if ($dbagi > 0) {
                    $jumlah = ceil($jumlahQtyData / $dbagi);
                }
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

                    $qr_code = $delivery->ds_num .'.'.$delivery->ds_line.'|'.$delivery->item.'|'.$qtyAsli;
        ?>
        <?php
            if($tipe == 'genap'){
                // jika increment adalah bilangan ganjil
        ?>
            <div class="box" style="
                width: 49%; 
                height: 240px; 
                display: block;
                margin-bottom: 10px;
                margin-right: 8px;
                float:left;"
            >
                
                <table id="tableA" style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td colspan="3" style="border: 1px solid gray; height: 39.1px;">
                            <div><strong>Item Code</strong> : {{ $delivery->item }}</div>
                        </td>
                    </tr>        
                    <tr>
                        <td colspan="3" style="border: 1px solid gray; height: 39.1px;">
                            <div><strong>Item Description</strong> : {{ $delivery->description }}</div>
                        </td>
                    </tr>                                
                    <tr>
                        <td colspan="2" style="border: 1px solid gray; width:660px; height: 39.1px;">
                            <div><strong>DS Num</strong> : {{ $delivery->ds_num }}-{{ $delivery->ds_line }}</div>
                        </td>
                        <td rowspan="3" style="border: 1px solid gray; height: 39.1px;">
                           <center><img src="data:image/png;base64, {{ base64_encode(QrCode::format('png')->size(100)->generate($qr_code)) }} "></center> 
                        </td>                        
                    </tr>
                    <tr>
                        <td style="border: 1px solid gray; ">
                            <div><strong>PO</strong> : {{ $delivery->po_num }}-{{ $delivery->po_line }}</div>
                        </td>
                        <td style="border: 1px solid gray; ">
                            <div><strong>Qty Ship</strong> : {{ $delivery->qty }}</div>                            
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid gray; ">
                            <div><strong>Ship Date</strong> : {{ $delivery->shipped_date }}</div>
                        </td>
                        <td style="border: 1px solid gray; ">
                            <div><strong>Qty Box</strong> : {{ $qtyAsli }}</div>                            
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
                display: block;
                margin-bottom: 10px;
                margin-right: 8px;
                float:left;"
            >
                
                <table id="tableA" style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td colspan="3" style="border: 1px solid gray; height: 39.1px;">
                            <div><strong>Item Code</strong> : {{ $delivery->item }}</div>
                        </td>
                    </tr>        
                    <tr>
                        <td colspan="3" style="border: 1px solid gray; height: 39.1px;">
                            <div><strong>Item Description</strong> : {{ $delivery->description }}</div>
                        </td>
                    </tr>                                
                    <tr>
                        <td colspan="2" style="border: 1px solid gray; width:660px; height: 39.1px;">
                            <div><strong>DS Num</strong> : {{ $delivery->ds_num }}-{{ $delivery->ds_line }}</div>
                        </td>
                        <td rowspan="3" style="border: 1px solid gray; height: 39.1px;">
                            <center><img src="data:image/png;base64, {{ base64_encode(QrCode::format('png')->size(100)->generate($qr_code)) }} "></center> 
                        </td>                        
                    </tr>
                    <tr>
                        <td style="border: 1px solid gray; ">
                            <div><strong>PO</strong> : {{ $delivery->po_num }}-{{ $delivery->po_line }}</div>
                        </td>
                        <td style="border: 1px solid gray; ">
                        <div><strong>Qty Ship</strong> : {{ $delivery->qty }}</div>                            
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid gray; ">
                            <div><strong>Ship Date</strong> : {{ $delivery->shipped_date }}</div>
                        </td>
                        <td style="border: 1px solid gray; ">
                            <div><strong>Qty Box</strong> : {{ $qtyAsli }}</div>                            
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
            
            if($jumlah == 0) {
                echo "QTY per box tidak boleh kosong";
            }
        ?>
      </div>  
</body>

</html>