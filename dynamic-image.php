<?php

function trailingslashit($value){
    return rtrim( $value, '/\\' ). '/';
}

function sanitize_title($value){
    return str_replace([' ','_'],'-', strtolower($value));
}


require_once __DIR__ . 'inc/image.php';

echo "<h1>HELLO</h1>";
die();
