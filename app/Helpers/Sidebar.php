<?php
namespace App\Helpers;
use Auth;

class Sidebar
{
 
  public function generate()
  {
    return
    [
      [
        'name' => 'Dashboard',
        'url' => backpack_url('dashboard'),
        'icon' => 'la-home',
        'key' => 'dashboard',
        'roles' => ['Admin PTKI', 'Warehouse Vendor', 'Marketing Vendor', 'Finance Vendor'],
        'childrens' => []
      ],
      [
        'name' => 'Purchase Order',
        'url' => backpack_url('purchase-order'),
        'icon' => 'la-newspaper',
        'key' => 'purchase-order',
        'roles' => ['Admin PTKI', 'Warehouse Vendor', 'Marketing Vendor', 'Finance Vendor'],
        'childrens' => []
      ],
      [
        'name' => 'Delivery Sheet',
        'url' => backpack_url('delivery'),
        'icon' => 'la-file',
        'key' => 'delivery',
        'roles' => ['Admin PTKI', 'Warehouse Vendor', 'Marketing Vendor', 'Finance Vendor'],
        'childrens' => []
      ],
      [
        'name' => 'Delivery Status',
        'url' => backpack_url('delivery-status'),
        'icon' => 'la-stream',
        'key' => 'delivery-status',
        'roles' => ['Admin PTKI', 'Warehouse Vendor', 'Marketing Vendor', 'Finance Vendor'],
        'childrens' => []
      ],
      [
        'name' => 'Delivery Serial',
        'url' => backpack_url('delivery-serial'),
        'icon' => 'la-qrcode',
        'key' => 'delivery-serial',
        'roles' => ['Admin PTKI', 'Warehouse Vendor', 'Marketing Vendor', 'Finance Vendor'],
        'childrens' => []
      ],
      [
        'name' => 'Material Outhouse',
        'url' => backpack_url('material-outhouse'),
        'icon' => 'la-cube',
        'key' => 'material-outhouse',
        'roles' => ['Admin PTKI', 'Warehouse Vendor', 'Marketing Vendor', 'Finance Vendor'],
        'childrens' => []
      ],
      [
        'name' => 'Summary MO',
        'url' => backpack_url('material-outhouse-summary'),
        'icon' => 'la-cube',
        'key' => 'material-outhouse-summary',
        'roles' => ['admin', 'vendor'],
        'childrens' => []
      ],
      [
        'name' => 'List Payment',
        'url' => backpack_url('tax-invoice'),
        'icon' => 'la-file-invoice-dollar',
        'key' => 'tax-invoice',
        'roles' => ['Admin PTKI', 'Warehouse Vendor', 'Marketing Vendor', 'Finance Vendor'],
        'childrens' => []
      ],
      
      [
        'name' => 'Forecast',
        'url' => backpack_url('forecast'),
        'icon' => 'la-chart-bar',
        'key' => 'forecast',
        'roles' => ['Admin PTKI', 'Warehouse Vendor', 'Marketing Vendor', 'Finance Vendor'],
        'childrens' => []
      ],
      [
        'name' => 'Vendor',
        'url' => backpack_url('vendor'),
        'icon' => 'la-people-carry',
        'key' => 'vendor',
        'roles' => ['Admin PTKI'],
        'childrens' => []
      ],
      [
        'name' => 'User',
        'url' => backpack_url('user'),
        'icon' => 'la-user-tie',
        'key' => 'user',
        'roles' => ['Admin PTKI'],
        'childrens' => []
      ],
      [
        'name' => 'General Message',
        'url' => backpack_url('general-message'),
        'icon' => 'la-envelope',
        'key' => 'general-message',
        'roles' => ['Admin PTKI'],
        'childrens' => []
      ],
      [
        'name' => 'Configurations',
        'url' => backpack_url('configuration'),
        'icon' => 'la-tools',
        'key' => 'configuration',
        'roles' => ['Admin PTKI'],
        'childrens' => []
      ],
      [
        'name' => 'Role',
        'url' => backpack_url('role'),
        'icon' => 'la-users',
        'key' => 'role',
        'roles' => ['Admin PTKI'],
        'childrens' => []
      ],
      [
        'name' => 'Permission',
        'url' => backpack_url('permission'),
        'icon' => 'la-lock',
        'key' => 'permission',
        'roles' => ['Admin PTKI'],
        'childrens' => []
      ],
    ];
  }
}