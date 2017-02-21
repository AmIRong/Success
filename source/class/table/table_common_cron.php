<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_cron extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_cron';
		$this->_pk    = 'cronid';

		parent::__construct();
	}
	
	public function fetch_nextrun($timestamp) {
	    $timestamp = intval($timestamp);
	    return DB::fetch_first('SELECT * FROM '.DB::table($this->_table)." WHERE available>'0' AND nextrun<='$timestamp' ORDER BY nextrun LIMIT 1");
	}
}

?>