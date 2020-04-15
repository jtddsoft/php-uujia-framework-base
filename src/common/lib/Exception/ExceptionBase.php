<?php

namespace uujia\framework\base\common\lib\Exception;

use Throwable;
use uujia\framework\base\common\lib\Log\Logger;

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
	 * @param Logger|null    $loggerObj
	 */
	public function __construct($message = "", $code = 0, Throwable $previous = null, Logger $loggerObj = null) {
		$this->_loggerObj = $loggerObj;
		
		parent::__construct($message, $code, $previous);
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