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
	
	/** @var ServerRouteLocal $_local */
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
	 * @return ServerRouteLocal
	 */
	public function getLocalObj(): ServerRouteLocal {
		$this->_localObj === null && $this->_localObj = new ServerRouteLocal($this);
		
		return $this->_localObj;
	}
	
	/**
	 * @param ServerRouteLocal $localObj
	 *
	 * @return $this
	 */
	public function _setLocal(ServerRouteLocal $localObj) {
		$this->_localObj = $localObj;
		
		return $this;
	}
	
}