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
		
		$mqttConfigFile = __DIR__ . "/config/mqtt_config.php";
		if (file_exists($mqttConfigFile)) {
			$mqttConfig = include $mqttConfigFile;
			UU::C(SimpleLog::class)->getMqttObj()->config($mqttConfig['mqtt']);
			UU::C(SimpleLog::class)->setEnabledMQTT(true);
		}
	}
	
	public function test() {
		return UU::C(Base::class)->ok();
	}
}