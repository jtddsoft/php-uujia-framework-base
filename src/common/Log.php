<?php


namespace uujia\framework\base\common;

use uujia\framework\base\common\lib\Log\Logger;

/**
 * Class Log
 *
 * @package uujia\framework\base\common
 */
class Log {
	
	/**
	 * @var Logger
	 */
	protected $_loggerObj;
	
	/**
	 * Log constructor.
	 *
	 * @param Logger $logger
	 */
	public function __construct(Logger $logger) {
		$this->_loggerObj = $logger;
		
		
	}
	
	/**
	 * 魔术方法
	 *  可直接访问MQCollection中方法
	 *
	 * @param $method
	 * @param $args
	 *
	 * @return $this|mixed
	 */
	public function __call($method, $args) {
		// 从MQCollection中查找方法
		if (is_callable([$this->getLoggerObj(), $method])) {
			return call_user_func_array([$this->getLoggerObj(), $method], $args);
		}
		
		// todo: 方法不存在
		// $this->getLoggerObj()->error('方法不存在', 1000);
		
		return $this;
	}
	
	/**
	 * 获取Logger集合对象
	 *  getLoggerObj的别名
	 *
	 * @return Logger
	 */
	public function mqObj() {
		return $this->getLoggerObj();
	}
	
	/**
	 * @return Logger
	 */
	public function getLoggerObj() {
		return $this->_loggerObj;
	}
	
	/**
	 * @param Logger $loggerObj
	 *
	 * @return $this
	 */
	public function setLoggerObj(Logger $loggerObj) {
		$this->_loggerObj = $loggerObj;
		
		return $this;
	}
	
}