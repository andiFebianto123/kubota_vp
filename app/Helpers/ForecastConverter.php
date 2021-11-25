<?php
namespace App\Helpers;
use DateTime;

class forecastConverter {

    # menyimpan data array semua tanggal dari tgl awal sampai target
    var $dataTglPerDay = [];

    var $fromDate = "";
    var $targetDate = "";

    # type forecast
    var $type = '';

    var $contohData = [
        [
            'id' => 1,
            'name_item' => 'Item 1',
            'tgl_request_update' => '2021-11-17 01:00:00',
            'qty' => 12
        ],
        [
            'id' => 10,
            'name_item' => 'Item 3',
            'tgl_request_update' => '2021-11-17 05:00:89',
            'qty' => 40
        ],
        [
            'id' => 4,
            'name_item' => 'Item 2',
            'tgl_request_update' => '2021-11-19 03:00:00',
            'qty' => 10
        ],
        [
            'id' => 5,
            'name_item' => 'Item 2',
            'tgl_request_update' => '2021-12-20 04:00:00',
            'qty' => 78
        ],
        [
            'id' => 2,
            'name_item' => 'Item 1',
            'tgl_request_update' => '2021-11-17 01:03:00',
            'qty' => 5
        ],
        [
            'id' => 3,
            'name_item' => 'Item 1',
            'tgl_request_update' => '2021-12-07 02:00:00',
            'qty' => 65
        ],
    ];

    var $name_items = []; // aggregat item

    var $dataDatePerWeek = []; // ini hanya dipakai untuk type mingguan

    private $resultForecastForDays = [];
    private $resultForecastForWeeks = [];

    /**
     * Method yang bertugas untuk melakukan screening data sebelum proses perhitungan data dimulai
     * @param void
     * @throws void
     * @return Object() # Mengembalikan nilai object dari class ini setelah semua perubahan terjadi
     */
    function forecastStart(){
        # Mendapatkan semua data tanggal
        $this->forecastDateToConvert();

        #proses mendapatkan data $contohData
            // $this->contohData = [];
        #end

        #proses mendapatkan aggregat data termasuk olahan data aggregation berdasarkan pagination
            $u = collect($this->contohData);
            $unique = $u->unique('name_item');
            $unique_sort = $unique->sortBy([
                ['id', 'desc']
            ])
            ->map(function($value, $key){
                return $value['name_item'];
            });
            $this->name_items = $unique_sort->all(); // ['Item 3', 'Item 2', 'Item 1']
        #end

        $d = $this->dateNow();
        if($this->type == 'days'){
            /*
                jika type adalah 'days' maka perlu dilakukan pemotongan terhadap data tanggal karena secara default semua data tanggal di mulai dari tanggal 01, karena yang diperlukan hanyalah tanggal sekarang sehingga data perlu di cut sampai index pertama berada di posisi tanggal sekarang
            */
            $dd = (int) $d['date'][2];
            $dataTglPerDay_slice = array_slice($this->dataTglPerDay, ($dd - 1));
            $this->dataTglPerDay = $dataTglPerDay_slice;

        }else if($this->type == 'week'){
            # jika tipe adalah minggu
            $this->forecastDateToConvertToWeek();
        }

        return $this;
    }

    /**
     * Method untuk memproses perhitungan forecast dan mendapat data dari nilai forecast untuk langsung dipakai
     * pada datatable backpack
     * @param void
     * @throws void
     * @return array(
     *      [0] => Item 1
            [1] => 5
            ...,
            'View'
     * )
     */
    function getResultForecast(){
        if($this->type == 'days'){
            # jika tipe adalah hari
            foreach ($this->name_items as $value) {
                $this->prosesDataPerItem($value);
            }
            return $this->resultForecastForDays;
        }else if($this->type == 'week'){
            #jika tipe adalah mingguan
            foreach ($this->name_items as $value) {
                $this->prosesDataPerItemForWeek($value);
            }
            return $this->resultForecastForWeeks;
        }
        return 0;
    }

