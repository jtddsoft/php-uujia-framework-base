<?php


namespace uujia\framework\base\common;


use uujia\framework\base\common\lib\MQ\MQTT;
use uujia\framework\base\common\lib\MQ\RabbitMQ;
use uujia\framework\base\traits\NameBase;

class Log {
	use NameBase;
	
	public static $_MQTT_CLIENT_ID = 'Logger_2019';
	public static $_MQTT_TOPICS = 'Logger_2019';
	public static $_MQTT_TOPICS_LIST = 'Logger_2019_List';
	
	public static $_RABBITMQ_QUEUE = 'Logger_2019';
	public static $_RABBITMQ_EXCHANGE = 'Logger_2019';
	public static $_RABBITMQ_ROUTING_KEY = 'Logger_2019';
	public static $_RABBITMQ_QUEUE_LIST = 'Logger_2019_List';
	public static $_RABBITMQ_EXCHANGE_LIST = 'Logger_2019_List';
	public static $_RABBITMQ_ROUTING_KEY_LIST = 'Logger_2019_List';
	
	public static $_LOG_CONFIG_NAME = 'log_config';
	public static $_LOG_CONFIG_KEY_MQ = [
		'enabled_response' => 'enabled_response',
	];
	
	public static $_LOG_CONFIG_KEY_MQTT = [
		'enabled' => 'enabled',
		'client_id' => 'client_id',
		'topics' => 'topics',
		
		'topics_list' => 'topics_list',
	];
	
	public static $_LOG_CONFIG_KEY_RABBITMQ = [
		'enabled' => 'enabled',
		'queue' => 'queue',
		'exchange' => 'exchange',
		'routing_key' => 'routing_key',
		
		'queue_list' => 'queue_list',
		'exchange_list' => 'exchange_list',
		'routing_key_list' => 'routing_key_list',
	];
	
	public static $_MQ_KEY = [
		'mq' => 'MQ',
		'mqtt' => 'MQTT',
		'rabbitmq' => 'RabbitMQ',
	];
	
	protected $log = '';
	protected $logs = [];
	
	// protected $log_info = [
	// 	'html' => '',
	// 	'text' => '',
	// ];
	// protected $logs_info = [
	// 	'header' => '',
	// 	'logs' => [], // log_info的集合
	// ];
	
	/** @var Config $_configObj */
	protected $_configObj;
	
	/** @var $mqObj MQCollection */
	protected $_mqObj;
	
	// protected $_enabledMQTT = false;
	protected $_enabledResponse = false;
	
	// MQTT是否连接
	// protected $_flagMQTTConnected = false;
	
	
	/**
	 * AbstractLog constructor.
	 *
	 * @param Config       $configObj
	 * @param MQCollection $mqObj MQCollection对象依赖
	 */
	public function __construct(Config $configObj, MQCollection $mqObj) {
		$this->log = '';
		$this->logs = [];
		
		$this->_configObj = $configObj;
		$this->_mqObj = $mqObj;
		// $this->_enabledMQTT = $enabledMQTT;
		// $this->_flagMQTTConnected = false;
		
		$this->init();
	}
	
	/**
	 * 初始化
	 */
	public function init() {
		$this->initNameInfo();
		
		$_enabledResponse = $this->getConfigMQ(self::$_LOG_CONFIG_KEY_MQ['enabled_response']) ?? false;
		$_enabledMQTT = $this->getConfigMQTT(self::$_LOG_CONFIG_KEY_MQTT['enabled']) ?? false;
		$_enabledRabbitMQ = $this->getConfigMQTT(self::$_LOG_CONFIG_KEY_RABBITMQ['enabled']) ?? false;
		
		$this->setEnabledResponse($_enabledResponse);
		$this->setEnabledMQTT($_enabledMQTT);
		$this->setEnabledRabbitMQ($_enabledRabbitMQ);
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = self::class;
		$this->name_info['intro'] = '日志管理';
	}
	
	/**
	 * Debug
	 * @param $text
	 */
	public function debug($text) {
		if (is_array($text)) {
			$text = Result::je($text);
		}
		
		$_time = date('Y-m-d H:i:s');
		
		$this->setLog("[DEBUG] [{$_time}] {$text}");
		
		$this->printMQTT($this->log);
	}
	
