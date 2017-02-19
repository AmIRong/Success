<?php
if(!defined('IN_COMSENZ')) {
    exit('Access Denied');
}

function getgpc($k, $t='GP') {
    $t = strtoupper($t);
    switch($t) {
        case 'GP' : isset($_POST[$k]) ? $var = &$_POST : $var = &$_GET; break;
        case 'G': $var = &$_GET; break;
        case 'P': $var = &$_POST; break;
        case 'C': $var = &$_COOKIE; break;
        case 'R': $var = &$_REQUEST; break;
    }
    return isset($var[$k]) ? $var[$k] : null;
}