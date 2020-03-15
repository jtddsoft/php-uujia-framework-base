<?php


namespace uujia\framework\base\common;


use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use uujia\framework\base\common\lib\MQ\MQTT;
use uujia\framework\base\common\lib\MQ\RabbitMQ;
use uujia\framework\base\common\lib\Utils\Json;
use uujia\framework\base\traits\NameBase;

class Log implements LoggerInterface {
	use NameBase;
	use LoggerTrait;
	
	const MQTT_CLIENT_ID   = 'Logger_2019';
	const MQTT_TOPICS      = 'Logger_2019';
	const MQTT_TOPICS_LIST = 'Logger_2019_List';
	
	const RABBITMQ_QUEUE                    = 'Logger_2019.one';
	const RABBITMQ_EXCHANGE                 = 'amq.topic';
	const RABBITMQ_ROUTING_KEY              = 'Logger_2019.one';
	const RABBITMQ_ROUTING_KEY_BINDING      = 'Logger_2019.one';
	const RABBITMQ_QUEUE_LIST               = 'Logger_2019.list';
	const RABBITMQ_EXCHANGE_LIST            = 'amq.topic';
	const RABBITMQ_ROUTING_KEY_LIST         = 'Logger_2019.list';
	const RABBITMQ_ROUTING_KEY_BINDING_LIST = 'Logger_2019.list';
	
	const LOG_CONFIG_NAME = 'log_config';
	const LOG_CONFIG_KEY_MQ = [
		'enabled_response' => 'enabled_response',
	];
	
	const LOG_CONFIG_KEY_MQTT = [
		'enabled' => 'enabled',
		'client_id' => 'client_id',
		'topics' => 'topics',
		
		'topics_list' => 'topics_list',
	];
	
	const LOG_CONFIG_KEY_RABBITMQ = [
		'enabled' => 'enabled',
		
		'queue' => 'queue',
		'exchange' => 'exchange',
		'routing_key' => 'routing_key',
		'routing_key_binding' => 'routing_key_binding',
		
		'queue_list' => 'queue_list',
		'exchange_list' => 'exchange_list',
		'routing_key_list' => 'routing_key_list',
		'routing_key_binding_list' => 'routing_key_binding_list',
	];
	
	const MQ_KEY = [
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
	 * @return $this
	 */
	public function init() {
		$this->initNameInfo();
		
		$_enabledResponse = $this->getConfigMQ(self::LOG_CONFIG_KEY_MQ['enabled_response']) ?? false;
		$_enabledMQTT = $this->getConfigMQTT(self::LOG_CONFIG_KEY_MQTT['enabled']) ?? false;
		$_enabledRabbitMQ = $this->getConfigRabbitMQ(self::LOG_CONFIG_KEY_RABBITMQ['enabled']) ?? false;
		
		$this->setEnabledResponse($_enabledResponse);
		$this->setEnabledMQTT($_enabledMQTT);
		$this->setEnabledRabbitMQ($_enabledRabbitMQ);
		
		return $this;
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = self::class;
		$this->name_info['intro'] = '日志管理';
	}
	
	/**
	 * 用上下文信息替换记录信息中的占位符
	 *
	 * @param       $message
	 * @param array $context
	 * @return string
	 */
	function interpolate($message, $context = []) {
		// 构建一个花括号包含的键名的替换数组
		$replace = array();
		foreach ($context as $key => $val) {
			// 检查该值是否可以转换为字符串
			if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
				$replace['{' . $key . '}'] = $val;
			}
		}
		
		// 替换记录信息中的占位符，最后返回修改后的记录信息。
		return strtr($message, $replace);
	}
	
	/**************************************************
	 * PSR-3 LoggerInterface 方法实现
	 **************************************************/
	
	/**
	 * @inheritDoc
	 */
	public function log($level, $message, array $context = array()) {
		$this->recordEx($message, $context, strtoupper($level));
	}
	
	/**************************************************
	 * Function
	 **************************************************/
	
	/**
	 * RecordEx
	 *
	 * @param string|array $msg
	 * @param array        $context
	 * @param string       $tag
	 * @return Log
	 */
	public function recordEx($msg, array $context = [], $tag = 'INFO') {
		if (is_array($msg)) {
			$msg = Json::je($msg);
		}
		
		$_text = $msg;
		if (empty($msg)) {
			$_time = date('Y-m-d H:i:s');
			$_text = "[{$tag}] [{$_time}] {$msg}";
		}
		
		$_logText = $this->interpolate($msg, $context);
		
		$this->setLog($_logText);
		
		$list = [
			'type' => 'json',
			'logs' => $this->getLog(),
		];
		
		// $this->printMQ($this->log);
		$this->printMQ($list);
		
		return $this;
	}
	
	/**
	 * DebugEx
	 *
	 * @param string|array $msg
	 * @param array        $context
	 * @return Log
	 */
	public function debugEx($msg, array $context = []) {
		return $this->recordEx($msg, $context, 'DEBUG');
	}
	
	/**
	 * InfoEx
	 *
	 * @param string|array $msg
	 * @param array        $context
	 * @return Log
	 */
	public function infoEx($msg, array $context = []) {
		return $this->recordEx($msg, $context, 'INFO');
	}
	
