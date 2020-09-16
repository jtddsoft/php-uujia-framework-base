<?php

namespace uujia\framework\base\common\lib\Runner;


use uujia\framework\base\common\lib\Base\BaseClass;

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