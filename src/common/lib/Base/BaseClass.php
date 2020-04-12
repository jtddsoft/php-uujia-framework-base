<?php

namespace uujia\framework\base\common\lib\Base;


use uujia\framework\base\common\traits\NameBase;

class BaseClass implements BaseClassInterface {
	use NameBase;
	
	/**
	 * BaseClass constructor.
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
		$this->name_info['intro'] = '基础类';
	}
	
	
	
}