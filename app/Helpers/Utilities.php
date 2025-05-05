<?php

namespace App\Helpers;

use NumberFormatter;

class Utilities
{
    const ORDINAL_NUMBERS = [
        1 => 'PRIMERO',
        2 => 'SEGUNDO',
        3 => 'TERCERO',
        4 => 'CUARTO',
        5 => 'QUINTO',
        6 => 'SEXTO',
        7 => 'SÉPTIMO',
        8 => 'OCTAVO',
        9 => 'NOVENO',
        10 => 'DÉCIMO',
        11 => 'UNDÉCIMO',
        12 => 'DUODÉCIMO',
        13 => 'DECIMOTERCERO',
        14 => 'DECIMOCUARTO',
        15 => 'DECIMOQUINTO',
        16 => 'DECIMOSEXTO',
        17 => 'DECIMOSÉPTIMO',
        18 => 'DECIMOCTAVO',
        19 => 'DECIMONOVENO',
        20 => 'VIGÉSIMO',
        21 => 'VIGÉSIMO PRIMERO',
        22 => 'VIGÉSIMO SEGUNDO',
        23 => 'VIGÉSIMO TERCERO',
        24 => 'VIGÉSIMO CUARTO',
        25 => 'VIGÉSIMO QUINTO',
        26 => 'VIGÉSIMO SEXTO',
        27 => 'VIGÉSIMO SÉPTIMO',
        28 => 'VIGÉSIMO OCTAVO',
        29 => 'VIGÉSIMO NOVENO',
        30 => 'TRIGÉSIMO',
        31 => 'TRIGÉSIMO PRIMERO',
        32 => 'TRIGÉSIMO SEGUNDO',
        33 => 'TRIGÉSIMO TERCERO',
    ];


    public static function NumberToOrdinal($number)
    {
        if ($number < 1 || $number > 33) {
            return null; // Fuera del rango definido
        }

        return self::ORDINAL_NUMBERS[$number];
    }

    //JUAN PEREZ ORTIZ = JPO
    public  static function InitialsString($string)
    {
        $parts = explode(' ', $string);
        $initials = '';
        foreach ($parts as $part) {
            if (isset($part[0])) {
                $initials .= strtoupper($part[0]);
            }
        }
        return $initials;
    }
}
