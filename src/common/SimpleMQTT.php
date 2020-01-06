<?php


namespace uujia\framework\base\common;


use Bluerhinos\phpMQTT;
use uujia\framework\base\traits\ResultBase;

class SimpleMQTT {
	use ResultBase;
	
	// public static $_CLIENT_TYPE = [
	// 	'unknow'    => 0,   // 未知
	// 	'publish'   => 1,   // 发布者
	// 	'subscribe' => 2,   // 订阅者
	// ];
	
	public static $_ERROR_CODE = [
		'0'   => 'ok',
		'100' => '未知错误',
		
		// MQTT
		'101' => '未成功初始化',
		'102' => '连接失败',
	];
	
	// MQTT 对象
	/** @var $mqttObj phpMQTT */
	protected $mqttObj;
	
	// 配置
	protected $_config = [
		// 'client_type' => 0,
		'enabled' => true,              // 启用
		
		'server'    => "localhost",     // change if necessary
		'port'      => 1883,            // change if necessary
		'username'  => "hello",         // set your username
		'password'  => "123456",        // set your password
		'client_id' => '',              // make sure this is unique for connecting to sever - you could use uniqid()
		'cafile'    => null,            // 证书
		'topics'    => '',              // 主题
	];
	
	protected $_init = false;
	
	
	public function __construct($config = []) {
		if (!empty($config)) {
			$this->_config = array_merge($this->_config, $config);
		}
		
		// $this->init();
	}
	
	/**
	 * 初始化
	 *
	 * @return bool
	 */
	public function init() {
		if ($this->_config['enabled']) {
			$this->mqttObj = new phpMQTT($this->_config['server'],
			                             $this->_config['port'],
			                             $this->_config['client_id'],
			                             $this->_config['cafile']);
			
			$this->setInit(true);
			return true;
		}
		
		$this->setInit(false);
		return false;
	}
	
	/**
	 * config
	 * get set
	 *
	 * @param null $config
	 *
	 * @return $this|array
	 */
	public function config($config = null) {
		if ($config === null) {
			return $this->_config;
		} else {
			$this->_config = array_merge($this->_config, $config);
		}
		
		return $this;
	}
	
	/**
	 * 启用
	 * get set
	 *
	 * @param string|null $enabled
	 *
	 * @return $this|array
	 */
	public function enabled($enabled = null) {
		if ($enabled === null) {
			return $this->_config['enabled'];
		} else {
			$this->_config['enabled'] = $enabled;
		}
		
		return $this;
	}
	
	/**
	 * server
	 * get set
	 *
	 * @param string|null $server
	 *
	 * @return $this|array
	 */
	public function server($server = null) {
		if ($server === null) {
			return $this->_config['server'];
		} else {
			$this->_config['server'] = $server;
		}
		
		return $this;
	}
	
	/**
	 * port
	 * get set
	 *
	 * @param int|null $port
	 *
	 * @return $this|array
	 */
	public function port($port = null) {
		if ($port === null) {
			return $this->_config['port'];
		} else {
			$this->_config['port'] = $port;
		}
		
		return $this;
	}
	
	/**
	 * username
	 * get set
	 *
	 * @param string|null $username
	 *
	 * @return $this|array
	 */
	public function username($username = null) {
		if ($username === null) {
			return $this->_config['username'];
		} else {
			$this->_config['username'] = $username;
		}
		
		return $this;
	}
	
	/**
	 * password
	 * get set
	 *
	 * @param string|null $password
	 *
	 * @return $this|array
	 */
	public function password($password = null) {
		if ($password === null) {
			return $this->_config['password'];
		} else {
			$this->_config['password'] = $password;
		}
		
		return $this;
	}
	
	/**
	 * client_id
	 * get set
	 *
	 * @param string|null $client_id
	 *
	 * @return $this|array
	 */
	public function client_id($client_id = null) {
		if ($client_id === null) {
			return $this->_config['client_id'];
		} else {
			$this->_config['client_id'] = $client_id;
		}
		
		return $this;
	}
	
