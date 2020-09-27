<?php

namespace uujia\framework\base\common;


use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Container\Container;
use uujia\framework\base\common\lib\Runner\RunnerManager;
use uujia\framework\base\common\lib\Runner\RunnerManagerInterface;

/**
 * Class Runner
 *
 * @package uujia\framework\base\common
 */
class Runner extends BaseClass {
	
	/**
	 * @var RunnerManagerInterface
	 */
	protected $_runnerManagerObj;
	
	/**
	 * Runner constructor.
	 *
	 * @param RunnerManagerInterface|null $runnerManagerObj
	 *
	 * @AutoInjection(arg = "runnerManagerObj", name = "RunnerManager")
	 */
	public function __construct(?RunnerManagerInterface $runnerManagerObj) {
		$this->_runnerManagerObj = $runnerManagerObj;
		
		parent::__construct();
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = static::class;
		$this->name_info['intro'] = '运行时管理类';
	}
	
	/**
	 * 魔术方法
	 *  可直接访问runnerManagerObj中方法
	 *
	 * @param $method
	 * @param $args
	 *
	 * @return $this|mixed
	 */
	public function __call($method, $args) {
		// 从runnerManagerObj中查找方法
		if (is_callable([$this->getRunnerManagerObj(), $method])) {
			return call_user_func_array([$this->getRunnerManagerObj(), $method], $args);
		}
		
		// todo: 方法不存在
		// $this->getRunnerManagerObj()->error('方法不存在', 1000);
		
		return $this;
	}
	
	/**
	 * 魔术方法
	 *  可直接访问runnerManagerObj中方法
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
		
		// 从runnerManagerObj中查找方法
		if (is_callable([$me->getRunnerManagerObj(), $method])) {
			return call_user_func_array([$me->getRunnerManagerObj(), $method], $args);
		}
		
		// todo: 方法不存在
		// $me->getRunnerManagerObj()->error('方法不存在', 1000);
		
		return $me;
	}
	
	/**
	 * 获取运行时管理对象
	 *
	 * @return RunnerManagerInterface
	 */
	public function runnerObj() {
		return $this->getRunnerManagerObj();
	}
	
	/**
	 * @return RunnerManagerInterface
	 */
	public function getRunnerManagerObj() {
		return $this->_runnerManagerObj;
	}
	
	/**
	 * @param RunnerManagerInterface $runnerManagerObj
	 * @return $this
	 */
	public function _setRunnerManagerObj(RunnerManagerInterface $runnerManagerObj) {
		$this->_runnerManagerObj = $runnerManagerObj;
		
		return $this;
	}
	
	
}