	/**
	 * Record
	 * @param string|array $text
	 */
	public function record($text, $tag = 'INFO') {
		if (is_array($text)) {
			$text = Result::je($text);
		}
		
		$_time = date('Y-m-d H:i:s');
		
		$this->setLog("[{$tag}] [{$_time}] {$text}");
		
		$this->printMQTT($this->log);
	}
	
	/**
	 * info
	 * @param string|array $text
	 */
	public function info($text) {
		$this->record($text, 'INFO');
	}
	
	/**
	 * Error
	 * @param $text
	 */
	public function error($text) {
		if (is_array($text)) {
			$text = Result::je($text);
		}
		
		$_time = date('Y-m-d H:i:s');
		
		$this->setLog("[ERROR] [{$_time}] {$text}");
		
		$this->printMQTT($this->log);
	}
	
	/**
	 * 响应
	 *
	 * @param array $logs
	 */
	public function response($logs = []) {
		if (empty($logs)) {
			$logs = $this->logs;
		}
		
		$list = [
			'type' => 'response',
			'logs' => $logs,
		];
		
		$this->printResponse($list);
	}
	
	/**
	 * 连接MQTT
	 *  注意需要先配置MQTT初始化参数
	 *
	 * @return $this|array|\think\response\Json
	 */
	public function connectMQTT() {
		// 出错默认会直接exit 如果配置了禁用die就要判断是否出错了
		if ($this->getMqttObj()->isErr()) {
			return $this;
		}
		
		$_clientID = $this->getConfigMQTT(self::$_LOG_CONFIG_KEY_MQTT['client_id']) ?? self::$_MQTT_CLIENT_ID;
		
		$this->getMqttObj()->client_id($_clientID); // self::$_MQTT_CLIENT_ID
		$this->getMqttObj()->connect();
		
		// $this->flagMQTTConnected(true);
		
		return $this;
	}
	
	/**
	 * 连接RabbitMQ
	 *  注意需要先配置RabbitMQ初始化参数
	 *
	 * @return $this|array|\think\response\Json
	 */
	public function connectRabbitMQ() {
		// 出错默认会直接exit 如果配置了禁用die就要判断是否出错了
		if ($this->getRabbitMQObj()->isErr()) {
			return $this;
		}
		
		$_queue = $this->getConfigRabbitMQ(self::$_LOG_CONFIG_KEY_RABBITMQ['queue']) ?? self::$_RABBITMQ_QUEUE;
		
		$this->getRabbitMQObj()->queue($_queue);
		$this->getRabbitMQObj()->connect();
		
		// $this->flagRabbitMQConnected(true);
		
		return $this;
	}
	
	/**
	 * 打印信息到MQTT
	 *
	 * @param $text
	 *
	 * @return bool
	 */
	public function printMQTT($text) {
		// 出错默认会直接exit 如果配置了禁用die就要判断是否出错了
		if ($this->getMqttObj()->isErr()) {
			return false;
		}
		
		if (!$this->isEnabledMQTT()) {
			return true;
		}
		
		if (!$this->isFlagMQTTConnected()) {
			$this->connectMQTT();
		}
		
		$_topics = $this->getConfigMQTT(self::$_LOG_CONFIG_KEY_MQTT['topics']) ?? self::$_MQTT_TOPICS;
		
		$this->getMqttObj()->topics($_topics);
		$this->getMqttObj()->publish($text);
		
		return true;
	}
	
	/**
	 * 打印信息到RabbitMQ
	 *
	 * @param $text
	 *
	 * @return bool
	 */
	public function printRabbitMQ($text) {
		// 出错默认会直接exit 如果配置了禁用die就要判断是否出错了
		if ($this->getRabbitMQObj()->isErr()) {
			return false;
		}
		
		if (!$this->isEnabledRabbitMQ()) {
			return true;
		}
		
		if (!$this->isFlagRabbitMQConnected()) {
			$this->connectRabbitMQ();
		}
		
		$_exchange = $this->getConfigRabbitMQ(self::$_LOG_CONFIG_KEY_RABBITMQ['exchange']) ?? self::$_RABBITMQ_EXCHANGE;
		$_routingKey = $this->getConfigRabbitMQ(self::$_LOG_CONFIG_KEY_RABBITMQ['routing_key']) ?? self::$_RABBITMQ_ROUTING_KEY;
		
		$this->getRabbitMQObj()->exchange($_exchange);
		$this->getRabbitMQObj()->routing_key($_routingKey);
		$this->getRabbitMQObj()->publish($text);
		
		return true;
	}
	
