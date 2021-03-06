<?php


namespace uujia\framework\base\common;


use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Container\Container;
use uujia\framework\base\common\lib\Error\ErrorCodeConfig;

/**
 * Class ErrorConfig
 *
 * @package uujia\framework\base\common
 */
class ErrorConfig extends BaseClass {
	
	/**
	 * @var ErrorCodeConfig
	 */
	protected $_errorCodeConfigObj;
	
	/**
	 * ErrorConfig constructor.
	 *
	 * @param ErrorCodeConfig $_errorCodeConfigObj
	 */
	public function __construct(ErrorCodeConfig $_errorCodeConfigObj) {
		$this->_errorCodeConfigObj = $_errorCodeConfigObj;
		
		parent::__construct();
		
		$this->initConfig();
		
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = static::class;
		$this->name_info['intro'] = '错误代码配置管理类';
	}
	
	/**
	 * 加载初始化配置文件
	 * @return $this
	 */
	public function initConfig() {
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
		
		$this->_errCodeList = $this->getErrorCodeConfigObj()
			->getConfigObj()
			->add(ErrorCodeConfig::ERROR_CODE_NAME, $_errs)
			->getListValue(ErrorCodeConfig::ERROR_CODE_NAME);
		
		
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
		
		return $this;
	}
	
	/**
	 * 魔术方法
	 *  可直接访问ErrorCodeConfig中方法
	 *
	 * @param $method
	 * @param $args
	 *
	 * @return $this|mixed
	 */
	public function __call($method, $args) {
		// 从ErrorCodeConfig中查找方法
		if (is_callable([$this->getErrorCodeConfigObj(), $method])) {
			return call_user_func_array([$this->getErrorCodeConfigObj(), $method], $args);
		}
		
		// todo: 方法不存在
		// $this->getErrorCodeConfigObj()->error('方法不存在', 1000);
		
		return $this;
	}
	
	/**
	 * 魔术方法
	 *  可直接访问ErrorCodeConfig中方法
	 *
	 * Date: 2020/9/28
	 * Time: 1:43
	 *
	 * @param $method
	 * @param $args
	 * @return mixed|object|Config|null
	 */
	public static function __callStatic($method, $args) {
		$di = Container::getInstance();
		$me = $di->get(static::class);
		
		// 从ErrorCodeConfig中查找方法
		if (is_callable([$me->getErrorCodeConfigObj(), $method])) {
			return call_user_func_array([$me->getErrorCodeConfigObj(), $method], $args);
		}
		
		// todo: 方法不存在
		// $me->getConfigManagerObj()->error('方法不存在', 1000);
		
		return $me;
	}
	
	/**
	 * 获取错误码管理对象
	 *  getErrorCodeConfigObj的别名
	 *
	 * @return ErrorCodeConfig
	 */
	public function errObj() {
		return $this->getErrorCodeConfigObj();
	}
	
	/**
	 * @return ErrorCodeConfig
	 */
	public function getErrorCodeConfigObj() {
		return $this->_errorCodeConfigObj;
	}
	
	/**
	 * @param ErrorCodeConfig $errorCodeConfigObj
	 *
	 * @return $this
	 */
	public function _setErrorCodeConfigObj($errorCodeConfigObj) {
		$this->_errorCodeConfigObj = $errorCodeConfigObj;
		
		return $this;
	}
	
	
}