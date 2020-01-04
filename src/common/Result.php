<?php

namespace uujia\framework\base\common;


use uujia\framework\base\traits\ResultBase;

class Result {
	use ResultBase;
	
	// 配置对象 依赖于配置管理class 必须事先初始化
	/** @var $configObj ErrorCodeList */
	protected $configObj;
	
	// 日志对象 默认为抽象类 需要子类继承
	/** @var $logObj AbstractLog */
	protected $logObj;
	
	// 返回类型
	public static $_RETURN_TYPE
		= [
			'arr'  => 1, // 返回数组
			'json' => 2, // 返回json
		];
	
	// 返回类型
	private $return_type = 1;
	// 如果出错直接exit返回
	private $return_die = true;
	
	/**
	 * 初始化依赖注入
	 *
	 * @param ErrorCodeList $configObj
	 * @param AbstractLog   $logObj
	 */
	public function __construct(ErrorCodeList $configObj, AbstractLog $logObj) {
		$this->configObj = $configObj;
		$this->logObj = $logObj;
	}
	
	/**
	 * 返回类型
	 *  1 - 数组 内部使用
	 *  2 - json 直接返回ajax前端
	 *
	 * @param int $return_type
	 *
	 * @return $this
	 */
	public function returnType($return_type = 2) {
		$this->setReturnType($return_type);
		return $this;
	}
	
	public function rt($return_type = 2) {
		$this->setReturnType($return_type);
		return $this;
	}
	
	/**
	 * 是否在返回错误时直接exit
	 *
	 * @param bool $die
	 *
	 * @return $this
	 */
	public function die($die = true) {
		$this->setReturnDie($die);
		return $this;
	}
	
	/**************************************************************
	 * 返回输出
	 **************************************************************/
	
	/**
	 * 返回错误
	 *
	 * @param string $msg
	 * @param int    $code
	 *
	 * @return array|\think\response\Json
	 */
	public function error($msg = 'error', $code = 1000) {
		$_ret         = self::$_RESULT_ERROR;
		$_ret['code'] = $code;
		$_ret['msg']  = $msg;
		
		// 记录最后的错误信息
		$this->setLastReturn($_ret);
		
		// 写入日志
		$this->getLogObj()->error($_ret);
		
		if ($this->isReturnDie()) {
			exit();
		}
		
		switch ($this->getReturnType()) {
			case self::$_RETURN_TYPE['json']:
				return json($_ret);
				break;
		}
		
		return $_ret;
	}
	
	/**
	 * 返回错误码 自动解析错误msg
	 *
	 * @param int $code
	 *
	 * @return array|mixed|string
	 */
	public function code($code = 1000) {
		$_ret         = self::$_RESULT_ERROR;
		$_ret['code'] = $code;
		$_ret['msg']  = $this->getConfigObj()->find($code);
		
		// 记录最后的错误信息
		$this->setLastReturn($_ret);
		
		// 写入日志
		$this->getLogObj()->error($_ret);
		
		if ($this->isReturnDie()) {
			exit();
		}
		
		switch ($this->getReturnType()) {
			case self::$_RETURN_TYPE['json']:
				return rjErrCode($code);
				break;
		}
		
		return rsErrCode($code);
	}
	
	public function ok() {
		$_ret = self::$_RESULT_OK;
		
		// 记录最后的错误信息
		$this->setLastReturn($_ret);
		
		// 写入日志
		$this->getLogObj()->info($_ret);
		
		switch ($this->getReturnType()) {
			case self::$_RETURN_TYPE['json']:
				return join($_ret);
				break;
		}
		
		return self::$_RESULT_OK;
	}
	
	public function data($data = []) {
		$_ret           = self::$_RESULT_OK;
		$_ret['result'] = $data;
		
		// 记录最后的错误信息
		$this->setLastReturn($_ret);
		
		// 写入日志
		$this->getLogObj()->info($_ret);
		
		switch ($this->getReturnType()) {
			case self::$_RETURN_TYPE['json']:
				return json($_ret);
				break;
		}
		
		return $_ret;
	}
	
	public function return_error() {
		if ($this->isReturnDie()) {
			exit();
		}
		
		switch ($this->getReturnType()) {
			case self::$_RETURN_TYPE['json']:
				return json($this->getLastReturn());
				break;
		}
		
		return $this->getLastReturn();
	}
	
	/**
	 * 获取返回类型（1 - 内部使用数组 2 - 直接输出的json）
	 * @return int
	 */
	public function getReturnType(): int {
		return $this->return_type;
	}
	
	/**
	 * 设置返回类型
	 * @param int $return_type
	 */
	public function setReturnType(int $return_type) {
		$this->return_type = $return_type;
	}
	
	/**
	 * 获取出错时是否终止运行
	 * @return bool
	 */
	public function isReturnDie(): bool {
		return $this->return_die;
	}
	
	/**
	 * 设置出错时是否终止运行
	 * @param bool $return_die
	 */
	public function setReturnDie(bool $return_die) {
		$this->return_die = $return_die;
	}
	
	/**
	 * 获取配置对象
	 *  期中保存多组错误代码（不是多个，是多组。每组包含一个数组，里面是多个错误代码，可同时支持多个组件自身的错误组。）
	 *
	 * @return ErrorCodeList
	 */
	public function getConfigObj(): ErrorCodeList {
		return $this->configObj;
	}
	
	/**
	 * 设置配置对象（一般不要更改）*
	 * @param ErrorCodeList $configObj
	 */
	public function setConfigObj(ErrorCodeList $configObj) {
		$this->configObj = $configObj;
	}
	
	/**
	 * 获取日志对象
	 *  抽象类需要子类继承
	 * @return AbstractLog
	 */
	public function getLogObj(): AbstractLog {
		return $this->logObj;
	}
	
	/**
	 * 设置日志对象
	 * @param AbstractLog $logObj
	 */
	public function setLogObj(AbstractLog $logObj) {
		$this->logObj = $logObj;
	}
}