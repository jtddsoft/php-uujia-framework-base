<?php


namespace uujia\framework\base\common;


use uujia\framework\base\traits\NameBase;

class SimpleLog {
	use NameBase;
	
	public static $_MQTT_CLIENT_ID = 'Logger_2019';
	public static $_MQTT_TOPICS = 'Logger_2019';
	public static $_MQTT_TOPICS_LIST = 'Logger_2019_List';
	
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
	
	/** @var $mqttObj SimpleMQTT */
	protected $mqttObj;
	protected $_enabledMQTT = false;
	protected $_enabledMQTTList = false;
	
	// MQTT是否连接
	protected $_flagMQTTConnected = false;
	
	
	/**
	 * AbstractLog constructor.
	 *
	 * @param SimpleMQTT $mqttObj     MQTT对象依赖
	 * @param bool       $enabledMQTT 是否启用MQTT实时输出
	 */
	public function __construct(SimpleMQTT $mqttObj, $enabledMQTT = false) {
		$this->log = '';
		$this->logs = [];
	
		$this->mqttObj = $mqttObj;
		$this->_enabledMQTT = $enabledMQTT;
		$this->_flagMQTTConnected = false;
		
		$this->init();
	}
	
	/**
	 * 初始化
	 */
	public function init() {
		$this->initNameInfo();
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
	 * @param $list
	 */
	public function response($logs) {
		if (empty($logs)) {
			$logs = $this->logs;
		}
		
		$list = [
			'type' => 'response',
			'logs' => $logs,
		];
		
		$this->printMQTTList($list);
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
		
		$this->getMqttObj()->client_id(self::$_MQTT_CLIENT_ID);
		$this->getMqttObj()->connect();
		
		$this->flagMQTTConnected(true);
		
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
		
		$this->getMqttObj()->topics(self::$_MQTT_TOPICS);
		$this->getMqttObj()->publish($text);
		
		return true;
	}
	
	/**
	 * 打印信息到MQTT
	 *
	 * @param $list
	 *
	 * @return bool
	 */
	public function printMQTTList($list) {
		// 出错默认会直接exit 如果配置了禁用die就要判断是否出错了
		if ($this->getMqttObj()->isErr()) {
			return false;
		}
		
		if (!$this->isEnabledMQTTList()) {
			return true;
		}
		
		if (!$this->isFlagMQTTConnected()) {
			$this->connectMQTT();
		}
		
		$this->getMqttObj()->topics(self::$_MQTT_TOPICS_LIST);
		$this->getMqttObj()->publish($list);
		
		return true;
	}
	
	/**
	 * enabledMQTT
	 * get set
	 *
	 * @param string|null $enabledMQTT
	 *
	 * @return bool|SimpleLog
	 */
	public function enabledMQTT($enabledMQTT = null) {
		if ($enabledMQTT === null) {
			return $this->_enabledMQTT;
		} else {
			$this->_enabledMQTT = $enabledMQTT;
		}
		
		return $this;
	}
	
	/**
	 * enabledMQTTList
	 * get set
	 *
	 * @param string|null $enabledMQTTList
	 *
	 * @return bool|SimpleLog
	 */
	public function enabledMQTTList($enabledMQTTList = null) {
		if ($enabledMQTTList === null) {
			return $this->_enabledMQTTList;
		} else {
			$this->_enabledMQTTList = $enabledMQTTList;
		}
		
		return $this;
	}
	
	/**
	 * flagMQTTConnected
	 * get set
	 *
	 * @param string|null $flagMQTTConnected
	 *
	 * @return bool|SimpleLog
	 */
	public function flagMQTTConnected($flagMQTTConnected = null) {
		if ($flagMQTTConnected === null) {
			return $this->_flagMQTTConnected;
		} else {
			$this->_flagMQTTConnected = $flagMQTTConnected;
		}
		
		return $this;
	}
	
	/**
	 * @return SimpleMQTT
	 */
	public function getMqttObj(): SimpleMQTT {
		return $this->mqttObj;
	}
	
	/**
	 * @return bool
	 */
	public function isEnabledMQTT(): bool {
		return $this->_enabledMQTT;
	}
	
	/**
	 * @param bool $enabledMQTT
	 */
	public function setEnabledMQTT(bool $enabledMQTT) {
		$this->_enabledMQTT = $enabledMQTT;
	}
	
	/**
	 * @return bool
	 */
	public function isEnabledMQTTList(): bool {
		return $this->_enabledMQTTList;
	}
	
	/**
	 * @param bool $enabledMQTTList
	 */
	public function setEnabledMQTTList(bool $enabledMQTTList) {
		$this->_enabledMQTTList = $enabledMQTTList;
	}
	
	/**
	 * @return bool
	 */
	public function isFlagMQTTConnected(): bool {
		return $this->_flagMQTTConnected;
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
	
}