<?php


namespace uujia\framework\base\test;


use uujia\framework\base\common\Config;

class ConfigTest {
	/** @var Config $config */
	public $config;
	
	public function __construct() {
		$this->config = new Config();
	}
	
	public function toString() {
		$l = $this->config->loadValue('app');
		var_dump($l);
	}
	
	
}

