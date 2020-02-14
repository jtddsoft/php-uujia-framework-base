<?php


namespace uujia\framework\base\common\lib\MQ;


use Bluerhinos\phpMQTT;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use uujia\framework\base\common\lib\Utils\JsonUtils;

class RabbitMQ extends MQ {
	
	// 配置
	protected $_config = [
		// 'client_type' => 0,
		'enabled' => false,              // 启用
		
		'server'    => "localhost",     // change if necessary
		'port'      => 5672,            // change if necessary
		'username'  => "hello",         // set your username
		'password'  => "123456",        // set your password
		
		// connect
		'queue'       => 'hello',
		'passive'     => false,
		'durable'     => true,
		'exclusive'   => false,
		'auto_delete' => false,
		'nowait'      => false,
		'arguments'   => [],
		'ticket'      => null,
		
		// subscribe
		// 'queue'       => 'hello',
		'consumer_tag' => '',
		'no_local'     => false,
		'no_ack'       => false,
		
		// publish
		'internal'            => false,
		'exchange'            => '',
		'exchange_type'       => 'topic',
		'routing_key'         => 'routingKey.hello',
		'routing_key_binding' => 'routingKey.*',
		'mandatory'           => true,
		'immediate'           => false,
	];
	
	// 连接的实例
	protected $_connection;
	
