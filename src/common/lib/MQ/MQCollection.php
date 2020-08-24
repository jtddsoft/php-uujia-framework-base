<?php


namespace uujia\framework\base\common\lib\MQ;

use uujia\framework\base\common\lib\Config\ConfigManagerInterface;
use uujia\framework\base\common\lib\Tree\TreeFunc;

/**
 * Class MQCollection
 * MQ的集合
 *  （仅做对象汇总 并不能完全统一方法）
 *
 * @package uujia\framework\base\common\lib\MQ
 */
class MQCollection extends TreeFunc {
	
	const MQ_KEY = [
		'mqtt' => 'MQTT',
		'rabbitmq' => 'RabbitMQ',
	];
	
	public static $_MQ_CONFIG_NAME = 'mq_config';
	
	/**
	 * 配置对象
	 * @var ConfigManagerInterface
	 */
	protected $_configObj;
	
	/**
	 * MQCollection constructor.
	 *
	 * @param ConfigManagerInterface $configObj
	 * @param                        $parent
	 */
	public function __construct(ConfigManagerInterface $configObj, $parent = null) {
		$this->_configObj = $configObj;
		
		parent::__construct($parent);
	}
	
	/**
	 * 初始化
	 * @return $this
	 */
	public function init() {
		parent::init();
		
		// 先清空
		$this->clear();
		
		// 添加MQTT
		$this->setKeyNewItemData(self::MQ_KEY['mqtt'], function ($data, $it) {
			$_config_list = $this->getMQConfigList();
			$_config = $_config_list[self::MQ_KEY['mqtt']] ?? [];
			
			$_mqttObj = new MQTT($_config);
			
			return $_mqttObj;
		});
		
		// 添加RabbitMQ
		$this->setKeyNewItemData(self::MQ_KEY['rabbitmq'], function ($data, $it) {
			$_config_list = $this->getMQConfigList();
			$_config = $_config_list[self::MQ_KEY['rabbitmq']] ?? [];
			
			$_rabbitMQObj = defined('EXT_AMQP_ENABLED') ? new RabbitMQExt($_config) : new RabbitMQ($_config);
			
			return $_rabbitMQObj;
		});
		
		return $this;
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = static::class;
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
		return $this->getKeyDataValue(self::MQ_KEY['mqtt']);
	}
	
	/**
	 * 获取RabbitMQ对象
	 *
	 * @return RabbitMQ|RabbitMQExt
	 */
	public function getRabbitMQObj() {
		return $this->getKeyDataValue(self::MQ_KEY['rabbitmq']);
	}
	
	/**
	 * 是否启用MQTT
	 *
	 * @return bool
	 */
	public function isMQTTEnabled() {
		return $this->isEnabledKey(self::MQ_KEY['mqtt']);
	}
	
	/**
	 * 设置是否启用MQTT
	 *
	 * @param bool $enabled
	 * @return $this
	 */
	public function setMQTTEnabled(bool $enabled) {
		$this->setEnabledKey(self::MQ_KEY['mqtt'], $enabled);
		
		return $this;
	}
	
	/**
	 * 是否启用RabbitMQ
	 *
	 * @return bool
	 */
	public function isRabbitMQEnabled() {
		return $this->isEnabledKey(self::MQ_KEY['rabbitmq']);
	}
	
	/**
	 * 设置是否启用RabbitMQ
	 *
	 * @param bool $enabled
	 * @return $this
	 */
	public function setRabbitMQEnabled(bool $enabled) {
		$this->setEnabledKey(self::MQ_KEY['rabbitmq'], $enabled);
		
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
	 * @return ConfigManagerInterface
	 */
	public function getConfigObj() {
		return $this->_configObj;
	}
	
	/**
	 * @param ConfigManagerInterface $configObj
	 *
	 * @return $this
	 */
	public function _setConfigObj($configObj) {
		$this->_configObj = $configObj;
		
		return $this;
	}
	
	
}