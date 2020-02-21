<?php


namespace uujia\framework\base\common;

use uujia\framework\base\common\lib\FactoryCacheTree;
use uujia\framework\base\common\lib\MQ\MQTT;
use uujia\framework\base\common\lib\MQ\RabbitMQ;
use uujia\framework\base\common\lib\MQ\RabbitMQExt;

/**
 * Class MQCollection
 * MQ的集合
 *  （仅做对象汇总 并不能完全统一方法）
 *
 * @package uujia\framework\base\common\lib
 */
class MQCollection extends FactoryCacheTree {
	
	public static $_MQ_KEY = [
		'mqtt' => 'MQTT',
		'rabbitmq' => 'RabbitMQ',
	];
	
	public static $_MQ_CONFIG_NAME = 'mq_config';
	
	// 配置对象
	protected $_configObj;
	
	/**
	 * ItemKeys constructor.
	 *
	 * @param Config $configObj
	 * @param        $parent
	 */
	public function __construct(Config $configObj, $parent = null) {
		parent::__construct($parent);
		
		$this->_configObj = $configObj;
	}
	
	/**
	 * 初始化
	 */
	public function init() {
		parent::init();
		
		// 先清空
		$this->clear();
		
		// 添加MQTT
		$this->setKeyItemData(self::$_MQ_KEY['mqtt'], function ($data, $it) {
			$_config_list = $this->getMQConfigList();
			$_config = $_config_list[self::$_MQ_KEY['mqtt']] ?? [];
			
			$_mqttObj = new MQTT($_config);
			
			return $_mqttObj;
		});
		
		// 添加RabbitMQ
		$this->setKeyItemData(self::$_MQ_KEY['rabbitmq'], function ($data, $it) {
			$_config_list = $this->getMQConfigList();
			$_config = $_config_list[self::$_MQ_KEY['rabbitmq']] ?? [];
			
			$_rabbitMQObj = defined('EXT_AMQP_ENABLED') ? new RabbitMQExt($_config) : new RabbitMQ($_config);
			
			return $_rabbitMQObj;
		});
		
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = self::class;
		$this->name_info['intro'] = 'MQ集合管理';
	}
	
	/**
	 * 获取加载的MQ配置文件列表值
	 *
	 * @return array|int|string|null
	 */
	public function getMQConfigList() {
		return $this->getConfigObj()->getListDataValue(self::$_MQ_CONFIG_NAME);
	}
	
	/**
	 * 获取MQTT对象
	 *
	 * @return MQTT
	 */
	public function getMQTTObj() {
		return $this->getKeyDataValue(self::$_MQ_KEY['mqtt']);
	}
	
	/**
	 * 获取RabbitMQ对象
	 *
	 * @return RabbitMQ|RabbitMQExt
	 */
	public function getRabbitMQObj() {
		return $this->getKeyDataValue(self::$_MQ_KEY['rabbitmq']);
	}
	
	/**
	 * 是否启用MQTT
	 *
	 * @return bool
	 */
	public function isMQTTEnabled() {
		return $this->isEnabledKey(self::$_MQ_KEY['mqtt']);
	}
	
	/**
	 * 设置是否启用MQTT
	 *
	 * @param bool $enabled
	 * @return $this
	 */
	public function setMQTTEnabled(bool $enabled) {
		$this->setEnabledKey(self::$_MQ_KEY['mqtt'], $enabled);
		
		return $this;
	}
	
	/**
	 * 是否启用RabbitMQ
	 *
	 * @return bool
	 */
	public function isRabbitMQEnabled() {
		return $this->isEnabledKey(self::$_MQ_KEY['rabbitmq']);
	}
	
	/**
	 * 设置是否启用RabbitMQ
	 *
	 * @param bool $enabled
	 * @return $this
	 */
	public function setRabbitMQEnabled(bool $enabled) {
		$this->setEnabledKey(self::$_MQ_KEY['rabbitmq'], $enabled);
		
		return $this;
	}
	
	/**
	 * MQTT是否已连接服务端
	 *
	 * @return bool
	 */
	public function isMQTTConnected(): bool {
		$mqttObj = $this->getMQTTObj();
		if (empty($mqttObj)) {
			return false;
		}
		
		return $mqttObj->isConnected();
	}
	
	/**
	 * RabbitMQ是否已连接服务端
	 *
	 * @return bool
	 */
	public function isRabbitMQConnected(): bool {
		$rabbitMQObj = $this->getRabbitMQObj();
		if (empty($rabbitMQObj)) {
			return false;
		}
		
		return $rabbitMQObj->isConnected();
	}
	
	/**
	 * @return Config
	 */
	public function getConfigObj() {
		return $this->_configObj;
	}
	
	/**
	 * @param mixed $configObj
	 */
	public function _setConfigObj($configObj) {
		$this->_configObj = $configObj;
	}
	
	
}