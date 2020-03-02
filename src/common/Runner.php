<?php

namespace uujia\framework\base\common;


use uujia\framework\base\traits\NameBase;

class Runner extends Base {
	use NameBase;
	
	/** @var $_configObj Config */
	protected $_configObj;
	
	/**
	 * 应用名称
	 * @var string $_app_name
	 */
	protected $_app_name = '';
	
	/**
	 * Runner constructor.
	 *
	 * @param Result $ret
	 * @param Config $configObj
	 * @param string $app_name
	 */
	public function __construct(Result $ret, Config $configObj, $app_name = '') {
		parent::__construct($ret);
		
		$this->_configObj = $configObj;
		$this->_app_name = $app_name;
	}
	
	/**
	 * 初始化
	 * @return $this
	 */
	public function init() {
		parent::init();
		
		return $this;
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = self::class;
		$this->name_info['intro'] = '运行时类';
	}
	
	/**
	 * @return string
	 */
	public function getAppName() {
		return $this->_app_name;
	}
	
	/**
	 * @param string $app_name
	 *
	 * @return $this
	 */
	public function _setAppName($app_name) {
		$this->_app_name = $app_name;
		
		return $this;
	}
	
	
}