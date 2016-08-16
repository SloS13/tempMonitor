<?php

$output = shell_exec('/var/www/html/python/./getTemp');

if ( substr_count($output,'YES') ) {
    $tempRaw = substr($output, -5);
    echo 'Temp:'.$tempRaw;
} else {
    echo 'ERROR';
}
