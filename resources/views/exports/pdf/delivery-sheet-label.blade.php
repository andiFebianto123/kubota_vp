<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo "Hallo semua"; ?></title>

    <style type="text/css" media="all">
    </style>
</head>

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
                $dbagi = 24;
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
                    margin-bottom: 10px;
                    display: block;
                    float:left; "
                >
                    <strong>{{ $increment }}</strong>
                    <p>{{ $delivery->id }}</p>
                    <p>{{ $delivery->ds_num }}</p>
                    <p>{{ $qtyAsli }}</p>
                </div>
                <div style="clear:both;"></div>
        <?php
            }else{
                // jika increment adalah bilangan genap
        ?>
            <div class="box" style="
                width: 49%; 
                height: 240px; 
                border: 1px solid black;
                display: block;
                margin-bottom: 10px;
                margin-right: 10px;
                float:left;"
            >
                <strong>{{ $increment }}</strong>
                <p>{{ $delivery->id }}</p>
                <p>{{ $delivery->ds_num }}</p>
                <p>{{ $qtyAsli }}</p>
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

</html>