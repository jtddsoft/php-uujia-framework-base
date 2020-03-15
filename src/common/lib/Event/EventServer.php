<?php

namespace uujia\framework\base\common\lib\Event;


use uujia\framework\base\common\consts\ServerConst;
use uujia\framework\base\common\lib\Tree\TreeFuncData;
use uujia\framework\base\common\lib\Tree\TreeFunc;
use uujia\framework\base\common\lib\Utils\Arr;
use uujia\framework\base\traits\InstanceBase;
use uujia\framework\base\traits\NameBase;
use uujia\framework\base\traits\ResultBase;

class EventServer {
	use NameBase;
	use ResultBase;
	use InstanceBase;
	
	/** @var Local $_local */
	protected $_localObj = null;
	
	// todo: POST
	protected $_postObj = null;
	
	/**
	 * EventServer constructor.
	 *
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
		$this->name_info['intro'] = '事件处理服务';
	}
	
	
	/**************************************************
	 * getter setter
	 **************************************************/
	
	/**
	 * @return Local
	 */
	public function getLocalObj(): Local {
		$this->_localObj === null && $this->_localObj = new Local($this);
		
		return $this->_localObj;
	}
	
	/**
	 * @param Local $localObj
	 * @return $this
	 */
	public function _setLocal(Local $localObj) {
		$this->_localObj = $localObj;
		
		return $this;
	}
	
}