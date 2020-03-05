<?php


namespace uujia\framework\base\test;


use uujia\framework\base\BaseService;
use uujia\framework\base\common\Base;
use uujia\framework\base\common\Config;
use uujia\framework\base\common\Event;
use uujia\framework\base\common\lib\Utils\Json;
use uujia\framework\base\common\Log;
use uujia\framework\base\traits\InstanceBase;
use uujia\framework\base\UU;

class Demo extends BaseService {
	use InstanceBase;
	
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
		$configObj->path(__DIR__ . '/config/error_code.php', '', '', 99);
		
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
			->queue(Log::RABBITMQ_QUEUE)
			->exchange(Log::RABBITMQ_EXCHANGE)
			->routingKey(Log::RABBITMQ_ROUTING_KEY)
			->routingKeyBinding(Log::RABBITMQ_ROUTING_KEY_BINDING)
			->setCallbackSubscribe(function ($body, $envelope, $queue) {
				/** @var $envelope \AMQPEnvelope */
				/** @var $queue \AMQPQueue */
				echo $body . "\n";
			})
			->subscribe();
		
	}
	
	
	public function event() {
		$event = $this->getEvent();
		$event->listen('a#*', function ($param) {
			// echo Json::je($param);
		
			return $this->getResult()->ok();
		});
		
		return $event->trigger('a', [1]);
	}
}