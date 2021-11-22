<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vendor;

class VendorSeeder extends Seeder
{

    public function run()
    {
        $arr_seeders = [
          [
            [
             "vend_num" => "V001272",
             "vend_name" => "A T  INDONESIA, PT.",
             "vend_addr" => "Jl. Maligi III H 1 - 5 Kawasan Industri KIIC Jl. Tol Jakarta Cikampek KM 47  KARAWANG 41361",
             "currency" => "IDR",
            ],
            ["vend_num" => "V001272"],
          ],
          [
            [
             "vend_num" => "V001303",
             "vend_name" => "ALTAN,TK.",
             "vend_addr" => "Jl. Raya Mijen No.216 Mijen   SEMARANG  INDONESIA",
             "currency" => "IDR",
            ],
            ["vend_num" => "V001303"],
          ],
          [
            [
             "vend_num" => "V002011",
             "vend_name" => "BANDO INDONESIA, PT",
             "vend_addr" => "Jl. Gajah Tunggal Kel. Pa Kecamatan Jati Uwung   TANGERANG 15135",
             "currency" => "IDR",
            ],
            ["vend_num" => "V002011"],
          ],
          [
            [
             "vend_num" => "V002062",
             "vend_name" => "BATARASURA MULIA, PT",
             "vend_addr" => "Jl. Raya Bekasi Tambun KM Desa Jati Mulya, Bekasi,   JAWA BARAT",
             "currency" => "IDR",
            ],
            ["vend_num" => "V002062"],
          ],
          [
            [
             "vend_num" => "V002073",
             "vend_name" => "BHINNEKA BAJANAS, PT",
             "vend_addr" => "Jl. Hasanudin A/47    SEMARANG",
             "currency" => "IDR",
            ],
            ["vend_num" => "V002073"],
          ],
          [
            [
             "vend_num" => "V002123",
             "vend_name" => "BANGUN JAYA, Toko",
             "vend_addr" => "Jl. Indragiri Utara III No.    SEMARANG",
             "currency" => "IDR",
            ],
            ["vend_num" => "V002123"],
          ],
          [
            [
             "vend_num" => "V002162",
             "vend_name" => "BERDIKARI METAL ENGINEERING, PT",
             "vend_addr" => "Jl.Industri III No.6 Leuwigajah Cimahi   BANDUNG 40532 INDONESIA",
             "currency" => "IDR",
            ],
            ["vend_num" => "V002162"],
          ],
          [
            [
             "vend_num" => "V002231",
             "vend_name" => "BACHTERA LADJU, PT",
             "vend_addr" => "Jl. Tanah Abang III No.29    JAKARTA PUSAT 10160",
             "currency" => "IDR",
            ],
            ["vend_num" => "V002231"],
          ],
          [
            [
             "vend_num" => "V003031",
             "vend_name" => "CITRA NUGERAH KARYA, PT",
             "vend_addr" => "Jl. Jati Raya Blok J3 No. Newton Techno Park,   BEKASI 17550",
             "currency" => "IDR",
            ],
            ["vend_num" => "V003031"],
          ],
          [
            [
             "vend_num" => "V003053",
             "vend_name" => "CIPTA GRAFIKA/SURIPTO",
             "vend_addr" => "Jl.Citarum Slt VII/156 Semarang   SEMARANG",
             "currency" => "IDR",
            ],
            ["vend_num" => "V003053"],
          ],
        ];

       foreach($arr_seeders as $key => $seed) {
          Vendor::updateOrCreate($seed[0],$seed[1]);
       }
    }
}