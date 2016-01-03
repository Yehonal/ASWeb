<?php

namespace Hwc;

abstract class ArrayLib
{

    static function deepKsort(&$arr, $sort_flags = SORT_REGULAR)
    {
        ksort($arr, $sort_flags);
        foreach ($arr as &$a) {
            if (is_array($a) && ! empty($a)) {
                self::deepKsort($a, $sort_flags);
            }
        }
    }
}