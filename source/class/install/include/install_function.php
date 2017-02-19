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

function timezone_set($timeoffset = 8) {
    if(function_exists('date_default_timezone_set')) {
        @date_default_timezone_set('Etc/GMT'.($timeoffset > 0 ? '-' : '+').(abs($timeoffset)));
    }
}

function transfer_ucinfo(&$post) {
    global $uchidden;
    if(isset($post['ucapi']) && isset($post['ucfounderpw'])) {
        $arr = array(
            'ucapi' => $post['ucapi'],
            'ucfounderpw' => $post['ucfounderpw']
        );
        $uchidden = urlencode(serialize($arr));
    } else {
        $uchidden = '';
    }
}

function show_license() {
    global $self, $uchidden, $step;
    $next = $step + 1;
    if(VIEW_OFF) {

        show_msg('license_contents', lang('license'), 1);

    } else {

        show_header();

        $license = str_replace('  ', '&nbsp; ', lang('license'));
        $lang_agreement_yes = lang('agreement_yes');
        $lang_agreement_no = lang('agreement_no');
        echo <<<EOT
</div>
<div class="main" style="margin-top:-123px;">
	<div class="licenseblock">$license</div>
	<div class="btnbox marginbot">
		<form method="get" autocomplete="off" action="index.php">
		<input type="hidden" name="step" value="$next">
		<input type="hidden" name="uchidden" value="$uchidden">
		<input type="submit" name="submit" value="{$lang_agreement_yes}" style="padding: 2px">&nbsp;
		<input type="button" name="exit" value="{$lang_agreement_no}" style="padding: 2px" onclick="javascript: window.close(); return false;">
		</form>
	</div>
EOT;

        show_footer();

    }
}