    /**
     * Method untuk mendapatkan nilai column yang nantinya dipakai pada column tabel backpack nya
     * @param void
     * @throws void
     * @return array(
        21 Jan 21,
        ...,
        21 Jan 22
     )
     */
    public function getColumns(){
        $dataColumn = [];
        switch ($this->type) {
            case 'days':
                foreach ($this->dataTglPerDay as $date) {
                    #$date --> Y-m-d
                    $newDate = new DateTime($date);
                    array_push($dataColumn, $newDate->format('d M y'));
                }	
                break;
            case 'week':
                foreach ($this->dataDatePerWeek as $weekDate) {
                    # $weekDate --> data array harian perminggu
                    $dateArray = collect($weekDate);
                    $dateSplit = $dateArray->map(function($item, $key){
                        $convertDate = new DateTime($item);
                        return explode("-", $convertDate->format('y-M-d'));
                    });
                    // per data tanggal akan menjadi --> [y, M, d]
                    if($dateArray->count() > 1){
                        # data tanggal dalam seminggu lebih dari 1 hari
                        $firstDate = $dateSplit->first()[2]; // tanggal awal 
                        $lastDate = $dateSplit->last()[2]; // tanggal akhir
                        $moon = $dateSplit->last()[1]; // bulan
                        $year = $dateSplit->last()[0]; // tahun
                        array_push($dataColumn, "{$firstDate}-{$lastDate} {$moon} {$year}");
                    }else{
                        # jika hanya satu hari
                        $date = $dateSplit->first()[2]; // hari
                        $moon = $dateSplit->first()[1]; // bulan
                        $year = $dateSplit->first()[0]; // tahun
                        array_push($dataColumn, "{$date} {$moon} {$year}");
                    }
                }
            default:
                # code...
                break;
        }
        return $dataColumn;
    }

    /**
     * Method untuk memproses perhitungan qty per item jika type forecast adalah days
     * @param string $str --> nama item
     * @throws void
     * @return void
     */
    private function prosesDataPerItem($str){
        # Mencari semua data forecast berdasarkan nama item untuk proses perhitungan
        $u = collect($this->contohData); // mengoleksi semua data sampel

        # memilih semua data berdasarkan name_item = $str
        $keys = $u->filter(function ($value) use($str) {
            return $value['name_item'] == $str;
        });
        # kumpulkan semua data setelah proses filter
        $data = $keys->values()->all();

        $itemForecast = ["<span>{$str}</span>"]; // isi data forecast pertama dengan nama item
        $qtyBefore = 0;
        $iterasi = 0;

        foreach ($this->dataTglPerDay as $date){
            # format $date : d-m-Y
            # pencarian qty item berdasarkan tanggal
            $key = $keys->filter(function ($value) use($date){
                return false !== stristr($value['tgl_request_update'], $date);           
            });
            $jumlah = $key->values()->count();
            if($jumlah > 0){
                # jika ada datanya berdasarkan tanggal yang dituju
                if($jumlah > 1){
                    # bila jumlah minimal 2 atau lebih data
                    # data item akan dicari berdasarkan inputan terakhir
                    $qtyBefore = (int) $key->sortBy([
                        ['id', 'desc']
                    ])->first()['qty'];
                }else{
                    $qtyBefore = (int) $key->first()['qty'];
                }
                array_push($itemForecast, "<span style='color:green;'>{$qtyBefore}</span>");
            }else{
                # jika tidak ada datanya
                if($iterasi > 0){
                    # jika iterasi lebih dari 0 alias sudah looping sekali
                    # tapi masih tidak ada datanya
                    array_push($itemForecast, "<span style='color: blue;'>{$qtyBefore}</span>");
                }else{
                    # jika berada di urutan looping pertama kog datanya tidak ada maka bisa dicari data di 
                    # tgl sebelumnya ada atau data terakhir
                    # di mysql
                    $qtyBefore = 0;
                    $searchBeforeDate = $keys->filter(function ($value) use($date){
                        return $value['tgl_request_update'] < $date;
                    });
                    if($searchBeforeDate->values()->count() > 0){
                        if($searchBeforeDate->values()->count() > 1){
                            # Jika ada lebih dari 1 data
                            $qtyBefore = (int) $searchBeforeDate->sortBy([
                                ['id', 'desc']
                            ])->first()['qty'];
                        }else{
                            # Bila hanya ada 1 data
                            $qtyBefore = (int) $searchBeforeDate->first()['qty'];
                        }
                    }
                    array_push($itemForecast, "<span style='color: red;'>{$qtyBefore}</span>");
                }
            }
            $iterasi++;
        }
        array_push($itemForecast, '<span>View</span>');
        array_push($this->resultForecastForDays, $itemForecast);
    }

