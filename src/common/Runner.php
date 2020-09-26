<?php

namespace uujia\framework\base\common;


use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Base\BaseClass;
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