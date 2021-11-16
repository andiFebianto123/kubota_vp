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
        'roles' => ['admin', 'vendor'],
        'childrens' => []
      ],
      [
        'name' => 'Purchase Order',
        'url' => backpack_url('purchase-order'),
        'icon' => 'la-newspaper',
        'key' => 'purchase-order',
        'roles' => ['admin', 'vendor'],
        'childrens' => []
      ],
      [
        'name' => 'Delivery Sheet',
        'url' => backpack_url('delivery'),
        'icon' => 'la-file',
        'key' => 'delivery',
        'roles' => ['admin', 'vendor'],
        'childrens' => []
      ],
      [
        'name' => 'Delivery Status',
        'url' => backpack_url('delivery-status'),
        'icon' => 'la-stream',
        'key' => 'delivery-status',
        'roles' => ['admin', 'vendor'],
        'childrens' => []
      ],
      [
        'name' => 'Delivery Serial',
        'url' => backpack_url('delivery-serial'),
        'icon' => 'la-qrcode',
        'key' => 'delivery-serial',
        'roles' => ['admin', 'vendor'],
        'childrens' => []
      ],
      [
        'name' => 'Forecast',
        'url' => backpack_url('forecast'),
        'icon' => 'la-chart-bar',
        'key' => 'forecast',
        'roles' => ['admin', 'vendor'],
        'childrens' => []
      ],
      [
        'name' => 'Vendor',
        'url' => backpack_url('vendor'),
        'icon' => 'la-people-carry',
        'key' => 'vendor',
        'roles' => ['admin'],
        'childrens' => []
      ],
      [
        'name' => 'User',
        'url' => backpack_url('user'),
        'icon' => 'la-user-tie',
        'key' => 'user',
        'roles' => ['admin'],
        'childrens' => []
      ],
      [
        'name' => 'General Message',
        'url' => backpack_url('general-message'),
        'icon' => 'la-envelope',
        'key' => 'general-message',
        'roles' => ['admin'],
        'childrens' => []
      ],
      [
        'name' => 'Configurations',
        'url' => backpack_url('configuration'),
        'icon' => 'la-tools',
        'key' => 'configuration',
        'roles' => ['admin'],
        'childrens' => []
      ],
    ];
  }
}