    /**
     * Method untuk memproses perhitungan qty per item jika type forecast adalah week
     * @param string $str --> nama item
     * @throws void
     * @return void
     */
    private function prosesDataPerItemForWeek($str){
        $u = collect($this->contohData); // mengoleksi semua data sampel

        # memilih semua data item berdasarkan nama item dari $this->contohData
        $keys = $u->filter(function ($value) use($str) {
            return $value['name_item'] == $str;
        });
        
        $itemForecast = [$str];
        foreach ($this->dataDatePerWeek as $dateRange) {
            /*
             Looping data per minggu
             $dateRange memiliki output berapa array tgl
            */
            $jumlahQty = 0;
            $iterasi = 0;
            $qtyBefore = 0;
            foreach($dateRange as $date){
                # $date --> exp : 2021-11-04
                /* 
                  Looping per hari dalam seminggu 
                */
                # mencari key berdasarkan tanggal
                $key = $keys->filter(function ($value) use($date){
                    return false !== stristr($value['tgl_request_update'], $date);           
                });
                $jumlah = $key->values()->count();
                if($jumlah > 0){
                    # jika ada datanya, ambil qty item
                    if($jumlah > 1){
                        # jika jumlah data ada lebih 1 dari tanggal yang sama 
                        $qtyBefore = (int) $key->sortBy([
                            ['id', 'desc']
                        ])->first()['qty'];
                    }else{
                        # jika hanya 1 data
                        $qtyBefore = (int) $key->first()['qty'];
                    }
                }else{
                    # Jika key tidak ketemu
                    if($iterasi > 0){
                        # jika looping harian sudah min 1x
                        $qtyBefore = $qtyBefore;
                    }else{
                        # jika berada di urutan looping pertama kog datanya tidak ada maka bisa dicari data di tgl sebelumnya ada atau data terakhir di mysql
                        $qtyBefore = 0; // set ke 0 terlebih dahulu
                        $searchBeforeDate = $keys->filter(function ($value) use($date){
                            return $value['tgl_request_update'] < $date;
                        });
                        if($searchBeforeDate->values()->count() > 0){
                            if($searchBeforeDate->values()->count() > 1){
                                # Jika ada lebih dari 1 data
                                $qtyBefore = (int) $searchBeforeDate->sortBy([
                                    ['id', 'desc']
                                ])->first()['qty'];
                            }else{
                                # Bila hanya ada 1 data
                                $qtyBefore = (int) $searchBeforeDate->first()['qty'];
                            }
                        }
                    }
                }
                # jumlahkan qty per hari dalam seminggu
                $jumlahQty += $qtyBefore;
                $iterasi++;
            }
            # Bila sudah melakukan looping dalam seminggu data total qty add ke $itemForecast
            array_push($itemForecast, $jumlahQty);
            # looping dilanjutkan ke minggu berikutnya
        }
        array_push($itemForecast, '<span>View</span>');
        array_push($this->resultForecastForWeeks, $itemForecast);
    }


