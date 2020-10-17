<?php

namespace uujia\framework\base\common\lib\Runner;

use Symfony\Component\Console\Output\OutputInterface;
use uujia\framework\base\common\interfaces\BaseInterface;

/**
 * Interface RunnerManagerInterface
 *
 * @package uujia\framework\base\common
 */
interface RunnerManagerInterface extends BaseInterface {
	
	/**
	 * 获取容器中class信息
	 * （实现了BaseClassInterface接口的class）
	 *
	 * Date: 2020/10/12
	 * Time: 1:21
	 *
	 * @return \Generator
	 */
	public function getDIClassInfo();
	
	/**
	 * 打印容器中class信息
	 *
	 * Date: 2020/10/12
	 * Time: 1:25
	 */
	public function echoDIClassInfo();
	
	/**
	 * 打印容器中class信息
	 *
	 * Date: 2020/10/12
	 * Time: 1:25
	 *
	 * @param OutputInterface $output
	 */
	public function printTableDIClassInfo(OutputInterface $output);
	
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
	
	/**
	 * @return bool
	 */
	public function isDebug(): bool;
	
	/**
	 * @param bool $debug
	 *
	 * @return $this
	 */
	public function setDebug(bool $debug);
	
}