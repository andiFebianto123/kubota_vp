<?php


if(!function_exists('getSQL')){
    function getSQL($builder) {
        $sql = $builder->toSql();
        foreach ( $builder->getBindings() as $binding ) {
          $value = is_numeric($binding) ? $binding : "'".$binding."'";
          $sql = preg_replace('/\?/', $value, $sql, 1);
        }
        return $sql;
    }
}

if(!function_exists('getNameFromNumber')){
  function getNameFromNumber($num) {
    $numeric = ($num - 1) % 26;
    $letter = chr(65 + $numeric);
    $num2 = intval(($num - 1) / 26);
    if ($num2 > 0) {
        return getNameFromNumber($num2) . $letter;
    } else {
        return $letter;
    }
  }
}