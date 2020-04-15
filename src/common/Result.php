<?php

namespace uujia\framework\base\common;


use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use uujia\framework\base\common\consts\ResultConst;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Error\ErrorCodeConfig;
use uujia\framework\base\common\lib\Log\Logger;
use uujia\framework\base\common\lib\Utils\Json;
use uujia\framework\base\common\lib\Utils\Response;
use uujia\framework\base\common\traits\NameBase;
use uujia\framework\base\common\traits\ResultBase;

class Result extends BaseClass implements LoggerAwareInterface {
	use ResultBase;
	
	// 配置对象 依赖于配置管理class 必须事先初始化
	/** @var ErrorCodeConfig */
	protected $errObj;
	
	// 日志对象 默认为抽象类 需要子类继承
	/** @var Logger */
	protected $logObj;
	
	// 返回类型
	const RETURN_TYPE = [
			'arr'  => 1, // 返回数组
			'json' => 2, // 返回json
		];
	
	// 返回类型
	protected $return_type = 1;
	// 如果出错直接exit返回
	protected $return_die = true;
	
	/**
	 * 初始化依赖注入
	 *
	 * @param ErrorCodeConfig $errObj
	 * @param Logger          $logObj
	 */
	public function __construct(ErrorCodeConfig $errObj, Logger $logObj) {
		$this->errObj = $errObj;
		$this->logObj = $logObj;
		
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
		$this->name_info['intro'] = '返回值管理';
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
		$_ret         = ResultConst::RESULT_ERROR;
		$_ret['code'] = $code;
		$_ret['msg']  = $msg;
		
		// 记录最后的错误信息
		$this->setLastReturn($_ret);
		
		// 写入日志
		$this->getLogObj()->errorEx($_ret);
		
		if ($this->isReturnDie()) {
			$this->getLogObj()->response();
			exit(Json::je($_ret));
		}
		
		switch ($this->getReturnType()) {
			case self::RETURN_TYPE['json']:
				$this->getLogObj()->response();
				// return json($_ret);
				Response::json($_ret);
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
		$_ret         = ResultConst::RESULT_ERROR;
		$_ret['code'] = $code;
		$_ret['msg']  = $this->getErrObj()->find($code);
		
		// 记录最后的错误信息
		$this->setLastReturn($_ret);
		
		// 写入日志
		$this->getLogObj()->errorEx($_ret);
		
		if ($this->isReturnDie()) {
			$this->getLogObj()->response();
			exit(Json::je($_ret));
		}
		
		switch ($this->getReturnType()) {
			case self::RETURN_TYPE['json']:
				$this->getLogObj()->response();
				// return json($_ret);
				Response::json($_ret);
				break;
		}
		
		return rsErrCode($code);
	}
	
	public function ok() {
		$_ret = ResultConst::RESULT_OK;
		
		// 记录最后的错误信息
		$this->setLastReturn($_ret);
		
		// 写入日志
		$this->getLogObj()->infoEx($_ret);
		
		switch ($this->getReturnType()) {
			case self::RETURN_TYPE['json']:
				$this->getLogObj()->response();
				// return json($_ret);
				Response::json($_ret);
				break;
		}
		
		return $_ret;
	}
	
	public function data($data = []) {
		$_ret           = ResultConst::RESULT_OK;
		$_ret['result'] = $data;
		
		// 记录最后的错误信息
		$this->setLastReturn($_ret);
		
		// 写入日志
		$this->getLogObj()->infoEx($_ret);
		
		switch ($this->getReturnType()) {
			case self::RETURN_TYPE['json']:
				$this->getLogObj()->response();
				// return json($_ret);
				Response::json($_ret);
				break;
		}
		
		return $_ret;
	}
	
	public function return_error() {
		if ($this->isReturnDie()) {
			exit();
		}
		
		switch ($this->getReturnType()) {
			case self::RETURN_TYPE['json']:
				$this->getLogObj()->response();
				// return json($this->getLastReturn());
				Response::json($this->getLastReturn());
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
	 * @return ErrorCodeConfig
	 */
	public function getErrObj() {
		return $this->errObj;
	}
	
	/**
	 * 设置配置对象（一般不要更改）*
	 *
	 * @param ErrorCodeConfig $errObj
	 */
	public function _setErrObj($errObj) {
		$this->errObj = $errObj;
	}
	
	/**
	 * 获取日志对象
	 *  抽象类需要子类继承
	 *
	 * @return Logger
	 */
	public function getLogObj() {
		return $this->logObj;
	}
	
	/**
	 * 设置日志对象
	 *
	 * @param Logger|LoggerInterface $logObj
	 * @return Result
	 */
	public function _setLogObj($logObj) {
		$this->logObj = $logObj;
		
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function setLogger(LoggerInterface $logger) {
		return $this->_setLogObj($logger);
	}
	
}