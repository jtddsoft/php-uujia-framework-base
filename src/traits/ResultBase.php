<?php


namespace uujia\framework\base\traits;


trait ResultBase{
	
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
	
	// 错误代码构造方法工厂（构造回调Func） 用时才加载
	private $_errCodeFactory = null;
	// 已构造的缓存
	private $_errCodeCache = null;
	
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
	
	/**************************************************************
	 * 返回输出
	 **************************************************************/
	
	/**
	 * 返回错误
	 *
	 * @param int $code
	 *
	 * @return array|\think\response\Json
	 */
	public function code($code = 1000) {
		$_ret         = self::$_RESULT_ERROR;
		$_ret['code'] = $code;
		
		if (empty($this->_errCodeCache)) {
			if (!empty($this->_errCodeFactory) && $this->_errCodeFactory instanceof \Closure) {
				$this->_errCodeCache = call_user_func_array($this->_errCodeFactory, [$this]);
			}
		}
		
		$_ret['msg']  = $this->_errCodeCache[$code]['error_code'] ?? '未知错误';
		
		// 记录最后的错误信息
		$this->setLastReturn($_ret);
		
		return rsErrCode($code);
	}
	
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
		
		return $_ret;
	}
	
	public function ok() {
		$_ret = self::$_RESULT_OK;
		
		// 记录最后的错误信息
		$this->setLastReturn($_ret);
		
		return self::$_RESULT_OK;
	}
	
	public function data($data = []) {
		$_ret           = self::$_RESULT_OK;
		$_ret['result'] = $data;
		
		// 记录最后的错误信息
		$this->setLastReturn($_ret);
		
		return $_ret;
	}
	
	public function return_error() {
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
		$this->last_return = array_merge($this->last_return, $last_return);
		
		$this->setCode($this->last_return['code']);
		$this->setMsg($this->last_return['msg']);
		$this->setStatus($this->last_return['status']);
		$this->setData($this->last_return['data']);
	}
	
	/**
	 * @return null|\Closure
	 */
	public function getErrCodeFactory() {
		return $this->_errCodeFactory;
	}
	
	/**
	 * @param null|\Closure $errCodeFactory
	 */
	public function setErrCodeFactory($errCodeFactory) {
		$this->_errCodeFactory = $errCodeFactory;
	}
	
	/**
	 * @return null|array
	 */
	public function getErrCodeCache() {
		return $this->_errCodeCache;
	}
	
	/**
	 * @param null|array $errCodeCache
	 */
	public function setErrCodeCache($errCodeCache) {
		$this->_errCodeCache = $errCodeCache;
	}
	
}