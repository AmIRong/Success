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

if(!$navtitle) {
    $navtitle = $_G['setting']['navs'][2]['navname'];
    $nobbname = false;
} else {
    $nobbname = true;
}
if(!$metadescription) {
    $metadescription = $navtitle;
}
if(!$metakeywords) {
    $metakeywords = $navtitle;
}

if($_G['setting']['indexhot']['status'] && $_G['cache']['heats']['expiration'] < TIMESTAMP) {
    require_once libfile('function/cache');
    updatecache('heats');
}

if($_G['uid'] && empty($_G['cookie']['nofavfid'])) {
    $favfids = array();
    $forum_favlist = C::t('home_favorite')->fetch_all_by_uid_idtype($_G['uid'], 'fid');
    if(!$forum_favlist) {
        dsetcookie('nofavfid', 1, 31536000);
    }
    foreach($forum_favlist as $key => $favorite) {
        if(defined('IN_MOBILE')) {
            $forum_favlist[$key]['title'] = strip_tags($favorite['title']);
        }
        $favfids[] = $favorite['id'];
    }
    if($favfids) {
        $favforumlist = C::t('forum_forum')->fetch_all($favfids);
        $favforumlist_fields = C::t('forum_forumfield')->fetch_all($favfids);
        foreach($favforumlist as $id => $forum) {
            if($favforumlist_fields[$forum['fid']]['fid']) {
                $favforumlist[$id] = array_merge($forum, $favforumlist_fields[$forum['fid']]);
            }
            forum($favforumlist[$id]);
        }

    }
}

if(!$gid && $_G['setting']['collectionstatus'] && ($_G['setting']['collectionrecommendnum'] || !$_G['setting']['hidefollowcollection'])) {
    require_once libfile('function/cache');
    loadcache('collection_index');
    $collectionrecommend = dunserialize($_G['setting']['collectionrecommend']);
    if(TIMESTAMP - $_G['cache']['collection_index']['dateline'] > 3600 * 4) {
        $collectiondata = $followdata = array();
        if($_G['setting']['collectionrecommendnum']) {
            if($collectionrecommend['ctids']) {
                $collectionrecommend['ctidsKey'] = array_keys($collectionrecommend['ctids']);
                $tmpcollection = C::t('forum_collection')->fetch_all($collectionrecommend['ctidsKey']);
                foreach($collectionrecommend['ctids'] as $ctid=>$setcollection) {
                    if($tmpcollection[$ctid]) {
                        $collectiondata[$ctid] = $tmpcollection[$ctid];
                    }
                }
                unset($tmpcollection, $ctid, $setcollection);
            }
            if($collectionrecommend['autorecommend']) {
                require_once libfile('function/collection');
                $autorecommenddata = getHotCollection(500);
            }
        }

        savecache('collection_index', array('dateline' => TIMESTAMP, 'data' => $collectiondata, 'auto' => $autorecommenddata));
        $collectiondata = array('data' => $collectiondata, 'auto' => $autorecommenddata);
    } else {
        $collectiondata = &$_G['cache']['collection_index'];
    }

    if($_G['setting']['showfollowcollection']) {
        $followcollections = $_G['uid'] ? C::t('forum_collectionfollow')->fetch_all_by_uid($_G['uid']) : array();;
        if($followcollections) {
            $collectiondata['follows'] = C::t('forum_collection')->fetch_all(array_keys($followcollections), 'dateline', 'DESC', 0, $_G['setting']['showfollowcollection']);
        }
    }
    if($collectionrecommend['autorecommend'] && $collectiondata['auto']) {
        $randrecommend = array_rand($collectiondata['auto'], min($collectionrecommend['autorecommend'], count($collectiondata['auto'])));
        if($randrecommend && !is_array($randrecommend)) {
            $collectiondata['data'][$randrecommend] = $collectiondata['auto'][$randrecommend];
        } else {
            foreach($randrecommend as $ctid) {
                $collectiondata['data'][$ctid] = $collectiondata['auto'][$ctid];
            }
        }
    }
    if($collectiondata['data']) {
        $collectiondata['data'] = array_slice($collectiondata['data'], 0, $collectionrecommend['autorecommend'], true);
    }

}

if(empty($gid) && empty($_G['member']['accessmasks']) && empty($showoldetails)) {
    extract(get_index_memory_by_groupid($_G['member']['groupid']));
    if(defined('FORUM_INDEX_PAGE_MEMORY') && FORUM_INDEX_PAGE_MEMORY) {
        categorycollapse();
        if(!defined('IN_ARCHIVER')) {
            include template('diy:forum/discuz');
        } else {
            include loadarchiver('forum/discuz');
        }
        dexit();
    }
}

