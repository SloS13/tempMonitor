<?php

$output = shell_exec('/var/www/html/python/./getTemp');

if ( substr_count($output,'YES') ) {
    $tempRaw = substr($output, -6);
    $tempC = $tempRaw / 1000;
    $tempF = ($tempC * 9 / 5) + 32;
    echo 'Temp:'.$tempF;
} else {
    echo 'ERROR';
}
