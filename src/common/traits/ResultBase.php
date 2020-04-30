<?php


namespace uujia\framework\base\common\traits;


use uujia\framework\base\common\consts\ResultConst;

trait ResultBase {
	
	// ResultConst
	
	// 错误代码构造方法工厂（构造回调Func） 用时才加载
	// private $_errCodeFactory = null;
	// 已构造的缓存
	// private $_errCodeCache = null;
	
	/**
	 * 缓存返回值
	 */
	private $code = 200;
	private $status = 'success';
	private $msg = '操作完成';
	public $data = [];
	
	// 缓存最后一次返回值 包括code msg。。。
	public $last_return
		= [
			'code'   => 200,
			'status' => 'success',
			'msg'    => '操作完成',
			'data'   => [],
		];
	
	// 返回列表
	public $_results = [];
	
	/**
	 * 重置返回值所有属性
	 */
	public function resetResult() {
		$this->ok();
		
		$this->cleanResults();
	}
	
	/**
	 * 分配
	 *  将最后一次返回值分配入对象
	 *
	 * @param array $lastReturn
	 * @param bool  $isCleanResults
	 *
	 * @return $this
	 */
	public function assignLastReturn($lastReturn = [], $isCleanResults = true) {
		$isCleanResults && $this->cleanResults();
		
		$this->setLastReturn($lastReturn);
		
		return $this;
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
		$_ret                           = ResultConst::RESULT_ERROR;
		$_ret[ResultConst::RESULT_CODE] = $code;
		
		if (empty($this->_errCodeCache)) {
			if (!empty($this->_errCodeFactory) && $this->_errCodeFactory instanceof \Closure) {
				$this->_errCodeCache = call_user_func_array($this->_errCodeFactory, [$this]);
			}
		}
		
		$_ret[ResultConst::RESULT_MSG] = $this->_errCodeCache[$code]['error_code'] ?? '未知错误';
		
		// 记录最后的错误信息
		$this->setLastReturn($_ret);
		
		// return rsErrCode($code);
		return $_ret;
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
		$_ret                           = ResultConst::RESULT_ERROR;
		$_ret[ResultConst::RESULT_CODE] = $code;
		$_ret[ResultConst::RESULT_MSG]  = $msg;
		
		// 记录最后的错误信息
		$this->setLastReturn($_ret);
		
		return $_ret;
	}
	
	public function ok() {
		$_ret = ResultConst::RESULT_OK;
		
		// 记录最后的错误信息
		$this->setLastReturn($_ret);
		
		return ResultConst::RESULT_OK;
	}
	
	public function data($data = []) {
		$_ret                           = ResultConst::RESULT_OK;
		$_ret[ResultConst::RESULT_DATA] = $data;
		
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
	 *
	 * @param array $ret
	 *
	 * @return bool
	 */
	public function isOk($ret = []) {
		$_ret = !empty($ret) ? $ret : $this->getLastReturn();
		
		if ($_ret[ResultConst::RESULT_CODE] == ResultConst::OK_CODE) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * 是否出错
	 *
	 * @param array $ret
	 *
	 * @return bool
	 */
	public function isErr($ret = []) {
		return !$this->isOk($ret);
	}
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
	/**
	 * 获取code
	 *
	 * @return int
	 */
	public function getCode(): int {
		return $this->code;
	}
	
	/**
	 * 设置code
	 *
	 * @param int $code
	 *
	 * @return $this
	 */
	public function setCode(int $code) {
		$this->code = $code;
		
		$this->last_return[ResultConst::RESULT_CODE] = $code;
		
		return $this;
	}
	
	/**
	 * 获取状态status
	 *
	 * @return string
	 */
	public function getStatus(): string {
		return $this->status;
	}
	
	/**
	 * 设置状态status
	 *
	 * @param string $status
	 *
	 * @return $this
	 */
	public function setStatus(string $status) {
		$this->status = $status;
		
		$this->last_return[ResultConst::RESULT_STATUS] = $status;
		
		return $this;
	}
	
	/**
	 * 获取消息msg
	 *
	 * @return string
	 */
	public function getMsg(): string {
		return $this->msg;
	}
	
	/**
	 * 设置消息msg
	 *
	 * @param string $msg
	 *
	 * @return $this
	 */
	public function setMsg(string $msg) {
		$this->msg = $msg;
		
		$this->last_return[ResultConst::RESULT_MSG] = $msg;
		
		return $this;
	}
	
	/**
	 * 获取数据data
	 *
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}
	
	/**
	 * 设置数据data
	 *
	 * @param array $data
	 *
	 * @return $this
	 */
	public function setData(array $data) {
		$this->data = $data;
		
		$this->last_return[ResultConst::RESULT_DATA] = $data;
		
		return $this;
	}
	
	/**
	 * 获取最后一次返回值
	 *
	 * @return array
	 */
	public function getLastReturn(): array {
		return $this->last_return;
	}
	
	/**
	 * 设置最后一次返回值
	 *
	 * @param array $last_return
	 *
	 * @return $this
	 */
	public function setLastReturn(array $last_return) {
		$this->last_return = array_merge($this->last_return, $last_return);
		
		$this->setCode($this->last_return[ResultConst::RESULT_CODE]);
		$this->setMsg($this->last_return[ResultConst::RESULT_MSG]);
		$this->setStatus($this->last_return[ResultConst::RESULT_STATUS]);
		$this->setData($this->last_return[ResultConst::RESULT_DATA]);
		
		// 添加到返回值列表
		$this->addResults($this->last_return);
		
		return $this;
	}
	
	// /**
	//  * @return null|\Closure
	//  */
	// public function getErrCodeFactory() {
	// 	return $this->_errCodeFactory;
	// }
	//
	// /**
	//  * @param null|\Closure $errCodeFactory
	//  * @return $this
	//  */
	// public function setErrCodeFactory($errCodeFactory) {
	// 	$this->_errCodeFactory = $errCodeFactory;
	//
	// 	return $this;
	// }
	//
	// /**
	//  * @return null|array
	//  */
	// public function getErrCodeCache() {
	// 	return $this->_errCodeCache;
	// }
	//
	// /**
	//  * @param null|array $errCodeCache
	//  * @return $this
	//  */
	// public function setErrCodeCache($errCodeCache) {
	// 	$this->_errCodeCache = $errCodeCache;
	//
	// 	return $this;
	// }
	
	/**
	 * @return array
	 */
	public function getResults() {
		return $this->_results;
	}
	
	/**
	 * @param array $results
	 *
	 * @return $this
	 */
	public function _setResults(array $results) {
		$this->_results = $results;
		
		return $this;
	}
	
	/**
	 * @return $this
	 */
	public function cleanResults() {
		$this->_results = [];
		
		return $this;
	}
	
	/**
	 * @param array $result
	 *
	 * @return $this
	 */
	public function addResults($result) {
		$this->_results[] = $result;
		
		return $this;
	}
	
}