$grids = array();
if($_G['setting']['grid']['showgrid']) {
    loadcache('grids');
    $cachelife = $_G['setting']['grid']['cachelife'] ? $_G['setting']['grid']['cachelife'] : 600;
    $now = dgmdate(TIMESTAMP, lang('form/misc', 'y_m_d')).' '.lang('forum/misc', 'week_'.dgmdate(TIMESTAMP, 'w'));
    if(TIMESTAMP - $_G['cache']['grids']['cachetime'] < $cachelife) {
        $grids = $_G['cache']['grids'];
    } else {
        $images = array();
        $_G['setting']['grid']['fids'] = in_array(0, $_G['setting']['grid']['fids']) ? 0 : $_G['setting']['grid']['fids'];

        if($_G['setting']['grid']['gridtype']) {
            $grids['digest'] = C::t('forum_thread')->fetch_all_for_guide('digest', 0, array(), 3, 0, 0, 10, $_G['setting']['grid']['fids']);
        } else {
            $images = C::t('forum_threadimage')->fetch_all_order_by_tid(10);
            foreach($images as $key => $value) {
                $tids[$value['tid']] = $value['tid'];
            }
            $grids['image'] = C::t('forum_thread')->fetch_all_by_tid($tids);
        }
        $grids['newthread'] = C::t('forum_thread')->fetch_all_for_guide('newthread', 0, array(), 0, 0, 0, 10, $_G['setting']['grid']['fids']);

        $grids['newreply'] = C::t('forum_thread')->fetch_all_for_guide('reply', 0, array(), 0, 0, 0, 10, $_G['setting']['grid']['fids']);
        $grids['hot'] = C::t('forum_thread')->fetch_all_for_guide('hot', 0, array(), 3, 0, 0, 10, $_G['setting']['grid']['fids']);

        $_G['forum_colorarray'] = array('', '#EE1B2E', '#EE5023', '#996600', '#3C9D40', '#2897C5', '#2B65B7', '#8F2A90', '#EC1282');
        foreach($grids as $type => $gridthreads) {
            foreach($gridthreads as $key => $gridthread) {
                $gridthread['dateline'] = str_replace('"', '\'', dgmdate($gridthread['dateline'], 'u', '9999', getglobal('setting/dateformat')));
                $gridthread['lastpost'] = str_replace('"', '\'', dgmdate($gridthread['lastpost'], 'u', '9999', getglobal('setting/dateformat')));
                if($gridthread['highlight'] && $_G['setting']['grid']['highlight']) {
                    $string = sprintf('%02d', $gridthread['highlight']);
                    $stylestr = sprintf('%03b', $string[0]);

                    $gridthread['highlight'] = ' style="';
                    $gridthread['highlight'] .= $stylestr[0] ? 'font-weight: bold;' : '';
                    $gridthread['highlight'] .= $stylestr[1] ? 'font-style: italic;' : '';
                    $gridthread['highlight'] .= $stylestr[2] ? 'text-decoration: underline;' : '';
                    $gridthread['highlight'] .= $string[1] ? 'color: '.$_G['forum_colorarray'][$string[1]] : '';
                    $gridthread['highlight'] .= '"';
                } else {
                    $gridthread['highlight'] = '';
                }
                if($_G['setting']['grid']['textleng']) {
                    $gridthread['oldsubject'] = dhtmlspecialchars($gridthread['subject']);
                    $gridthread['subject'] = cutstr($gridthread['subject'], $_G['setting']['grid']['textleng']);
                }

                $grids[$type][$key] = $gridthread;
            }
        }
        if(!$_G['setting']['grid']['gridtype']) {

            $focuspic = $focusurl = $focustext = array();
            $grids['focus'] = 'config=5|0xffffff|0x0099ff|50|0xffffff|0x0099ff|0x000000';
            foreach($grids['image'] as $ithread) {
                if($ithread['displayorder'] < 0) {
                    continue;
                }
                if($images[$ithread['tid']]['remote']) {
                    $imageurl = $_G['setting']['ftp']['attachurl'].'forum/'.$images[$ithread['tid']]['attachment'];
                } else {
                    $imageurl = $_G['setting']['attachurl'].'forum/'.$images[$ithread['tid']]['attachment'];
                }
                $grids['slide'][$ithread['tid']] = array(
                    'image' => $imageurl,
                    'url' => 'forum.php?mod=viewthread&tid='.$ithread['tid'],
                    'subject' => $ithread['subject']
                );
            }
        }
        $grids['cachetime'] = TIMESTAMP;
        savecache('grids', $grids);
    }
}

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

function get_index_memory_by_groupid($key) {
    $enable = getglobal('setting/memory/forumindex');
    if($enable !== null && memory('check')) {
        if(IS_ROBOT) {
            $key = 'for_robot';
        }
        $ret = memory('get', 'forum_index_page_'.$key);
        define('FORUM_INDEX_PAGE_MEMORY', $ret ? 1 : 0);
        if($ret) {
            return $ret;
        }
    }
    return array('none' => null);
}
