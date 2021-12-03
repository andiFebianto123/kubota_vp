<?php
namespace App\Helpers;

class Constant
{
 
  public function statusOFC(){
    return [ 
        'O' => ['text' => 'Ordered', 'color' => ''], 
        'F' => ['text' => 'Filled', 'color' => 'text-primary'], 
        'C' => ['text' => 'Complete', 'color' => 'text-success']
    ];
  }

  
}