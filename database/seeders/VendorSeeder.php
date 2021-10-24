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
             "number" => "V001272",
             "name" => "A T  INDONESIA, PT.",
             "address" => "Jl. Maligi III H 1 - 5 Kawasan Industri KIIC Jl. Tol Jakarta Cikampek KM 47  KARAWANG 41361",
             "company" => "",
             "phone" => "",
            ],
            ["number" => "V001272"],
          ],
          [
            [
             "number" => "V001303",
             "name" => "ALTAN,TK.",
             "address" => "Jl. Raya Mijen No.216 Mijen   SEMARANG  INDONESIA",
             "company" => "",
             "phone" => "",
            ],
            ["number" => "V001303"],
          ],
          [
            [
             "number" => "V002011",
             "name" => "BANDO INDONESIA, PT",
             "address" => "Jl. Gajah Tunggal Kel. Pa Kecamatan Jati Uwung   TANGERANG 15135",
             "company" => "",
             "phone" => "",
            ],
            ["number" => "V002011"],
          ],
          [
            [
             "number" => "V002062",
             "name" => "BATARASURA MULIA, PT",
             "address" => "Jl. Raya Bekasi Tambun KM Desa Jati Mulya, Bekasi,   JAWA BARAT",
             "company" => "",
             "phone" => "",
            ],
            ["number" => "V002062"],
          ],
          [
            [
             "number" => "V002073",
             "name" => "BHINNEKA BAJANAS, PT",
             "address" => "Jl. Hasanudin A/47    SEMARANG",
             "company" => "",
             "phone" => "",
            ],
            ["number" => "V002073"],
          ],
          [
            [
             "number" => "V002123",
             "name" => "BANGUN JAYA, Toko",
             "address" => "Jl. Indragiri Utara III No.    SEMARANG",
             "company" => "",
             "phone" => "",
            ],
            ["number" => "V002123"],
          ],
          [
            [
             "number" => "V002162",
             "name" => "BERDIKARI METAL ENGINEERING, PT",
             "address" => "Jl.Industri III No.6 Leuwigajah Cimahi   BANDUNG 40532 INDONESIA",
             "company" => "",
             "phone" => "",
            ],
            ["number" => "V002162"],
          ],
          [
            [
             "number" => "V002231",
             "name" => "BACHTERA LADJU, PT",
             "address" => "Jl. Tanah Abang III No.29    JAKARTA PUSAT 10160",
             "company" => "",
             "phone" => "",
            ],
            ["number" => "V002231"],
          ],
          [
            [
             "number" => "V003031",
             "name" => "CITRA NUGERAH KARYA, PT",
             "address" => "Jl. Jati Raya Blok J3 No. Newton Techno Park,   BEKASI 17550",
             "company" => "",
             "phone" => "",
            ],
            ["number" => "V003031"],
          ],
          [
            [
             "number" => "V003053",
             "name" => "CIPTA GRAFIKA/SURIPTO",
             "address" => "Jl.Citarum Slt VII/156 Semarang   SEMARANG",
             "company" => "",
             "phone" => "",
            ],
            ["number" => "V003053"],
          ],
        ];

       foreach($arr_seeders as $key => $seed) {
          Vendor::updateOrCreate($seed[0],$seed[1]);
       }
    }
}