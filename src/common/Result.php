<?php

namespace uujia\framework\base\common;


class Result {
	// 配置对象 依赖于配置管理class 必须事先初始化
	protected $configObj;
	
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
	 */
	public function __construct(ErrorCodeList $configObj) {
		$this->configObj = $configObj;
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
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
	/**
	 * @return int
	 */
	public function getCode(): int {
		return $this->code;
	}
	
	/**
	 * @param int $code
	 */
	public function setCode(int $code) {
		$this->code = $code;
	}
	
	/**
	 * @return string
	 */
	public function getStatus(): string {
		return $this->status;
	}
	
	/**
	 * @param string $status
	 */
	public function setStatus(string $status) {
		$this->status = $status;
	}
	
	/**
	 * @return string
	 */
	public function getMsg(): string {
		return $this->msg;
	}
	
	/**
	 * @param string $msg
	 */
	public function setMsg(string $msg) {
		$this->msg = $msg;
	}
	
	/**
	 * @return array
	 */
	public function getData(): array {
		return $this->data;
	}
	
	/**
	 * @param array $data
	 */
	public function setData(array $data) {
		$this->data = $data;
	}
	
	/**
	 * @return int
	 */
	public function getReturnType(): int {
		return $this->return_type;
	}
	
	/**
	 * @param int $return_type
	 */
	public function setReturnType(int $return_type) {
		$this->return_type = $return_type;
	}
	
	/**
	 * @return bool
	 */
	public function isReturnDie(): bool {
		return $this->return_die;
	}
	
	/**
	 * @param bool $return_die
	 */
	public function setReturnDie(bool $return_die) {
		$this->return_die = $return_die;
	}
	
	/**
	 * @return array
	 */
	public function getLastReturn(): array {
		return $this->last_return;
	}
	
	/**
	 * @param array $last_return
	 */
	public function setLastReturn(array $last_return) {
		$this->last_return = $last_return;
	}
	
	/**
	 * @return ErrorCodeList
	 */
	public function getConfigObj(): ErrorCodeList {
		return $this->configObj;
	}
	
	/**
	 * @param ErrorCodeList $configObj
	 */
	public function setConfigObj(ErrorCodeList $configObj) {
		$this->configObj = $configObj;
	}
}