	/**
	 * ErrorEx
	 *
	 * @param string|array $msg
	 * @param array        $context
	 * @return Log
	 */
	public function errorEx($msg, array $context = []) {
		return $this->recordEx($msg, $context, 'ERROR');
	}
	
	/**
	 * 响应
	 *
	 * @param array $logs
	 * @return Log
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
		
		return $this;
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
		
		$_clientID = $this->getConfigMQTT(self::LOG_CONFIG_KEY_MQTT['client_id']) ?? self::MQTT_CLIENT_ID;
		
		$this->getMqttObj()->clientId($_clientID); // self::$_MQTT_CLIENT_ID
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
		
		$this->getRabbitMQObj()->connect();
		
		// $this->flagRabbitMQConnected(true);
		
		return $this;
	}
	
	/**
	 * 打印信息到MQ
	 *
	 * @param $text
	 * @return bool
	 */
	public function printMQ($text) {
		// MQTT
		if ($this->isEnabledMQTT()) {
			$this->printMQTT($text);
		}
		
		// RabbitMQ
		if ($this->isEnabledRabbitMQ()) {
			$this->printRabbitMQ($text);
		}
		
		return true;
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
		
		$_topics = $this->getConfigMQTT(self::LOG_CONFIG_KEY_MQTT['topics']) ?? self::MQTT_TOPICS;
		
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
		
		$_queue = $this->getConfigRabbitMQ(self::LOG_CONFIG_KEY_RABBITMQ['queue']) ?? self::RABBITMQ_QUEUE;
		$_exchange = $this->getConfigRabbitMQ(self::LOG_CONFIG_KEY_RABBITMQ['exchange']) ?? self::RABBITMQ_EXCHANGE;
		$_routingKey = $this->getConfigRabbitMQ(self::LOG_CONFIG_KEY_RABBITMQ['routing_key']) ?? self::RABBITMQ_ROUTING_KEY;
		$_routingKeyBinding = $this->getConfigRabbitMQ(self::LOG_CONFIG_KEY_RABBITMQ['routing_key_binding']) ?? self::RABBITMQ_ROUTING_KEY_BINDING;
		
		$this->getRabbitMQObj()->queue($_queue);
		$this->getRabbitMQObj()->exchange($_exchange);
		$this->getRabbitMQObj()->routingKey($_routingKey);
		$this->getRabbitMQObj()->routingKeyBinding($_routingKeyBinding);
		
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
		if ($this->isEnabledMQTT()) {
			$this->printMQTTResponse($list);
		}
		
		// RabbitMQ
		if ($this->isEnabledRabbitMQ()) {
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
		
		$_topicsList = $this->getConfigMQTT(self::LOG_CONFIG_KEY_MQTT['topics_list']) ?? self::MQTT_TOPICS_LIST;
		
		$this->getMqttObj()->topics($_topicsList);
		
		$this->getMqttObj()->publish(Json::je($list));
		
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
		
		$_queueList = $this->getConfigRabbitMQ(self::LOG_CONFIG_KEY_RABBITMQ['queue_list']) ?? self::RABBITMQ_QUEUE_LIST;
		$_exchangeList = $this->getConfigRabbitMQ(self::LOG_CONFIG_KEY_RABBITMQ['exchange_list']) ?? self::RABBITMQ_EXCHANGE_LIST;
		$_routingKeyList = $this->getConfigRabbitMQ(self::LOG_CONFIG_KEY_RABBITMQ['routing_key_list']) ?? self::RABBITMQ_ROUTING_KEY_LIST;
		$_routingKeyBindingList = $this->getConfigRabbitMQ(self::LOG_CONFIG_KEY_RABBITMQ['routing_key_binding_list']) ?? self::RABBITMQ_ROUTING_KEY_BINDING_LIST;
		
		$this->getRabbitMQObj()->queue($_queueList);
		$this->getRabbitMQObj()->exchange($_exchangeList);
		$this->getRabbitMQObj()->routingKey($_routingKeyList);
		$this->getRabbitMQObj()->routingKeyBinding($_routingKeyBindingList);
		
		$this->getRabbitMQObj()->publish(Json::je($list));
		
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
		return $this->getConfigObj()->loadValue('', self::LOG_CONFIG_NAME);
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
			return $config[self::MQ_KEY['mq']] ?? null;
		}
		
		return $config[self::MQ_KEY['mq']][$name] ?? null;
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
			return $config[self::MQ_KEY['mqtt']] ?? null;
		}
		
		return $config[self::MQ_KEY['mqtt']][$name] ?? null;
	}
	
	/**
	 * 获取RabbitMQ配置值
	 *
	 * @param string $name
	 * @return array|null
	 */
	public function getConfigRabbitMQ($name = '') {
		$config = $this->getConfigValue();
		if (empty($config)) {
			return null;
		}
		
		if (empty($name)) {
			return $config[self::MQ_KEY['rabbitmq']] ?? null;
		}
		
		return $config[self::MQ_KEY['rabbitmq']][$name] ?? null;
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