<?php

namespace uujia\framework\base\common\lib\Runner;


use Symfony\Component\Console\Output\OutputInterface;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Base\BaseClassInterface;
use uujia\framework\base\common\lib\Console\ConsoleManager;

/**
 * Class RunnerManager
 *
 * @package uujia\framework\base\common
 */
class RunnerManager extends BaseClass implements RunnerManagerInterface {
	
	/**
	 * 应用名称
	 * @var string $_app_name
	 */
	protected $_app_name = 'app';
	
	/**
	 * Debug
	 * @var bool
	 */
	protected $_debug = false;
	
	/**
	 * 其他自定义参数
	 * @var array $param
	 */
	public $param = [];
	
	/**
	 * RunnerManager constructor.
	 *
	 * @param string $app_name
	 * @param bool   $debug
	 */
	public function __construct($app_name = 'app', $debug = false) {
		$this->_app_name = $app_name;
		$this->_debug = $debug;
		
		parent::__construct();
	}
	
	/**
	 * 初始化
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
		$this->name_info['name'] = static::class;
		$this->name_info['intro'] = '运行时类';
	}
	
	/**
	 * 获取容器中class信息
	 * （实现了BaseClassInterface接口的class）
	 *
	 * Date: 2020/10/12
	 * Time: 1:21
	 *
	 * @return \Generator
	 */
	public function getDIClassInfo() {
		foreach ($this->getContainer() as $key => $item) {
			if (!($item instanceof BaseClassInterface)) {
				continue;
			}
			
			yield $item->getNameInfo();
		}
	}
	
	/**
	 * 打印容器中class信息
	 *
	 * Date: 2020/10/12
	 * Time: 1:25
	 */
	public function echoDIClassInfo() {
		foreach ($this->getDIClassInfo() as $itemNameInfo) {
			echo $itemNameInfo['name'] . " " . $itemNameInfo['intro'] . "\n";
		}
	}
	
	/**
	 * 打印容器中class信息
	 *
	 * Date: 2020/10/12
	 * Time: 1:25
	 */
	public function printTableDIClassInfo(OutputInterface $output) {
		$rows = [
			'header' => ['class name', 'intro'],
			'data' => [],
		];
		
		foreach ($this->getDIClassInfo() as $itemNameInfo) {
			$rows['data'][] = [$itemNameInfo['name'], $itemNameInfo['intro']];
		}
		
		$this->getContainer()->get(ConsoleManager::class)->printTable($rows, $output);
	}
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
	/**
	 * @return string
	 */
	public function getAppName() {
		return $this->_app_name;
	}
	
	/**
	 * @param string $app_name
	 *
	 * @return $this
	 */
	public function _setAppName($app_name) {
		$this->_app_name = $app_name;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getParam(): array {
		return $this->param;
	}
	
	/**
	 * @param array $param
	 *
	 * @return $this
	 */
	public function setParam(array $param) {
		$this->param = $param;
		
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isDebug(): bool {
		return $this->_debug;
	}
	
	/**
	 * @param bool $debug
	 *
	 * @return $this
	 */
	public function setDebug(bool $debug) {
		$this->_debug = $debug;
		
		return $this;
	}
	
	
}