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
	 * @param RunnerManagerInterface $runnerManagerObj
	 */
	public function __construct(RunnerManagerInterface $runnerManagerObj) {
		$this->_runnerManagerObj = $runnerManagerObj;
		
		parent::__construct();
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