	// 通道的实例
	protected $_channel;
	
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = self::class;
		$this->name_info['intro'] = 'RibbitMQ通讯管理';
	}
	
	/**
	 * 初始化
	 *
	 * @return bool
	 */
	public function initMQ() {
		if ($this->_config['enabled']) {
			$this->setInit(true);
			return true;
		}
		
		$this->setInit(false);
		return false;
	}
	
	/**
	 * queue
	 * get set
	 *
	 * @param string|null $queue
	 *
	 * @return $this|string
	 */
	public function queue($queue = null) {
		if ($queue === null) {
			return $this->_config['queue'];
		} else {
			$this->_config['queue'] = $queue;
		}
		
		return $this;
	}
	
	/**
	 * passive
	 * get set
	 *
	 * @param bool|null $passive
	 *
	 * @return $this|bool
	 */
	public function passive($passive = null) {
		if ($passive === null) {
			return $this->_config['passive'];
		} else {
			$this->_config['passive'] = $passive;
		}
		
		return $this;
	}
	
	/**
	 * durable
	 * get set
	 *
	 * @param bool|null $durable
	 *
	 * @return $this|bool
	 */
	public function durable($durable = null) {
		if ($durable === null) {
			return $this->_config['durable'];
		} else {
			$this->_config['durable'] = $durable;
		}
		
		return $this;
	}
	
	/**
	 * exclusive
	 * get set
	 *
	 * @param bool|null $exclusive
	 *
	 * @return $this|bool
	 */
	public function exclusive($exclusive = null) {
		if ($exclusive === null) {
			return $this->_config['exclusive'];
		} else {
			$this->_config['exclusive'] = $exclusive;
		}
		
		return $this;
	}
	
	/**
	 * auto_delete
	 * get set
	 *
	 * @param bool|null $autoDelete
	 *
	 * @return $this|bool
	 */
	public function autoDelete($autoDelete = null) {
		if ($autoDelete === null) {
			return $this->_config['auto_delete'];
		} else {
			$this->_config['auto_delete'] = $autoDelete;
		}
		
		return $this;
	}
	
	/**
	 * nowait
	 * get set
	 *
	 * @param bool|null $nowait
	 *
	 * @return $this|bool
	 */
	public function nowait($nowait = null) {
		if ($nowait === null) {
			return $this->_config['nowait'];
		} else {
			$this->_config['nowait'] = $nowait;
		}
		
		return $this;
	}
	
	/**
	 * arguments
	 * get set
	 *
	 * @param array|null $arguments
	 *
	 * @return $this|array
	 */
	public function arguments($arguments = null) {
		if ($arguments === null) {
			return $this->_config['arguments'];
		} else {
			$this->_config['arguments'] = $arguments;
		}
		
		return $this;
	}
	
	/**
	 * ticket
	 * get set
	 *
	 * @param int|null $ticket
	 *
	 * @return $this|int
	 */
	public function ticket($ticket = null) {
		if ($ticket === null) {
			return $this->_config['ticket'];
		} else {
			$this->_config['ticket'] = $ticket;
		}
		
		return $this;
	}
	
	/**
	 * internal
	 * get set
	 *
	 * @param bool|null $internal
	 *
	 * @return $this|bool
	 */
	public function internal($internal = null) {
		if ($internal === null) {
			return $this->_config['internal'];
		} else {
			$this->_config['internal'] = $internal;
		}
		
		return $this;
	}
	
	/**
	 * exchange
	 * get set
	 *
	 * @param int|null $exchange
	 *
	 * @return $this|int
	 */
	public function exchange($exchange = null) {
		if ($exchange === null) {
			return $this->_config['exchange'];
		} else {
			$this->_config['exchange'] = $exchange;
		}
		
		return $this;
	}
	
	/**
	 * exchange_type
	 * get set
	 *
	 * @param string|null $exchangeType
	 *
	 * @return $this|string
	 */
	public function exchangeType($exchangeType = null) {
		if ($exchangeType === null) {
			return $this->_config['exchange_type'];
		} else {
			$this->_config['exchange_type'] = $exchangeType;
		}
		
		return $this;
	}
	
	/**
	 * routing_key
	 * get set
	 *
	 * @param string|null $routingKey
	 *
	 * @return $this|string
	 */
	public function routingKey($routingKey = null) {
		if ($routingKey === null) {
			return $this->_config['routing_key'];
		} else {
			$this->_config['routing_key'] = $routingKey;
		}
		
		return $this;
	}
	
	/**
	 * routing_key_binding
	 * get set
	 *
	 * @param string|null $routingKeyBinding
	 *
	 * @return $this|string
	 */
	public function routingKeyBinding($routingKeyBinding = null) {
		if ($routingKeyBinding === null) {
			return $this->_config['routing_key_binding'];
		} else {
			$this->_config['routing_key_binding'] = $routingKeyBinding;
		}
		
		return $this;
	}
	
	/**
	 * mandatory
	 * get set
	 *
	 * @param int|null $mandatory
	 *
	 * @return $this|int
	 */
	public function mandatory($mandatory = null) {
		if ($mandatory === null) {
			return $this->_config['mandatory'];
		} else {
			$this->_config['mandatory'] = $mandatory;
		}
		
		return $this;
	}
	
	/**
	 * immediate
	 * get set
	 *
	 * @param int|null $immediate
	 *
	 * @return $this|int
	 */
	public function immediate($immediate = null) {
		if ($immediate === null) {
			return $this->_config['immediate'];
		} else {
			$this->_config['immediate'] = $immediate;
		}
		
		return $this;
	}
	
	/**
	 * 自动连接服务端
	 *
	 * @return $this|array|mixed|string|\think\response\Json
	 */
	public function connect_auto() {
		return parent::connect_auto();
	}
	
	/**
	 * 连接服务端
	 *
	 * @return $this|array|mixed|string|\think\response\Json
	 */
	public function connect() {
		if ($this->isErr()) { return $this; }
		
		if (!$this->isInit()) {
			if (!$this->initMQ()) {
				$this->error(self::$_ERROR_CODE[101], 101); // 未成功初始化
				return $this;
			}
		}
		
		$this->setConnected(false);
		
		//建立一个连接通道，声明一个可以发送消息的队列hello
		// $connection = new AMQPStreamConnection($this->_config['server'],
		//                                        $this->_config['port'],
		//                                        $this->_config['username'],
		//                                        $this->_config['password']);
		
		$connection = new AMQPConnection($this->_config['server'],
		                                 $this->_config['port'],
		                                 $this->_config['username'],
		                                 $this->_config['password']);
		$this->setConnection($connection);
		
		$channel = $connection->channel();
		$this->setChannel($channel);
		
		// if ($re === null) {
		// 	$this->error(self::$_ERROR_CODE[102], 102); // 连接失败
		// 	return $this;
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
		
		$this->getChannel()->close();
		$this->getConnection()->close();
		
		return $this->ok();
	}
	
	/**
	 * 订阅
	 *
	 * @return array|\think\response\Json|RabbitMQ
	 * @throws \ErrorException
	 */
	public function subscribe() {
		if ($this->isErr()) { return $this; }
		
		if ($this->isConnected()) {
			//在接收消息的时候调用$callback函数
			$_callback = function ($msgObj) {
				if ($this->getCallbackSubscribe() !== null && is_callable($this->getCallbackSubscribe())) {
					$_param = [
						'msg' => $msgObj->body,
						'msgObj' => $msgObj,
					];
					call_user_func_array($this->getCallbackSubscribe(), $_param);
				}
			};
			
			$this->getChannel()->basic_consume($this->_config['queue'],
			                                   $this->_config['consumer_tag'],
			                                   $this->_config['no_local'],
			                                   $this->_config['no_ack'],
			                                   $this->_config['exclusive'],
			                                   $this->_config['nowait'],
			                                   $_callback,
			                                   $this->_config['ticket'],
			                                   $this->_config['arguments']);
			
			while(count($this->getChannel()->callbacks)) {
				$this->getChannel()->wait();
			}
		} else {
			$this->error(self::$_ERROR_CODE[104], 104); // 未连接服务端
		}
		
		return $this;
	}
	
	/**
	 * 发布
	 *
	 * @param     $content
	 * @return array|\think\response\Json|$this
	 */
	public function publish($content) {
		if ($this->isErr()) { return $this; }
		
		if ($this->isConnected()) {
			$re = $this->getChannel()->exchange_declare($this->_config['exchange'],
			                                            $this->_config['exchange_type'],
			                                            $this->_config['passive'],
			                                            $this->_config['durable'],
			                                            $this->_config['auto_delete'],
			                                            $this->_config['internal'],
			                                            $this->_config['nowait'],
			                                            $this->_config['arguments'],
			                                            $this->_config['ticket']);
			
			$re = $this->getChannel()->queue_declare($this->_config['queue'],
						                              $this->_config['passive'],
						                              $this->_config['durable'],
						                              $this->_config['exclusive'],
						                              $this->_config['auto_delete'],
						                              $this->_config['nowait'],
						                              $this->_config['arguments'],
						                              $this->_config['ticket']);
			
			$re = $this->getChannel()->queue_bind($this->_config['queue'],
			                                      $this->_config['exchange'],
			                                      $this->_config['routing_key_binding'],
			                                      $this->_config['nowait'],
			                                      $this->_config['arguments'],
			                                      $this->_config['ticket']);
			
			//定义一个消息，消息内容为Hello World!
			$msgText = $content;
			is_array($msgText) && $msgText = JsonUtils::je($content);
			
			$msgObj = new AMQPMessage($msgText);
			$this->getChannel()->basic_publish($msgObj,
			                                   $this->_config['exchange'],
			                                   $this->_config['routing_key'],
			                                   $this->_config['mandatory'],
			                                   $this->_config['immediate'],
			                                   $this->_config['ticket']);
		} else {
			$this->error(self::$_ERROR_CODE[104], 104); // 未连接服务端
		}
		
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getConnection() {
		return $this->_connection;
	}
	
	/**
	 * @param mixed $connection
	 */
	public function setConnection($connection) {
		$this->_connection = $connection;
	}
	
	/**
	 * @return AMQPChannel
	 */
	public function getChannel() {
		return $this->_channel;
	}
	
	/**
	 * @param mixed $channel
	 */
	public function setChannel($channel) {
		$this->_channel = $channel;
	}
	
	
}