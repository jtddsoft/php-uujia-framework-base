<?php


namespace uujia\framework\base\common;

use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Container\Container;
use uujia\framework\base\common\lib\Log\Logger;

/**
 * Class Log
 *
 * @package uujia\framework\base\common
 */
class Log extends BaseClass {
	
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
		
		parent::__construct();
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = static::class;
		$this->name_info['intro'] = '日志管理类';
	}
	
	/**
	 * 魔术方法
	 *  可直接访问loggerObj中方法
	 *
	 * @param $method
	 * @param $args
	 *
	 * @return $this|mixed
	 */
	public function __call($method, $args) {
		// 从loggerObj中查找方法
		if (is_callable([$this->getLoggerObj(), $method])) {
			return call_user_func_array([$this->getLoggerObj(), $method], $args);
		}
		
		// todo: 方法不存在
		// $this->getLoggerObj()->error('方法不存在', 1000);
		
		return $this;
	}
	
	/**
	 * 魔术方法
	 *  可直接访问loggerObj中方法
	 *
	 * Date: 2020/9/28
	 * Time: 1:43
	 *
	 * @param $method
	 * @param $args
	 * @return mixed|object|Config|null
	 */
	public static function __callStatic($method, $args) {
		$di = Container::getInstance();
		$me = $di->get(static::class);
		
		// 从loggerObj中查找方法
		if (is_callable([$me->getLoggerObj(), $method])) {
			return call_user_func_array([$me->getLoggerObj(), $method], $args);
		}
		
		// todo: 方法不存在
		// $me->getLoggerObj()->error('方法不存在', 1000);
		
		return $me;
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