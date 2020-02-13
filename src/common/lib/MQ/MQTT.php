<?php


namespace uujia\framework\base\common\lib\MQ;


use Bluerhinos\phpMQTT;

class MQTT extends MQ {
	
	// MQTT 对象
	/** @var $_mqObj phpMQTT */
	protected $_mqObj;
	
	// 配置
	protected $_config = [
		// 'client_type' => 0,
		'enabled' => false,              // 启用
		
		'server'    => "localhost",     // change if necessary
		'port'      => 1883,            // change if necessary
		'username'  => "hello",         // set your username
		'password'  => "123456",        // set your password
		'client_id' => '',              // make sure this is unique for connecting to sever - you could use uniqid()
		'cafile'    => null,            // 证书
		'topics'    => '',              // 主题
		
		// connect_auto connect
		'clean' => true,
		'will' => null,
		
		// subscribe
		'topics_callback' => null,
		// 'topics_callback' => [
		// 	'[topic1]' => [
		// 		'function' => function ($topic, $msg) {},
		// 		'qos' => 0,
		// 	],
		// 	'[topic2]' => [
		// 		'function' => function ($topic, $msg) {},
		// 		'qos' => 0,
		// 	],
		// ],
		'qos'    => 0,
		// subscribe publish
		'retain' => 0,
	];
	
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = self::class;
		$this->name_info['intro'] = 'MQTT通讯管理';
	}
	
	/**
	 * 初始化
	 *
	 * @return bool
	 */
	public function initMQ() {
		if ($this->_config['enabled']) {
			$this->setMqObj(new phpMQTT($this->_config['server'],
			                            $this->_config['port'],
			                            $this->_config['client_id'],
			                            $this->_config['cafile']));
			
			$this->setInit(true);
			return true;
		}
		
		$this->setInit(false);
		return false;
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
	 * @return $this|string
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
	 * clean
	 * get set
	 *
	 * @param int|null $clean
	 * @return $this|array
	 */
	public function clean($clean = null) {
		if ($clean === null) {
			return $this->_config['clean'];
		} else {
			$this->_config['clean'] = $clean;
		}
		
		return $this;
	}
	
	/**
	 * will
	 * get set
	 *
	 * @param int|null $will
	 * @return $this|array
	 */
	public function will($will = null) {
		if ($will === null) {
			return $this->_config['will'];
		} else {
			$this->_config['will'] = $will;
		}
		
		return $this;
	}
	
	/**
	 * topics_callback
	 * get set
	 *
	 *  [
	 *      '[topic1]' => [
	 *  		'function' => function ($topic, $msg) {},
	 *  		'qos' => 0,
	 *  	],
	 *  	'[topic2]' => [
	 *  		'function' => function ($topic, $msg) {},
	 *  		'qos' => 0,
	 *  	],
	 *  ]
	 *
	 * @param array|null $topics_callback
	 * @return $this|array
	 */
	public function topics_callback($topics_callback = null) {
		if ($topics_callback === null) {
			return $this->_config['topics_callback'];
		} else {
			$this->_config['topics_callback'] = $topics_callback;
		}
		
		return $this;
	}
	
	/**
	 * qos
	 * get set
	 *
	 * @param int|null $qos
	 * @return $this|array
	 */
	public function qos($qos = null) {
		if ($qos === null) {
			return $this->_config['qos'];
		} else {
			$this->_config['qos'] = $qos;
		}
		
		return $this;
	}
	
	/**
	 * retain
	 * get set
	 *
	 * @param int|null $retain
	 * @return $this|array
	 */
	public function retain($retain = null) {
		if ($retain === null) {
			return $this->_config['retain'];
		} else {
			$this->_config['retain'] = $retain;
		}
		
		return $this;
	}
	
	/**
	 * 自动连接服务端
	 *
	 * @return $this|array|mixed|string|\think\response\Json
	 */
	public function connect_auto() {
		if ($this->isErr()) { return $this; } // return $this->return_error();
		
		if (!$this->isInit()) {
			if (!$this->initMQ()) {
				$this->error(self::$_ERROR_CODE[101], 101); // 未成功初始化
				return $this;
			}
		}
		
		$this->setConnected(false);
		
		$re = $this->getMqObj()->connect_auto($this->_config['clean'],
		                                      $this->_config['will'],
		                                      $this->_config['username'],
		                                      $this->_config['password']);
		if ($re === false) {
			$this->error(self::$_ERROR_CODE[102], 102); // 连接失败
			return $this;
		}
		
		$this->setConnected(true);
		
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
				$this->error(self::$_ERROR_CODE[101], 101); // 未成功初始化
				return $this;
			}
		}
		
		$this->setConnected(false);
		
		$re = $this->getMqObj()->connect($this->_config['clean'],
		                                 $this->_config['will'],
		                                 $this->_config['username'],
		                                 $this->_config['password']);
		if ($re === false) {
			$this->error(self::$_ERROR_CODE[102], 102); // 连接失败
			return $this;
		}
		
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
		
		$this->getMqObj()->close();
		
		return $this->ok();
	}
	
	/**
	 * 订阅
	 *
	 * @param int $qos
	 *
	 * @return array|\think\response\Json|MQTT
	 */
	public function subscribe() {
		if ($this->isErr()) { return $this; }
		
		if ($this->isConnected()) {
			$_topics_callback = $this->topics_callback();
			
			if ($_topics_callback === null){
				$_topics_callback[$this->topics()] = [
					'function' => function ($topic, $msg) {
						if ($this->getCallbackSubscribe() !== null && is_callable($this->getCallbackSubscribe())) {
							$_param = [
								'msg' => $msg,
								'topic' => $topic,
							];
							call_user_func_array($this->getCallbackSubscribe(), $_param);
						}
					},
					'qos' => 0,
				];
			}
			
			$this->getMqObj()->subscribe($_topics_callback, $this->_config['qos']);
			
			while($this->getMqObj()->proc()){}
		} else {
			$this->error(self::$_ERROR_CODE[104], 104); // 未连接服务端
		}
		
		return $this;
	}
	
	/**
	 * 发布
	 *
	 * @param     $content
	 * @return array|\think\response\Json|MQTT
	 */
	public function publish($content) {
		if ($this->isErr()) { return $this; }
		
		if ($this->isConnected()) {
			$this->getMqObj()->publish($this->_config['topics'],
			                           $content,
			                           $this->_config['qos'],
			                           $this->_config['retain']);
		} else {
			$this->error(self::$_ERROR_CODE[104], 104); // 未连接服务端
		}
		
		return $this;
	}
	
	/**
	 * @return phpMQTT
	 */
	public function getMqObj(): phpMQTT {
		return $this->_mqObj;
	}
	
	/**
	 * @param phpMQTT $mqObj
	 */
	public function setMqObj($mqObj) {
		$this->_mqObj = $mqObj;
	}
	
	
	
}