	/**
	 * 打印返回响应信息
	 *
	 * @param $list
	 * @return bool
	 */
	public function printResponse($list) {
		// 是否启用
		if (!$this->isEnabledResponse()) {
			return true;
		}
		
		// MQTT
		if (!$this->isEnabledMQTT()) {
			$this->printMQTTResponse($list);
		}
		
		// RabbitMQ
		if (!$this->isEnabledRabbitMQ()) {
			$this->printRabbitMQResponse($list);
		}
		
		return true;
	}
	
	/**
	 * 打印信息到MQTT
	 *
	 * @param $list
	 *
	 * @return bool
	 */
	public function printMQTTResponse($list) {
		// 出错默认会直接exit 如果配置了禁用die就要判断是否出错了
		if ($this->getMqttObj()->isErr()) {
			return false;
		}
		
		if (!$this->isEnabledResponse()) {
			return true;
		}
		
		if (!$this->isFlagMQTTConnected()) {
			$this->connectMQTT();
		}
		
		$_topics_list = $this->getConfigMQTT(self::$_LOG_CONFIG_KEY_MQTT['topics_list']) ?? self::$_MQTT_TOPICS_LIST;
		
		$this->getMqttObj()->topics($_topics_list);
		$this->getMqttObj()->publish($list);
		
		return true;
	}
	
	/**
	 * 打印信息到RabbitMQ
	 *
	 * @param $list
	 *
	 * @return bool
	 */
	public function printRabbitMQResponse($list) {
		// 出错默认会直接exit 如果配置了禁用die就要判断是否出错了
		if ($this->getRabbitMQObj()->isErr()) {
			return false;
		}
		
		if (!$this->isEnabledResponse()) {
			return true;
		}
		
		if (!$this->isFlagRabbitMQConnected()) {
			$this->connectRabbitMQ();
		}
		
		$_exchange_list = $this->getConfigRabbitMQ(self::$_LOG_CONFIG_KEY_RABBITMQ['exchange_list']) ?? self::$_RABBITMQ_EXCHANGE_LIST;
		$_routingKey_list = $this->getConfigRabbitMQ(self::$_LOG_CONFIG_KEY_RABBITMQ['routing_key_list']) ?? self::$_RABBITMQ_ROUTING_KEY_LIST;
		
		$this->getRabbitMQObj()->exchange($_exchange_list);
		$this->getRabbitMQObj()->routing_key($_routingKey_list);
		$this->getRabbitMQObj()->publish($list);
		
		return true;
	}
	
	/**
	 * enabledMQTT
	 * get set
	 *
	 * @param bool|null $enabled
	 *
	 * @return bool|Log
	 */
	public function enabledMQTT($enabled = null) {
		if ($enabled === null) {
			return $this->isEnabledMQTT();
		} else {
			$this->setEnabledMQTT($enabled);
		}
		
		return $this;
	}
	
	/**
	 * enabledMQTTList
	 * get set
	 *
	 * @param bool|null $enabled
	 *
	 * @return bool|Log
	 */
	public function enabledResponse($enabled = null) {
		if ($enabled === null) {
			return $this->isEnabledResponse();
		} else {
			$this->setEnabledResponse($enabled);
		}
		
		return $this;
	}
	
	// /**
	//  * flagMQTTConnected
	//  * get set
	//  *
	//  * @param string|null $flagMQTTConnected
	//  *
	//  * @return bool|Log
	//  */
	// public function flagMQTTConnected($flagMQTTConnected = null) {
	// 	if ($flagMQTTConnected === null) {
	// 		return $this->_flagMQTTConnected;
	// 	} else {
	// 		$this->_flagMQTTConnected = $flagMQTTConnected;
	// 	}
	//
	// 	return $this;
	// }
	
	/**
	 * @return MQTT
	 */
	public function getMqttObj(): MQTT {
		return $this->getMqObj()->getMqttObj();
	}
	
