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

        $this->assignRolePermission();

        $this->assignUserRole();
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
                  'id' => 1,
                  "name" => "create",
                  "guard_name" => "web",
                  "description" => "Mempunyai akses untuk membuat data semua jenis transaksi"
                ],
                ['id' => 1],
            ],
            [
                [
                  'id' => 2,
                  "name" => "update",
                  "guard_name" => "web",
                  "description" => "Mempunyai akses untuk mengubah data transaksi"
                ],
                ['id' => 2],
            ],
            [
                [
                  'id' => 3,
                  "name" => "delete",
                  "guard_name" => "web",
                  "description" => "Mempunyai akses untuk menghapus data transaksi"
                ],
                ['id' => 3],
            ],
        ];
        foreach($arrPermission as $key => $seed) {
          Permission::updateOrCreate($seed[0],$seed[1]);
        }
    }

    function assignRolePermission(){
        // memberikan permission pada role
        $adminRole = Role::findByName('Admin PTKI');
        $adminRole->givePermissionTo('create');
        $adminRole->givePermissionTo('update');
        $adminRole->givePermissionTo('delete');
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