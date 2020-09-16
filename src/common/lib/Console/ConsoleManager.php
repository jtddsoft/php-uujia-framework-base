<?php
/**
 *
 * author: lz
 * Date: 2020/9/16
 * Time: 13:30
 */

namespace uujia\framework\base\common\lib\Console;


use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Config\ConfigManagerInterface;

/**
 * Class ConsoleManager
 * Date: 2020/9/16
 * Time: 15:27
 *
 * @package uujia\framework\base\common\lib\Console
 */
class ConsoleManager extends BaseClass {
	
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
		$this->name_info['intro'] = '控制台管理';
	}
	
	
}