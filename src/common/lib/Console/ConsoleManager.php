<?php
/**
 *
 * author: lz
 * Date: 2020/9/16
 * Time: 13:30
 */

namespace uujia\framework\base\common\lib\Console;


use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\OutputInterface;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Config\ConfigManagerInterface;

/**
 * Class ConsoleManager
 * Date: 2020/9/16
 * Time: 15:27
 *
 * @package uujia\framework\base\common\lib\Console
 */
class ConsoleManager extends BaseClass implements ConsoleManagerInterface {
	
	/** @var ConfigManagerInterface */
	protected $_configObj;
	
	/**
	 * ConsoleManager constructor.
	 *
	 * @param ConfigManagerInterface $configObj
	 */
	public function __construct(ConfigManagerInterface $configObj) {
		$this->_configObj = $configObj;
		
		parent::__construct();
	}
	
	/**
	 * 初始化
	 *
	 * @return $this
	 */
	public function init() {
		parent::init();
		
		return $this;
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name']  = static::class;
		$this->name_info['intro'] = '控制台管理';
	}
	
	/**
	 * 输出打印表格
	 *
	 * Date: 2020/10/15
	 * Time: 9:33
	 *
	 * @param array           $rows
	 * @param OutputInterface $output
	 */
	public function printTable(array $rows, OutputInterface $output) {
		$_rows = [];
		foreach ($rows['data'] as $row) {
			$_rows[] = $row;
			$_rows[] = new TableSeparator();
		}
		$_rows  = array_slice($_rows, 0, count($_rows) - 1);
		$table = new Table($output);
		$table
			->setHeaders($rows['header'] ?? [])
			->setRows($_rows);
		$table->render();
	}
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
}