<?php

namespace uujia\framework\base\common\lib\Exception;

use Throwable;

/**
 * Class ExceptionCache
 *
 * @package uujia\framework\base\common\lib\Exception
 */
class ExceptionCache extends ExceptionBase {
	
	/**
	 * ExceptionCache constructor.
	 *
	 * @param string         $message
	 * @param int            $code
	 * @param Throwable|null $previous
	 */
	public function __construct($message = "", $code = 0, Throwable $previous = null) {
		parent::__construct($message, $code, $previous);
	}
	
	
	
	
}