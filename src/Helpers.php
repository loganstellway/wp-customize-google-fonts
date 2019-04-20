<?php

namespace LoganStellway\GoogleFonts\Helpers;

/**
 * Get base URL
 */
function getBaseUrl()
{
    return 'https://fonts.googleapis.com/css?family=';
}

/**
 * Get font parts
 */
function getFontParts(string $font)
{
    $font = explode(':', $font);
    $font[1] = isset($font[1]) ? $font[1] : '';
    $weight = preg_replace('/[A-z]+/', '', $font[1]);

    return array(
        'family' => str_replace('+', ' ', $font[0]),
        'style' => stripos($font[1], 'i') == false ? 'normal' : 'italic',
        'weight' => strlen($weight) ? $weight : '400',
    );
}
