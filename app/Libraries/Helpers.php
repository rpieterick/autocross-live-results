<?php

namespace App\Libraries;

trait Helpers
{
    public static function arrayKeyExists($key, $arr)
    {
        $newarr = array_change_key_case($arr);
        return array_key_exists(strtolower($key), $newarr);
    }

    public static function arraySearch($val, $arr)
    {
        $newarr = array_map("strtolower", $arr);
        return array_search(strtolower($val), $newarr);
    }

    public static function inArray($val, $arr)
    {
        $newarr = array_map("strtolower", $arr);
        return in_array(strtolower($val), $newarr);
    }
}