    /**
     * Method untuk menyaring tanggal foreacast per minggu dari semua tanggal
     * pada method forecastDateToConvert, method ini hanya berlaku jika type forecast
     * week
     * @param void
     * @throws void
     * @return void
     */
    private function forecastDateToConvertToWeek(){
        $dateArrayForWeekAll = []; // ini berguna untuk menampung semua data per minggu
        $dateRangeForWeek = []; // ini berguna untuk menampung tgl per minggu
        $bulanPertama = 0; // ini berguna untuk menandai bulan untuk pengecekan data

        foreach ($this->dataTglPerDay as $value) {
            // mulai hitung perhari berdasarkan tanggal
            $exp = explode("-", $value); // memecah string menjadi [Y,m,d,D]
            if($bulanPertama == 0 || $bulanPertama == $exp[1]){
                /*
                    jika masih berada di index pertama [0] atau hari/tanggal
                    masih berada pada bulan yang sama
                */
                if($exp[3] != "Sat"){
                    /*
                        jika hari jatuh pada hari selain sabtu, maka data akan input ke range harian per minggu
                    */
                    // array_push($dateRangeForWeek, $value);
                    array_push($dateRangeForWeek, "{$exp[0]}-{$exp[1]}-{$exp[2]}");
                    $bulanPertama = $exp[1]; // bulan berubah berdasar tanggal
                }else{
                    /*
                        jika hari jatuh pada hari sabtu, maka data akan input ke range harian per minggu setelah itu data mingguan tersebut ditambahkan ke data $dateArrayForWeekAll dan data range harian perminggu harus di kosongkan.
                    */
                    // array_push($dateRangeForWeek, $value);
                    array_push($dateRangeForWeek, "{$exp[0]}-{$exp[1]}-{$exp[2]}");
                    $bulanPertama = $exp[1];
                    array_push($dateArrayForWeekAll, $dateRangeForWeek);
                    $dateRangeForWeek = [];
                }
            }else{
                /*
                    jika hari/tanggal sudah melewati bulan
                */
                if(count($dateRangeForWeek) > 0){
                    /*
                        Sebuah kondisi yang diperlukan untuk memeriksa apakah data di variabel $dateRangeForWeek kosong atau tidak, 
                        jika tidak kosong maka data mingguan tersebut ditambahkan ke data $dateArrayForWeekAll dan data range harian
                        perminggu harus di kosongkan.
                    */
                    array_push($dateArrayForWeekAll, $dateRangeForWeek);
                    $dateRangeForWeek = [];
                }
                if($exp[3] != "Sat"){
                    // jika hari bukan sabtu
                    // array_push($dateRangeForWeek, $value);
                    array_push($dateRangeForWeek, "{$exp[0]}-{$exp[1]}-{$exp[2]}");
                    $bulanPertama = $exp[1];
                }else{
                    // jika sabtu
                    // array_push($dateRangeForWeek, $value);
                    array_push($dateRangeForWeek, "{$exp[0]}-{$exp[1]}-{$exp[2]}");
                    $bulanPertama = $exp[1];
                    array_push($dateArrayForWeekAll, $dateRangeForWeek);
                    $dateRangeForWeek = [];
                }
            }
        }
        /*
          Jika looping telah selesai maka sisa data perminggu akan ditambahakan
          ke $dateArrayForWeekAll
        */
        array_push($dateArrayForWeekAll, $dateRangeForWeek);
        $dateRangeForWeek = [];

        // ubah data $this->dataDatePerWeek berisi range data perminggu
        $this->dataDatePerWeek = $dateArrayForWeekAll;
    }


