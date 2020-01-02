<?php

namespace uujia\framework\base\common;


class Result {
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
	
	// 返回ok模板
	public static $_RESULT_OK
		= [
			'code'   => 200,
			'status' => 'success',
			'msg'    => '操作完成',
			'data'   => [],
		];
	
	// 返回error模板
	public static $_RESULT_ERROR
		= [
			'code'   => 1000,
			'status' => 'error',
			'msg'    => '操作失败',
			'data'   => [],
		];
	
	// 验证正确的依据code = 200
	public static $_OK_CODE = 200;
	
	// 返回类型
	private $return_type = 1;
	// 如果出错直接exit返回
	private $return_die = true;
	
	/**
	 * 缓存返回值
	 */
	private $code = 200;
	private $status = 'success';
	private $msg = '操作完成';
	private $data = [];
	
	// 缓存最后一次返回值 包括code msg。。。
	private $last_return = [];
	
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
	 * json_encode
	 *
	 * @param $value
	 *
	 * @return false|string
	 */
	public static function je($value) {
		return json_encode($value, JSON_UNESCAPED_UNICODE);
	}
	
	/**
	 * json_decode
	 *
	 * @param $json
	 *
	 * @return mixed
	 */
	public static function jd($json) {
		return json_decode($json, true);
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
		
		$this->setCode($_ret['code']);
		$this->setMsg($_ret['msg']);
		$this->setStatus($_ret['status']);
		$this->setData($_ret['data']);
		
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
		
		$this->setCode($_ret['code']);
		$this->setMsg($_ret['msg']);
		$this->setStatus($_ret['status']);
		$this->setData($_ret['data']);
		
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
		
		$this->setCode($_ret['code']);
		$this->setMsg($_ret['msg']);
		$this->setStatus($_ret['status']);
		$this->setData($_ret['data']);
		
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
	
	/**************************************************************
	 * 验证输出
	 **************************************************************/
	
	/**
	 * 是否正确
	 * @return bool
	 */
	public function isOk() {
		if ($this->getCode() == self::$_OK_CODE) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * 是否出错
	 * @return bool
	 */
	public function isErr() {
		return !$this->isOk();
	}
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
	/**
	 * 获取code
	 * @return int
	 */
	public function getCode(): int {
		return $this->code;
	}
	
	/**
	 * 设置code
	 * @param int $code
	 */
	public function setCode(int $code) {
		$this->code = $code;
	}
	
	/**
	 * 获取状态status
	 * @return string
	 */
	public function getStatus(): string {
		return $this->status;
	}
	
	/**
	 * 设置状态status
	 * @param string $status
	 */
	public function setStatus(string $status) {
		$this->status = $status;
	}
	
	/**
	 * 获取消息msg
	 * @return string
	 */
	public function getMsg(): string {
		return $this->msg;
	}
	
	/**
	 * 设置消息msg
	 * @param string $msg
	 */
	public function setMsg(string $msg) {
		$this->msg = $msg;
	}
	
	/**
	 * 获取数据data
	 * @return array
	 */
	public function getData(): array {
		return $this->data;
	}
	
	/**
	 * 设置数据data
	 * @param array $data
	 */
	public function setData(array $data) {
		$this->data = $data;
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
	 * 获取最后一次返回值
	 * @return array
	 */
	public function getLastReturn(): array {
		return $this->last_return;
	}
	
	/**
	 * 设置最后一次返回值
	 * @param array $last_return
	 */
	public function setLastReturn(array $last_return) {
		$this->last_return = $last_return;
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