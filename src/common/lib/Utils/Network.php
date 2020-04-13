<?php

namespace uujia\framework\base\common\lib\Utils;


class Network {
	
	/**
	 * Get Server Ip
	 *
	 * @return false|string
	 */
	public static function getServerIp() {
		return gethostbyname($_ENV['COMPUTERNAME']);
	}
	
	
	
	
}