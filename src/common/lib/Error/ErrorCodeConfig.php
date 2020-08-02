<?php


namespace uujia\framework\base\common\lib\Error;


use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Config\ConfigManagerInterface;
use uujia\framework\base\common\lib\Tree\TreeFunc;

/**
 * Class ErrorCodeConfig
 *
 * @package uujia\framework\base\common\lib\Error
 */
class ErrorCodeConfig extends BaseClass {
	
	const ERROR_CODE_NAME = 'error_code';
	
	/**
	 * @var ConfigManagerInterface
	 */
	protected $_configObj;
	
	/**
	 * 加入多组error_code
	 *  每组格式：[
	 *      10000 => '用户名不能为空',
	 *      10001 => '用户名密码错误',
	 *  ]
	 *
	 * @var TreeFunc $_errCodeList
	 */
	protected $_errCodeList;
	
	/**
	 * ErrorCodeList constructor.
	 *  依赖Config
	 *
	 * @param ConfigManagerInterface $configObj
	 * @param array|string  $err
	 */
	public function __construct(ConfigManagerInterface $configObj, $err = '') {
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
		$this->name_info['name'] = self::class;
		$this->name_info['intro'] = '错误码列表管理';
	}
	
	// /**
	//  * 开头插入
	//  *
	//  * @param string|array $path
	//  * @return ErrorCodeList
	//  */
	// public function unshift($path) {
	// 	// array_unshift(self::$errCodeList, $data);
	//
	// 	if (is_string($path)) {
	// 		$item = new TreeFunc();
	// 		$item->getData()->set(function ($data, $it) use ($path) {
	// 			$errConfig = include $path;
	//
	// 			return $errConfig;
	// 		});
	//
	// 		$this->getErrCodeList()->unshift($item);
	// 	} elseif (is_array($path)) {
	// 		foreach ($path as $row) {
	// 			$item = new TreeFunc();
	// 			$item->getData()->set(function ($data, $it) use ($row) {
	// 				$errConfig = include $row;
	//
	// 				return $errConfig;
	// 			});
	//
	// 			$this->getErrCodeList()->unshift($item);
	// 		}
	// 	}
	//
	// 	return $this;
	// }
	//
	// /**
	//  * 尾部添加
	//  *
	//  * @param string|array $path
	//  * @return $this
	//  */
	// public function add($path) {
	// 	if (is_string($path)) {
	// 		$item = new TreeFunc();
	// 		$item->getData()->set(function ($data, $it) use ($path) {
	// 			$errConfig = include $path;
	//
	// 			return $errConfig;
	// 		});
	//
	// 		$this->getErrCodeList()->add($item);
	// 	} elseif (is_array($path)) {
	// 		foreach ($path as $row) {
	// 			$item = new TreeFunc();
	// 			$item->getData()->set(function ($data, $it) use ($row) {
	// 				$errConfig = include $row;
	//
	// 				return $errConfig;
	// 			});
	//
	// 			$this->getErrCodeList()->add($item);
	// 		}
	// 	}
	//
	// 	return $this;
	// }
	
	/**
	 * 查找时从第一组开始 只要找到直接返回
	 *
	 * @param $code
	 *
	 * @return mixed|string
	 */
	public function find($code) {
		$msg = '未知异常';
		
		// foreach ($this->_errCodeList as $errItem) {
		// 	$_err = $errItem[self::$_ERROR_CODE_NAME];
		// 	if (in_array($code, $_err)) {
		// 		$msg = $_err[$code];
		// 		break;
		// 	}
		// }
		
		$re = $this->getErrCodeList()->wFindData(function ($item, $i, $me, $data, $value) use ($code) {
			$_err = $value[self::ERROR_CODE_NAME];
			if (array_key_exists($code, $_err)) {
				//$msg = $_err[$code];
				return true;
			}
			
			return false;
		});
		
		if ($re !== false) {
			$msg = $re['value'][self::ERROR_CODE_NAME][$code];
		}
		
		return $msg;
	}
	
	/**
	 * @return TreeFunc
	 */
	public function getErrCodeList(): TreeFunc {
		return $this->_errCodeList;
	}
	
	/**
	 * @param TreeFunc $errCodeList
	 */
	public function _setErrCodeList(TreeFunc $errCodeList) {
		$this->_errCodeList = $errCodeList;
	}
	
	/**
	 * @return ConfigManagerInterface
	 */
	public function getConfigObj() {
		return $this->_configObj;
	}
	
	/**
	 * @param ConfigManagerInterface $configObj
	 */
	public function _setConfigObj($configObj) {
		$this->_configObj = $configObj;
	}
}