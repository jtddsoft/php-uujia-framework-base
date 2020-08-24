<?php


namespace uujia\framework\base\common\lib\MQ;

use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\traits\ResultTrait;

abstract class AbstractMQ extends BaseClass implements MQInterface {
	use ResultTrait;
	
	// public static $_CLIENT_TYPE = [
	// 	'unknow'    => 0,   // 未知
	// 	'publish'   => 1,   // 发布者
	// 	'subscribe' => 2,   // 订阅者
	// ];
	
	/**
	 * 错误代码定义
	 */
	const ERROR_CODE = [
		'0'   => 'ok',
		'100' => '未知错误',
		
		// MQ
		'101' => '未成功初始化',
		'102' => '连接失败',
		'103' => '自动连接超时',
		'104' => '未连接服务端',
		'105' => '断开失败',
	];
	
	/**
	 * MQ 对象
	 */
	protected $_mqObj;
	
	/**
	 * 配置
	 *
	 * @var array
	 */
	protected $_config = [
		// 'client_type' => 0,
		'enabled' => false,              // 启用
		
		'server'    => "localhost",     // change if necessary
		'port'      => 1883,            // change if necessary
		'username'  => "hello",         // set your username
		'password'  => "123456",        // set your password
	];
	
	/**
	 * 是否已初始化
	 *
	 * @var bool
	 */
	protected $_init = false;
	
	/**
	 * 是否已建立连接
	 *
	 * @var bool
	 */
	protected $_connected = false;
	
	/**
	 * 自动连接超时时间（秒）
	 *
	 * @var int
	 */
	protected $_autoConnectTimeOut = 10;
	
	/**
	 * 订阅回调
	 *
	 * @var \Closure|null
	 */
	protected $_callbackSubscribe = null;
	
	/**
	 * AbstractMQ constructor.
	 *
	 * @param array $config
	 */
	public function __construct($config = []) {
		if (!empty($config)) {
			$this->_config = array_merge($this->_config, $config);
		}
		
		parent::__construct();
	}
	
	/**
	 * 初始化
	 * @return $this
	 */
	public function init() {
		parent::init();
		
		return $this;
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = static::class;
		$this->name_info['intro'] = 'MQ抽象基础类';
	}
	
	/**
	 * 初始化
	 *
	 * @return bool
	 */
	public function initMQ() {
		if ($this->_config['enabled']) {
			// 需要具体初始化
			
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
	 * 自动连接服务端
	 *
	 * @return $this|array|mixed|string|\think\response\Json
	 */
	public function connectAuto() {
		// if ($this->isErr()) { return $this; } // return $this->return_error();
		//
		// if (!$this->isInit()) {
		// 	if (!$this->initMQ()) {
		// 		$this->error(self::$_ERROR_CODE[101], 101); // 未成功初始化
		//      return $this;
		// 	}
		// }
		
		// $re = $this->getMqObj()->connect_auto($clean, $will, $this->_config['username'], $this->_config['password']);
		// if ($re === false) {
		// 	    $this->error(self::$_ERROR_CODE[102], 102); // 连接失败
		//      return $this;
		// }
		
		$_time = time();
		
		while(!$this->isConnected()) {
			$this->connect();
			
			if (time() - $_time > $this->getAutoConnectTimeOut()) {
				break;
			}
			
			sleep(1);
		}
		
		// 验证是否连接成功
		if (!$this->isConnected()) {
			$this->error(self::ERROR_CODE[103], 103); // 连接失败
			return $this;
		}
		
		return $this;
	}
	
	/**
	 * 连接服务端
	 *
	 * @return $this|array|mixed|string|\think\response\Json
	 */
	public function connect() {
		if ($this->isErr()) { return $this; } // return $this->return_error();
		
		if (!$this->isInit()) {
			if (!$this->initMQ()) {
				return $this->error(self::ERROR_CODE[101], 101); // 未成功初始化
			}
		}
		
		$this->setConnected(false);
		
		// $re = $this->getMqObj()->connect($clean, $will, $this->_config['username'], $this->_config['password']);
		// if ($re === false) {
		// 	return $this->error(self::$_ERROR_CODE[102], 102); // 连接失败
		// }
		
		$this->setConnected(true);
		
		return $this;
	}
	
	/**
	 * 关闭连接
	 *
	 * @return array|string|\think\response\Json
	 */
	public function close() {
		if ($this->isErr()) { return $this->return_error(); }
		
		// $this->mqttObj->close();
		
		return $this->ok();
	}
	
	/**
	 * 订阅
	 *
	 * @return array|\think\response\Json|$this
	 */
	public function subscribe() {
		if ($this->isErr()) { return $this; } // return $this->return_error();
		
		if ($this->isConnected()) {
			// $this->mqObj->subscribe($this->_config['topics'], $this->qos());
			//
			// while($this->mqObj->proc()){}
		} else {
			$this->error(self::ERROR_CODE[104], 104); // 未连接服务端
		}
		
		return $this;
	}
	
	/**
	 * 发布
	 *
	 * @param string|array $content
	 * @return array|\think\response\Json|$this
	 */
	public function publish($content) {
		if ($this->isErr()) { return $this; }
		
		if ($this->isConnected()) {
			// $this->mqObj->publish($this->_config['topics'], $content, $this->qos(), $this->retain());
		} else {
			$this->error(self::ERROR_CODE[104], 104); // 未连接服务端
		}
		
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getMqObj() {
		return $this->_mqObj;
	}
	
	/**
	 * @param $mqObj
	 * @return $this
	 */
	public function setMqObj($mqObj) {
		$this->_mqObj = $mqObj;
		
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isInit(): bool {
		return $this->_init;
	}
	
	/**
	 * @param bool $init
	 * @return $this
	 */
	public function setInit(bool $init) {
		$this->_init = $init;
		
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isConnected(): bool {
		return $this->_connected;
	}
	
	/**
	 * @param bool $connected
	 * @return $this
	 */
	public function setConnected(bool $connected) {
		$this->_connected = $connected;
		
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getAutoConnectTimeOut(): int {
		return $this->_autoConnectTimeOut;
	}
	
	/**
	 * @param int $autoConnectTimeOut
	 * @return $this
	 */
	public function setAutoConnectTimeOut(int $autoConnectTimeOut) {
		$this->_autoConnectTimeOut = $autoConnectTimeOut;
		
		return $this;
	}
	
	/**
	 * @return null|\Closure
	 */
	public function getCallbackSubscribe() {
		return $this->_callbackSubscribe;
	}
	
	/**
	 * @param null|\Closure $callbackSubscribe
	 * @return $this
	 */
	public function setCallbackSubscribe(\Closure $callbackSubscribe) {
		$this->_callbackSubscribe = $callbackSubscribe;
		
		return $this;
	}
	
	
	
}