<?php

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once libfile('function/forumlist');

$gid = intval(getgpc('gid'));
$showoldetails = get_index_online_details();

if(!$_G['uid'] && !$gid && $_G['setting']['cacheindexlife'] && !defined('IN_ARCHIVER') && !defined('IN_MOBILE')) {
    get_index_page_guest_cache();
}

$newthreads = round((TIMESTAMP - $_G['member']['lastvisit'] + 600) / 1000) * 1000;

$catlist = $forumlist = $sublist = $forumname = $collapse = $favforumlist = array();
$threads = $posts = $todayposts = $announcepm = 0;
$postdata = $_G['cache']['historyposts'] ? explode("\t", $_G['cache']['historyposts']) : array(0,0);
$postdata[0] = intval($postdata[0]);
$postdata[1] = intval($postdata[1]);


list($navtitle, $metadescription, $metakeywords) = get_seosetting('forum');
function get_index_online_details() {
    $showoldetails = getgpc('showoldetails');
    switch($showoldetails) {
        case 'no': dsetcookie('onlineindex', ''); break;
        case 'yes': dsetcookie('onlineindex', 1, 86400 * 365); break;
    }
    return $showoldetails;
}

function get_index_page_guest_cache() {
    global $_G;
    $indexcache = getcacheinfo(0);
    if(TIMESTAMP - $indexcache['filemtime'] > $_G['setting']['cacheindexlife']) {
        @unlink($indexcache['filename']);
        define('CACHE_FILE', $indexcache['filename']);
    } elseif($indexcache['filename']) {
        @readfile($indexcache['filename']);
        $updatetime = dgmdate($indexcache['filemtime'], 'H:i:s');
        $gzip = $_G['gzipcompress'] ? ', Gzip enabled' : '';
        echo "<script type=\"text/javascript\">
        if($('debuginfo')) {
        $('debuginfo').innerHTML = '. This page is cached  at $updatetime $gzip .';
    }
    </script>";
        exit();
    }
}
