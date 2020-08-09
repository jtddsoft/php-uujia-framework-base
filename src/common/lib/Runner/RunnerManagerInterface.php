<?php

namespace uujia\framework\base\common\lib\Runner;

use uujia\framework\base\common\interfaces\BaseInterface;

/**
 * Interface RunnerManagerInterface
 *
 * @package uujia\framework\base\common
 */
interface RunnerManagerInterface extends BaseInterface {
	
	/**
	 * @return string
	 */
	public function getAppName();
	
	/**
	 * @param string $app_name
	 *
	 * @return $this
	 */
	public function _setAppName($app_name);
	
	/**
	 * @return array
	 */
	public function getParam();
	
	/**
	 * @param array $param
	 *
	 * @return $this
	 */
	public function setParam(array $param);
	
	
}