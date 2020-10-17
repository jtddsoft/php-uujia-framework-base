<?php

namespace uujia\framework\base\common;


use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Console\ConsoleManager;
use uujia\framework\base\common\lib\Console\ConsoleManagerInterface;
use uujia\framework\base\common\lib\Container\Container;

/**
 * Class Console
 * Date: 2020/10/15
 * Time: 10:26
 *
 * @package uujia\framework\base\common
 */
class Console extends BaseClass {
	
	/**
	 * @var ConsoleManagerInterface
	 */
	protected $_consoleManagerObj;
	
	/**
	 * Console constructor.
	 *
	 * @param ConsoleManagerInterface|null $consoleManagerObj
	 *
	 * @AutoInjection(arg = "consoleManagerObj", name = "ConsoleManager")
	 */
	public function __construct(?ConsoleManagerInterface $consoleManagerObj) {
		$this->_consoleManagerObj = $consoleManagerObj;
		
		parent::__construct();
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = static::class;
		$this->name_info['intro'] = '命令行管理类';
	}
	
	/**
	 * 魔术方法
	 *  可直接访问consoleManagerObj中方法
	 *
	 * @param $method
	 * @param $args
	 *
	 * @return $this|mixed
	 */
	public function __call($method, $args) {
		// 从consoleManagerObj中查找方法
		if (is_callable([$this->getConsoleManagerObj(), $method])) {
			return call_user_func_array([$this->getConsoleManagerObj(), $method], $args);
		}
		
		// todo: 方法不存在
		// $this->getConsoleManagerObj()->error('方法不存在', 1000);
		
		return $this;
	}
	
	/**
	 * 魔术方法
	 *  可直接访问consoleManagerObj中方法
	 *
	 * Date: 2020/10/15
	 * Time: 10:25
	 *
	 * @param $method
	 * @param $args
	 * @return mixed|object|Config|null
	 */
	public static function __callStatic($method, $args) {
		$di = Container::getInstance();
		$me = $di->get(static::class);
		
		// 从consoleManagerObj中查找方法
		if (is_callable([$me->getConsoleManagerObj(), $method])) {
			return call_user_func_array([$me->getConsoleManagerObj(), $method], $args);
		}
		
		// todo: 方法不存在
		// $me->getConsoleManagerObj()->error('方法不存在', 1000);
		
		return $me;
	}
	
	/**
	 * 获取运行时管理对象
	 *
	 * @return ConsoleManagerInterface
	 */
	public function consoleObj() {
		return $this->getConsoleManagerObj();
	}
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
	/**
	 * @return ConsoleManagerInterface
	 */
	public function getConsoleManagerObj() {
		return $this->_consoleManagerObj;
	}
	
	/**
	 * @param ConsoleManagerInterface $consoleManagerObj
	 * @return $this
	 */
	public function _setConsoleManagerObj(ConsoleManagerInterface $consoleManagerObj) {
		$this->_consoleManagerObj = $consoleManagerObj;
		
		return $this;
	}
	
	
}