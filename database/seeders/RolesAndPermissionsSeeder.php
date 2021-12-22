<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->createRole();

        $this->createPermission();

        // $this->assignRolePermission();

        // $this->assignUserRole();
    }

    function createRole(){
        $arr_seeders = [
            [
              [
                "id" => 1,
                "name" => "Admin PTKI",
                "guard_name" => "web",
              ],
              ["id" => 1],
            ],
            [
              [
                "id" => 2,
                "name" => "Marketing Vendor",
                "guard_name" => "web",
              ],
              ['id' => 2],
            ],
            [
              [
                "id" => 3,
                "name" => "Finance Vendor",
                "guard_name" => "web",
              ],
              ['id' => 3],
            ],
            [
                [
                    'id' => 4,
                    'name' => 'Warehouse Vendor',
                    'guard_name' => 'web'
                ],
                ['id' => 4]
            ]
          ];
  
        foreach($arr_seeders as $key => $seed) {
            Role::updateOrCreate($seed[0],$seed[1]);
        }
    }

    function createPermission(){
        $arrPermission = [
            [
                [
                  "name" => "Show Payment Status DS",
                  "guard_name" => "web",
                  "description" => "Mempunyai akses untuk melihat panel Payment Status pada Delivery Sheet"
                ],
                ['name' => 'Show Payment Status DS'],
            ],
            [
              [
                "name" => "Read dashboard",
                "guard_name" => "web",
                "description" => "Mempunyai akses untuk melihat halaman dashboard"
              ],
              ['name' => 'Read dashboard'],
            ],
            [
              [
                "name" => "Read Purchase Order",
                "guard_name" => "web",
                "description" => "Mempunyai akses untuk melihat data di halaman purchase order"
              ],
              ['name' => 'Read Purchase Order'],
            ],
            [
              [
                "name" => "Export Purchase Order",
                "guard_name" => "web",
                "description" => "Mempunyai akses untuk export data purchase order"
              ],
              ['name' => 'Export Purchase Order'],
            ],
            [
              [
                "name" => "Import Purchase Order",
                "guard_name" => "web",
                "description" => "Mempunyai akses untuk import data purchase order"
              ],
              ['name' => 'Import Purchase Order'],
            ],
            [
              [
                "name" => "Send Mail New PO",
                "guard_name" => "web",
                "description" => "Mempunyai akses kirim email di semua vendor jika ada new PO"
              ],
              ['name' => 'Send Mail New PO'],
            ],
            [
              [
                "name" => "Read PO Detail",
                "guard_name" => "web",
                "description" => "Mempunyai akses print order sheet, accept, reject, view detail PO Line dan view detail PO Change History"
              ],
              ['name' => 'Read PO Detail'],
            ],
            [
              [
                "name" => "Unread PO Detail",
                "guard_name" => "web",
                "description" => "Mempunyai akses unread pada PO detail"
              ],
              ['name' => 'Unread PO Detail'],
            ],
            [
              [
                "name" => "Read PO Line Detail",
                "guard_name" => "web",
                "description" => "Mempunyai akses melihat halaman detail Po Line & Delivery Sheet Detail"
              ],
              ['name' => 'Read PO Line Detail'],
            ],
            [
              [
                "name" => "Create Delivery Sheet",
                "guard_name" => "web",
                "description" => "Mempunyai akses membuat data Delivery Sheet"
              ],
              ['name' => 'Create Delivery Sheet'],
            ],
            [
              [
                "name" => "Print Label Delivery Sheet",
                "guard_name" => "web",
                "description" => "Mempunyai akses mencetak label data Delivery Sheet"
              ],
              ['name' => 'Print Label Delivery Sheet'],
            ],
            [
              [
                "name" => "Delete Delivery Sheet",
                "guard_name" => "web",
                "description" => "Mempunyai akses menghapus data Delivery Sheet"
              ],
              ['name' => 'Delete Delivery Sheet'],
            ],
            [
              [
                "name" => "Read Delivery Sheet",
                "guard_name" => "web",
                "description" => "Mempunyai akses melihat Delivery Sheet"
              ],
              ['name' => 'Read Delivery Sheet'],
            ],
            [
              [
                "name" => "Print DS with Price",
                "guard_name" => "web",
                "description" => "Mempunyai akses mencetak DS dengan harga"
              ],
              ['name' => 'Print DS with Price'],
            ],
            [
              [
                "name" => "Print DS without Price",
                "guard_name" => "web",
                "description" => "Mempunyai akses mencetak DS tanpa menyertakan harga"
              ],
              ['name' => 'Print DS without Price'],
            ],
        ];
        foreach($arrPermission as $key => $seed) {
          Permission::updateOrCreate($seed[0],$seed[1]);
        }
    }

    function assignRolePermission(){
        // memberikan permission pada role
        $adminRole = Role::findByName('Admin PTKI');
        $adminRole->givePermissionTo('Show Payment Status DS');
        // dd($adminRole->permissions);

    }

    function assignUserRole(){
        $userAdmin = User::find(1)->first();

        if($userAdmin){
            $userAdmin->assignRole(['Admin PTKI']);
            // bila ingin menambahkan permission langsung dari user
            // $userAdmin->givePermissionTo('update');
        }
    }
}