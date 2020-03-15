<?php

namespace uujia\framework\base\common\lib\Server;

use uujia\framework\base\traits\NameBase;
use uujia\framework\base\traits\ResultBase;

class ServerRoute {
	use NameBase;
	use ResultBase;
	
	
	/**
	 * Local constructor.
	 */
	public function __construct() {
		
		
		$this->init();
	}
	
	/**
	 * 初始化
	 * @return $this
	 */
	public function init() {
		$this->initNameInfo();
		
		return $this;
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = self::class;
		$this->name_info['intro'] = '服务器配置';
	}
	
	/**************************************************
	 * getter setter
	 **************************************************/
	
	
	
	
}