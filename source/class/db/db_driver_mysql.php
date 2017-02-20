<?php


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class db_driver_mysql
{
    function set_config($config) {
        $this->config = &$config;
        $this->tablepre = $config['1']['tablepre'];
        if(!empty($this->config['map'])) {
            $this->map = $this->config['map'];
            for($i = 1; $i <= 100; $i++) {
                if(isset($this->map['forum_thread'])) {
                    $this->map['forum_thread_'.$i] = $this->map['forum_thread'];
                }
                if(isset($this->map['forum_post'])) {
                    $this->map['forum_post_'.$i] = $this->map['forum_post'];
                }
                if(isset($this->map['forum_attachment']) && $i <= 10) {
                    $this->map['forum_attachment_'.($i-1)] = $this->map['forum_attachment'];
                }
            }
            if(isset($this->map['common_member'])) {
                $this->map['common_member_archive'] =
                $this->map['common_member_count'] = $this->map['common_member_count_archive'] =
                $this->map['common_member_status'] = $this->map['common_member_status_archive'] =
                $this->map['common_member_profile'] = $this->map['common_member_profile_archive'] =
                $this->map['common_member_field_forum'] = $this->map['common_member_field_forum_archive'] =
                $this->map['common_member_field_home'] = $this->map['common_member_field_home_archive'] =
                $this->map['common_member_validate'] = $this->map['common_member_verify'] =
                $this->map['common_member_verify_info'] = $this->map['common_member'];
            }
        }
    }
}