	/**
	 * cafile
	 * get set
	 *
	 * @param string|null $cafile
	 *
	 * @return $this|array
	 */
	public function cafile($cafile = null) {
		if ($cafile === null) {
			return $this->_config['cafile'];
		} else {
			$this->_config['cafile'] = $cafile;
		}
		
		return $this;
	}
	
	/**
	 * topics
	 * get set
	 *
	 * @param string|null $topics
	 *
	 * @return $this|array
	 */
	public function topics($topics = null) {
		if ($topics === null) {
			return $this->_config['topics'];
		} else {
			$this->_config['topics'] = $topics;
		}
		
		return $this;
	}
	
	/**
	 * 自动连接服务端
	 *
	 * @param bool $clean
	 * @param null $will
	 *
	 * @return $this|array|mixed|string|\think\response\Json
	 */
	public function connect_auto($clean = true, $will = NULL) {
		if ($this->isErr()) { return $this->return_error(); }
		
		if (!$this->isInit()) {
			if (!$this->init()) {
				return $this->error(self::$_ERROR_CODE[101], 101); // 未成功初始化
			}
		}
		
		$re = $this->mqttObj->connect_auto($clean, $will, $this->_config['username'], $this->_config['password']);
		if ($re === false) {
			return $this->error(self::$_ERROR_CODE[102], 102); // 连接失败
		}
		
		return $this;
	}
	
	/**
	 * 连接服务端
	 *
	 * @param bool $clean
	 * @param null $will
	 *
	 * @return $this|array|mixed|string|\think\response\Json
	 */
	public function connect($clean = true, $will = NULL) {
		if ($this->isErr()) { return $this->return_error(); }
		
		if (!$this->isInit()) {
			if (!$this->init()) {
				return $this->error(self::$_ERROR_CODE[101], 101); // 未成功初始化
			}
		}
		
		$re = $this->mqttObj->connect($clean, $will, $this->_config['username'], $this->_config['password']);
		if ($re === false) {
			return $this->error(self::$_ERROR_CODE[102], 102); // 连接失败
		}
		
		return $this;
	}
	
	/**
	 * 关闭连接
	 *
	 * @return array|string|\think\response\Json
	 */
	public function close() {
		if ($this->isErr()) { return $this->return_error(); }
		
		$this->mqttObj->close();
		
		return $this->ok();
	}
	
	/**
	 * 订阅
	 *
	 * @param int $qos
	 *
	 * @return array|\think\response\Json|SimpleMQTT
	 */
	public function subscribe($qos = 0) {
		if ($this->isErr()) { return $this->return_error(); }
		
		$this->mqttObj->subscribe($this->_config['topics'], $qos);

		while($this->mqttObj->proc()){}
		
		return $this;
	}
	
	/**
	 * 发布
	 *
	 * @param     $content
	 * @param int $qos
	 * @param int $retain
	 *
	 * @return array|\think\response\Json|SimpleMQTT
	 */
	public function publish($content, $qos = 0, $retain = 0) {
		if ($this->isErr()) { return $this->return_error(); }
		
		$this->mqttObj->publish($this->_config['topics'], $content, $qos, $retain);
		
		return $this;
	}
	
	/**
	 * @return phpMQTT
	 */
	public function getMqttObj(): phpMQTT {
		return $this->mqttObj;
	}
	
	/**
	 * @param phpMQTT $mqttObj
	 */
	public function setMqttObj(phpMQTT $mqttObj) {
		$this->mqttObj = $mqttObj;
	}
	
	/**
	 * @return bool
	 */
	public function isInit(): bool {
		return $this->_init;
	}
	
	/**
	 * @param bool $init
	 */
	public function setInit(bool $init) {
		$this->_init = $init;
	}
	
	
}