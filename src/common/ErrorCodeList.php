<?php


namespace uujia\framework\base\common;


use uujia\framework\base\common\lib\Tree\TreeFunc;
use uujia\framework\base\common\traits\NameBase;

class ErrorCodeList {
	use NameBase;
	
	const ERROR_CODE_NAME = 'error_code';
	
	/** @var Config $_configObj */
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
	 * @param Config       $configObj
	 * @param array|string $err
	 */
	public function __construct(Config $configObj, $err = '') {
		$this->_configObj = $configObj;
		
		$_errs = [];
		
		if (!empty($err)) {
			if (is_string($err)) {
				array_unshift($_errs, $err);
			} elseif (is_array($err)) {
				foreach ($err as $row) {
					array_push($_errs, $err);
				}
			}
		}
		
		array_push($_errs, __DIR__ . "/../config/error_code.php");
		
		$this->_errCodeList = $configObj
			->add(self::ERROR_CODE_NAME, $_errs)
			->getListValue(self::ERROR_CODE_NAME);
		
		
		// // 实例化
		// $this->_errCodeList = new TreeFunc();
		//
		// $configObj->getList()->set(self::$_ERROR_CODE_NAME, $this->_errCodeList);
		//
		// // 自身code
		// // self::$errCodeList[] = include __DIR__ . "/../config/error_code.php";
		// $this->add(__DIR__ . "/../config/error_code.php");
		//
		// if (!empty($err)) {
		// 	if (is_string($err)) {
		// 		$this->unshift($err);
		// 	} elseif (is_array($err)) {
		// 		$this->add($err);
		// 	}
		// }
		
		$this->init();
	}
	
	/**
	 * 初始化
	 * @return $this
	 */
	public function init() {
		$this->initNameInfo();
		
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
	 * @return Config
	 */
	public function getConfigObj(): Config {
		return $this->_configObj;
	}
	
	/**
	 * @param Config $configObj
	 */
	public function _setConfigObj(Config $configObj) {
		$this->_configObj = $configObj;
	}
}