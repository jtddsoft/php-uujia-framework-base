<?php
/**
 *
 * author: lz
 * Date: 2020/9/16
 * Time: 13:30
 */

namespace uujia\framework\base\common\lib\Console;


use Symfony\Component\Console\Output\OutputInterface;
use uujia\framework\base\common\interfaces\BaseInterface;

/**
 * Interface ConsoleManagerInterface
 * Date: 2020/10/15
 * Time: 9:40
 *
 * @package uujia\framework\base\common\lib\Console
 */
interface ConsoleManagerInterface extends BaseInterface {
	
	/**
	 * 输出打印表格
	 *
	 * Date: 2020/10/15
	 * Time: 9:33
	 *
	 * @param array           $rows
	 * @param OutputInterface $output
	 */
	public function printTable(array $rows, OutputInterface $output);
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
	
}