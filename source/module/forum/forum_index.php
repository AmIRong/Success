<?php

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once libfile('function/forumlist');

$gid = intval(getgpc('gid'));
$showoldetails = get_index_online_details();



function get_index_online_details() {
    $showoldetails = getgpc('showoldetails');
    switch($showoldetails) {
        case 'no': dsetcookie('onlineindex', ''); break;
        case 'yes': dsetcookie('onlineindex', 1, 86400 * 365); break;
    }
    return $showoldetails;
}