    /**
     * Method yang berguna untuk mengkonversi & mendapatkan data semua tanggal
     * dari tgl awal sampai tgl target
     * @param void
     * @throws void
     * @return void
     */
    public function forecastDateToConvert(){
        # first check date valid
        $targetDate = $this->getDateOnTarget(); // mendapatkan tgl awal & target
        // echo "Date now ".$targetDate['now']."<br/>";
        // echo "Date target " .$targetDate['target']."<br/><br/>";

        $this->fromDate = $targetDate['now']; // string tanggal awal
        $this->targetDate = $targetDate['target']; // string tanggal target

        $monthNowTrigger = (int) $targetDate['explode'][1]; // bulan pertama
        $yearNowTrigger = (int) $targetDate['explode'][0]; // tahun tahun pertama

        $jumlahhariSetahun = 0;

        for($v = 1; $v<=(12 + 1); $v++){
            /*
              Lopping sebanyak 13x untuk menghitung bulan dalam 1 tahun ditambah 1 bulan
            */

            if($monthNowTrigger > 12){
                /*
                 Jika looping lebih dari 12x
                */
                $monthNowTrigger = 1; // bulan akan di set bulan awal
                $yearNowTrigger += 1; // tahun ditambah 1 sebagai ganti tahun
            }

            $str = "<br/>bulan : $monthNowTrigger, tahun : $yearNowTrigger<br/>";

            #mendapatkan jumlah tanggal dalam 1 bulan berdasarkan kalender georgian
            $totalDayofMonth = cal_days_in_month(CAL_GREGORIAN, $monthNowTrigger, $yearNowTrigger);

            $picu = 0; // trigger untuk menghentikan proses looping

            for($i = 1; $i<=$totalDayofMonth; $i++){
                /*
                    Melakukan looping sebanyak jumlah hari dalam 1 bulan
                */

                # mendapatkan tanggal berdasarkan kalender
                $getDate = new DateTime("{$yearNowTrigger}-{$monthNowTrigger}-{$i}");
                
                # NOTED : variabel $jumlahhariSetahun sebenarnya tidak diperlukan
                $jumlahhariSetahun++; // menghitung jumlah hari setahun

                if($this->type == 'week'){
                    /* 
                        jika tipe forecast dihitung berdasarkan mingguan, semua tgl
                        akan mengandung nama hari
                    */
                    array_push($this->dataTglPerDay, $getDate->format("Y-m-d-D"));
                }else{
                    /* 
                        jika tipe forecast dihitung berdasarkan hari atau yang selain
                        week
                    */
                    array_push($this->dataTglPerDay, $getDate->format("Y-m-d"));
                }

                // $tanggal = str_pad($i,2,"0",STR_PAD_LEFT);
                $tanggal = $getDate->format('Y-m-d');

                if($targetDate['target'] == $tanggal){
                    /*
                     Pemeriksaan tanggal bila tanggal target sama dengan tanggal looping
                    */
                    if($this->type == 'days'){
                        /* 
                            jika tipe forecast adalah hari maka looping akan dihentikan
                        */
                        $picu = 1; // mengaktifkan picu untuk break looping per bulan
                        break;
                    }

                }
            }
            if ($picu > 0){
                /*
                    jika picu adalah 1 maka looping dihentikan
                */
                break;
            }
            $monthNowTrigger += 1; // ganti bulan ke berikutnya
        }

    }

    /**
     * Method yang berguna untuk mendapatkan tanggal yang dituju atau batas maksimal dari tanggal sebelumya misal 12-02-2020 sampai 12-02-2021 guna untuk menentukan batasan terhadap proses perhitungan data forecastnya, method ini adalah yang kedua di proses.
     * @param void
     * @throws void
     * @return array [
             now => Y-m-d --> # 2020-02-12
             target => d-m-Y, --> # 2021-02-12
             explode => [year, month, day] --> # [2020, 02, 12]
         ]
     */
    public function getDateOnTarget(){
        $getDate = $this->dateNow(); # mendapatkan tgl sekarang/today
        $minDate = 	$getDate["dateString"]; // date now

        $convertYear = (int) $getDate["date"][0]; // tahun
        $convertYear+=1; # tambahkan satu tahun

        # menentukan target tanggalnya
        $maxDate = $convertYear.'-'.$getDate['date'][1].'-'.$getDate['date'][2];

        # melakukan pemeriksaan tanggal target apakah sudah valid atau tidak berdasarkan kalender
        $dC = new DateTime($maxDate);
        if($dC->format("Y-m-d") != $maxDate){
            # jika tgl tidak valid dengan target maka akan ambil berdasarkan tgl terakhir dari bulan target berdasar kalender
            # kasus ini biasa terjadi pada bulan februari
            $date_ = new DateTime("last day of {$convertYear}-{$getDate['date'][1]}");
            $maxDate = $date_->format("Y-m-d");
        }
        // $maxDate = "15-12-2021";
        return [
            'now' => $minDate,
            'target' => $maxDate,
            'explode' => $getDate['date'],
        ];
    }

    /**
     * Method untuk mendapatkan tanggal pada hari ini / sekarang
     * method ini adalah yang pertama kali di proses
     * @param void
     * @throws void
     * @return array [
             date => [year, month, day], --> # [2020, 02, 14]
             dateString => Y-m-d --> # 2020-02-14
         ]
     */
    function dateNow(){
        $d = new DateTime();
        $str = $d->format("Y-m-d");
        $ex = explode("-", $str);

        return [
            'date' => $ex,
            'dateString' => $str
        ];
    }
}
?>