	/**
	 * @return RabbitMQ
	 */
	public function getRabbitMQObj(): RabbitMQ {
		return $this->getMqObj()->getRabbitMQObj();
	}
	
	/**
	 * @return bool
	 */
	public function isEnabledMQTT(): bool {
		return $this->getMqObj()->isMQTTEnabled();
	}
	
	/**
	 * @param bool $enabled
	 */
	public function setEnabledMQTT(bool $enabled) {
		$this->getMqObj()->setMQTTEnabled($enabled);
	}
	
	/**
	 * @return bool
	 */
	public function isEnabledRabbitMQ(): bool {
		return $this->getMqObj()->isRabbitMQEnabled();
	}
	
	/**
	 * @param bool $enabled
	 */
	public function setEnabledRabbitMQ(bool $enabled) {
		$this->getMqObj()->setRabbitMQEnabled($enabled);
	}
	
	/**
	 * @return bool
	 */
	public function isEnabledResponse(): bool {
		return $this->_enabledResponse;
	}
	
	/**
	 * @param bool $enabledResponse
	 */
	public function setEnabledResponse(bool $enabledResponse) {
		$this->_enabledResponse = $enabledResponse;
	}
	
	/**
	 * @return bool
	 */
	public function isFlagMQTTConnected(): bool {
		return $this->getMqObj()->isMQTTConnected();
	}
	
	/**
	 * @return bool
	 */
	public function isFlagRabbitMQConnected(): bool {
		return $this->getMqObj()->isRabbitMQConnected();
	}
	
	/**
	 * @return string
	 */
	public function getLog(): string {
		return $this->log;
	}
	
	/**
	 * @param string $log
	 */
	public function setLog(string $log) {
		$this->log = $log;
		$this->addLogs($log);
	}
	
	/**
	 * @return array
	 */
	public function getLogs(): array {
		return $this->logs;
	}
	
	/**
	 * @param array $logs
	 */
	public function setLogs(array $logs) {
		$this->logs = $logs;
	}
	
	/**
	 * @param array $logs
	 */
	public function addLogs(string $log) {
		$this->logs[] = $log;
	}
	
	/**
	 * @return Config
	 */
	public function getConfigObj(): Config {
		return $this->_configObj;
	}
	
	/**
	 * @param Config $configObj
	 */
	public function _setConfigObj(Config $configObj) {
		$this->_configObj = $configObj;
	}
	
	/**
	 * 获取配置值
	 *
	 * @return array|string|int|null
	 */
	public function getConfigValue() {
		return $this->getConfigObj()->loadValue('', self::$_LOG_CONFIG_NAME);
	}
	
	/**
	 * 获取MQ配置值
	 *
	 * @param string $name
	 * @return array|null
	 */
	public function getConfigMQ($name = '') {
		$config = $this->getConfigValue();
		if (empty($config)) {
			return null;
		}
		
		if (empty($name)) {
			return $config[self::$_MQ_KEY['mq']] ?? null;
		}
		
		return $config[self::$_MQ_KEY['mq']][$name] ?? null;
	}
	
	/**
	 * 获取MQTT配置值
	 *
	 * @param string $name
	 * @return array|null
	 */
	public function getConfigMQTT($name = '') {
		$config = $this->getConfigValue();
		if (empty($config)) {
			return null;
		}
		
		if (empty($name)) {
			return $config[self::$_MQ_KEY['mqtt']] ?? null;
		}
		
		return $config[self::$_MQ_KEY['mqtt']][$name] ?? null;
	}
	
	/**
	 * 获取RabbitMQ配置值
	 *
	 * @return array|null
	 */
	public function getConfigRabbitMQ($name = '') {
		$config = $this->getConfigValue();
		if (empty($config)) {
			return null;
		}
		
		if (empty($name)) {
			return $config[self::$_MQ_KEY['rabbitmq']] ?? null;
		}
		
		return $config[self::$_MQ_KEY['rabbitmq']][$name] ?? null;
	}
	
	/**
	 * @return MQCollection
	 */
	public function getMqObj(): MQCollection {
		return $this->_mqObj;
	}
	
	/**
	 * @param MQCollection $mqObj
	 */
	public function _setMqObj(MQCollection $mqObj) {
		$this->_mqObj = $mqObj;
	}
	
	
}