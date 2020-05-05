<?php

namespace uujia\framework\base\common;


use uujia\framework\base\common\lib\Runner\RunnerManager;

/**
 * Class Runner
 *
 * @package uujia\framework\base\common
 */
class Runner {
	
	/**
	 * @var RunnerManager
	 */
	protected $_runnerManagerObj;
	
	/**
	 * Runner constructor.
	 *
	 * @param Result $ret
	 * @param Config $configObj
	 * @param string $app_name
	 */
	public function __construct(RunnerManager $runnerManagerObj) {
		$this->_runnerManagerObj = $runnerManagerObj;
	}
	
	/**
	 * 获取运行时管理对象
	 *
	 * @return RunnerManager
	 */
	public function runnerObj() {
		return $this->getRunnerManagerObj();
	}
	
	/**
	 * @return RunnerManager
	 */
	public function getRunnerManagerObj(): RunnerManager {
		return $this->_runnerManagerObj;
	}
	
	/**
	 * @param RunnerManager $runnerManagerObj
	 * @return $this
	 */
	public function _setRunnerManagerObj(RunnerManager $runnerManagerObj) {
		$this->_runnerManagerObj = $runnerManagerObj;
		
		return $this;
	}
	
	
}