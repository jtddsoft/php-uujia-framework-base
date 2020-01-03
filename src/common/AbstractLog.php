<?php


namespace uujia\framework\base\common;


abstract class AbstractLog {
	protected $log = '';
	protected $logs = [];
	
	protected $flagMQTT = false;
	
	public function __construct($flagMQTT = false) {
		$this->log = '';
		$this->logs = [];
		
		$this->flagMQTT = $flagMQTT;
	}
	
	/**
	 * Debug
	 * @param $text
	 */
	public function debug($text) {
		$_time = date('Y-m-d H:i:s');
		
		$this->log = "[DEBUG] [{$_time}] {$text}";
		$this->logs[] = $this->log;
	}
	
	/**
	 * Record
	 * @param $text
	 */
	public function record($text) {
		$_time = date('Y-m-d H:i:s');
		
		$this->log = "[INFO] [{$_time}] {$text}";
		$this->logs[] = $this->log;
	}
	
	/**
	 * Error
	 * @param $text
	 */
	public function error($text) {
		$_time = date('Y-m-d H:i:s');
		
		$this->log = "[ERROR] [{$_time}] {$text}";
		$this->logs[] = $this->log;
	}
	
}