<?php


namespace uujia\framework\base\test;


use uujia\framework\base\BaseService;
use uujia\framework\base\common\Base;
use uujia\framework\base\common\Config;
use uujia\framework\base\common\Log;
use uujia\framework\base\UU;

class Demo extends BaseService {
	
	public function __construct() {
		parent::__construct();
	}
	
	public function init() {
		parent::init();
		
		// $fileMQTTConfig = __DIR__ . '/config/mq_config.php';
		//
		// $arrMQTTConfig = [];
		// if (file_exists($fileMQTTConfig)) {
		// 	$arrMQTTConfig = include $fileMQTTConfig;
		//
		// 	/** @var $logObj Log */
		// 	$logObj = UU::C(Log::class);
		// 	$logObj->getMqttObj()->config($arrMQTTConfig['mqtt']);
		// 	$logObj->setEnabledMQTT(true);
		//
		// }
		
		/** @var $configObj Config */
		$configObj = UU::C(Config::class);
		$configObj->path(__DIR__ . '/config/error_code.php');
		
		$paths = glob(__DIR__ . "/config/*_config.php", GLOB_BRACE);
		$configObj->path($paths);
	}
	
	public function test() {
		// return glob(__DIR__ . "/../config/*_config.php", GLOB_BRACE);
		// return UU::C(Base::class)->rt()->ok();
		return UU::C(Base::class)->ok();
	}
	
	public function subscribeRabbitMQ() {
		$mq = $this->getMQCollection()->getRabbitMQObj();
		$mq->connect()
			->queue(Log::$_RABBITMQ_QUEUE)
			->exchange(Log::$_RABBITMQ_EXCHANGE)
			->routingKey(Log::$_RABBITMQ_ROUTING_KEY)
			->routingKeyBinding(Log::$_RABBITMQ_ROUTING_KEY_BINDING)
			->setCallbackSubscribe(function ($body, $envelope, $queue) {
				/** @var $envelope \AMQPEnvelope */
				/** @var $queue \AMQPQueue */
				echo $body . "\n";
			})
			->subscribe();
		
	}
}