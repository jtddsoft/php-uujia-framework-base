<?php


namespace uujia\framework\base\test;


use uujia\framework\base\BaseService;
use uujia\framework\base\common\Base;
use uujia\framework\base\common\SimpleLog;
use uujia\framework\base\common\SimpleMQTT;
use uujia\framework\base\UU;

class Demo extends BaseService {
	
	public function __construct() {
		parent::__construct();
	}
	
	public function init() {
		parent::init();
		
		$fileMQTTConfig = __DIR__ . '/config/mqtt_config.php';
		
		$arrMQTTConfig = [];
		if (file_exists($fileMQTTConfig)) {
			$arrMQTTConfig = include $fileMQTTConfig;
			
			/** @var $logObj SimpleLog */
			$logObj = UU::C(SimpleLog::class);
			$logObj->getMqttObj()->config($arrMQTTConfig['mqtt']);
			$logObj->setEnabledMQTT(true);
			
		}
	}
	
	public function test() {
		return UU::C(Base::class)->ok();
	}
}