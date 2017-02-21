<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class helper_seo {
    
    public static function get_seosetting($page, $data = array(), $defset = array()) {
        global $_G;
        $searchs = array('{bbname}');
        $replaces = array($_G['setting']['bbname']);
    
        $seotitle = $seodescription = $seokeywords = '';
        $titletext = $defset['seotitle'] ? $defset['seotitle'] : $_G['setting']['seotitle'][$page];
        $descriptiontext = $defset['seodescription'] ? $defset['seodescription'] : $_G['setting']['seodescription'][$page];
        $keywordstext = $defset['seokeywords'] ? $defset['seokeywords'] : $_G['setting']['seokeywords'][$page];
        preg_match_all("/\{([a-z0-9_-]+?)\}/", $titletext.$descriptiontext.$keywordstext, $pageparams);
        if($pageparams) {
            foreach($pageparams[1] as $var) {
                $searchs[] = '{'.$var.'}';
                if($var == 'page') {
                    $data['page'] = $data['page'] > 1 ? lang('core', 'page', array('page' => $data['page'])) : '';
                }
                $replaces[] = $data[$var] ? strip_tags($data[$var]) : '';
            }
            if($titletext) {
                $seotitle = helper_seo::strreplace_strip_split($searchs, $replaces, $titletext);
            }
            if($descriptiontext && (isset($_G['makehtml']) || CURSCRIPT == 'forum' || IS_ROBOT || $_G['adminid'] == 1)) {
                $seodescription = helper_seo::strreplace_strip_split($searchs, $replaces, $descriptiontext);
            }
            if($keywordstext && (isset($_G['makehtml']) || CURSCRIPT == 'forum' || IS_ROBOT || $_G['adminid'] == 1)) {
                $seokeywords = helper_seo::strreplace_strip_split($searchs, $replaces, $keywordstext);
            }
        }
        return array($seotitle, $seodescription, $seokeywords);
    }

}

?>