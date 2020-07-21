<?php

namespace uujia\framework\base\common\lib\Exception;

use Throwable;
use uujia\framework\base\common\lib\Container\Container;
use uujia\framework\base\common\lib\Log\Logger;
use uujia\framework\base\common\lib\Utils\Ret;
use uujia\framework\base\common\traits\ResultTrait;

/**
 * Class ExceptionBase
 *
 * @package uujia\framework\base\common\lib\Exception
 */
class ExceptionBase extends \Exception {
	
	/**
	 * @var Logger
	 */
	protected $_loggerObj;
	
	/**
	 * ExceptionBase constructor.
	 *
	 * @param string         $message
	 * @param int            $code
	 * @param Throwable|null $previous
	 */
	public function __construct($message = "", $code = 0, Throwable $previous = null) {
		$this->_loggerObj = Container::getInstance()->get(Logger::class);
		
		$this->initialize($message, $code);
		
		parent::__construct($message, $code, $previous);
	}
	
	// 初始化
	protected function initialize($message = "", $code = 0) {
		if (!empty($this->getLoggerObj())) {
			$this->getLoggerObj()->errorEx(Ret::getInstance()->error($message, $code));
		}
	}
	
	/**
	 * @return Logger
	 */
	public function getLoggerObj() {
		return $this->_loggerObj;
	}
	
	/**
	 * @param Logger $loggerObj
	 *
	 * @return $this
	 */
	public function _setLoggerObj($loggerObj) {
		$this->_loggerObj = $loggerObj;
		
		return $this;
	}
	
}