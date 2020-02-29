<?php


namespace uujia\framework\base\common\lib\MQ;

use Bluerhinos\phpMQTT;

// use PhpAmqpLib\Channel\AMQPChannel;
// use PhpAmqpLib\Connection\AMQPConnection;
// use PhpAmqpLib\Connection\AMQPStreamConnection;
// use PhpAmqpLib\Message\AMQPMessage;
use uujia\framework\base\common\lib\Utils\Json;

class RabbitMQExt extends RabbitMQ {
	
	// 配置
	protected $_config = [
		// 'client_type' => 0,
		'enabled' => false,              // 启用
		
		'server'   => "localhost",     // change if necessary
		'port'     => 5672,            // change if necessary
		'username' => "hello",         // set your username
		'password' => "123456",        // set your password
		
		'vhost'               => '/',
		
		// connect
		'queue'               => 'hello',
		'passive'             => false,
		'durable_exchange'    => AMQP_DURABLE,
		'durable_queue'       => AMQP_DURABLE,
		'exclusive'           => false,
		'auto_delete'         => false,
		'nowait'              => false,
		'arguments'           => [],
		'ticket'              => null,
		
		// subscribe
		// 'queue'       => 'hello',
		'consumer_tag'        => '',
		'no_local'            => false,
		'no_ack'              => false,
		'ack_flags'           => AMQP_AUTOACK,
		
		// publish
		'internal'            => false,
		'exchange'            => '',
		'exchange_type'       => AMQP_EX_TYPE_TOPIC,
		'routing_key'         => 'routingKey.hello',
		'routing_key_binding' => 'routingKey.*',
		'mandatory'           => true,
		'immediate'           => false,
	];
	
	// 连接的实例
	/** @var $_connection \AMQPConnection */
	protected $_connection = null;
	
	// 通道的实例
	/** @var $_channel \AMQPChannel */
	protected $_channel = null;
	
	// 交换器的实例
	/** @var $_exchange \AMQPExchange */
	protected $_exchange = null;
	
	// 队列的实例
	/** @var $_queue \AMQPQueue */
	protected $_queue = null;
	
	/**
	 * ack flags
	 * get set
	 *
	 * @param int|null $ackFlags
	 *
	 * @return $this|int
	 */
	public function ackFlags($ackFlags = null) {
		if ($ackFlags === null) {
			return $this->_config['ack_flags'];
		} else {
			$this->_config['ack_flags'] = $ackFlags;
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
		if ($this->isErr()) {
			return $this;
		}
		
		if (!$this->isInit()) {
			if (!$this->initMQ()) {
				$this->error(self::ERROR_CODE[101], 101); // 未成功初始化
				
				return $this;
			}
		}
		
		$this->setConnected(false);
		
		try {
			//建立一个连接通道，声明一个可以发送消息的队列hello
			$connection = new \AMQPConnection();
			$this->setConnection($connection);
			
			$connection->setHost($this->_config['server']);
			$connection->setPort($this->_config['port']);
			$connection->setLogin($this->_config['username']);
			$connection->setPassword($this->_config['password']);
			$connection->setVhost($this->_config['vhost']);
			
			if ($connection->connect()) {
				$this->setConnected(true);
			}
			
			return $this;
		} catch (\AMQPConnectionException $e) {
			$this->error(self::ERROR_CODE[102], 102); // 连接失败
			
			return $this;
		}
	}
	
	/**
	 * 关闭连接
	 *
	 * @return array|string|\think\response\Json
	 */
	public function close() {
		if ($this->isErr()) {
			return $this->return_error();
		}
		
		try {
			$this->getQueue()->delete();
			$this->getExchange()->delete();
			$this->getChannel()->close();
			$this->getConnection()->disconnect();
			
			return $this->ok();
		} catch (\AMQPChannelException $e) {
			return $this->error(self::ERROR_CODE[105], 105); // 断开失败
		} catch (\AMQPConnectionException $e) {
			return $this->error(self::ERROR_CODE[105], 105); // 断开失败
		} catch (\AMQPExchangeException $e) {
			return $this->error(self::ERROR_CODE[105], 105); // 断开失败
		}
	}
	
	/**
	 * 订阅
	 *
	 * @return array|\think\response\Json|RabbitMQ
	 */
	public function subscribe() {
		if ($this->isErr()) {
			return $this;
		}
		
		if ($this->isConnected()) {
			try {
				//在接收消息的时候调用$callback函数
				$_callback = function ($envelope, $queue) {
					/** @var $envelope \AMQPEnvelope */
					/** @var $queue \AMQPQueue */
					if ($this->getCallbackSubscribe() !== null && is_callable($this->getCallbackSubscribe())) {
						// $_param = [
						// 	'msg'    => $envelope->body,
						// 	'msgObj' => $envelope,
						// 	'queue'  => $queue,
						// ];
						$_param = [$envelope->getBody(), $envelope, $queue];
						call_user_func_array($this->getCallbackSubscribe(), $_param);
						// 如果是手动应答ACK 回调中需要调用ACK
						// $queue->ack($envelope->getDeliveryTag()); //手动发送ACK应答
						// $queue->ack($envelope->getDeliveryTag(), AMQP_AUTODELETE);
					}
				};
				
				// 通道
				if ($this->getChannel() === null || !$this->getChannel()->isConnected()) {
					$channel = new \AMQPChannel($this->getConnection());
					$this->setChannel($channel);
				}
				
				// 交换
				if ($this->getExchange() === null) {
					$exchange = new \AMQPExchange($this->getChannel());
					$this->setExchange($exchange);
				}
				
				$_changeExchange = $this->getExchange()->getName() != $this->_config['exchange'] ||
				                   $this->getExchange()->getType() != $this->_config['exchange_type'] ||
				                   $this->getExchange()->getFlags() != $this->_config['durable_exchange'];
				
				$this->getExchange()->getName() != $this->_config['exchange'] && $this->getExchange()->setName($this->_config['exchange']);
				$this->getExchange()->getType() != $this->_config['exchange_type'] && $this->getExchange()->setType($this->_config['exchange_type']);
				$this->getExchange()->getFlags() != $this->_config['durable_exchange'] && $this->getExchange()->setFlags($this->_config['durable_exchange']);
				
				if ($_changeExchange && empty($exchange)) {
					$this->getExchange()->declareExchange();
				}
				
				// 队列
				if ($this->getQueue() === null) {
					$queue = new \AMQPQueue($this->getChannel());
					$this->setQueue($queue);
				}
				
				$_changeQueue = $this->getQueue()->getName() != $this->_config['queue'] ||
				                $this->getQueue()->getFlags() != $this->_config['durable_queue'];
				
				$this->getQueue()->getName() != $this->_config['queue'] && $this->getQueue()->setName($this->_config['queue']);
				$this->getQueue()->getFlags() != $this->_config['durable_queue'] && $this->getQueue()->setFlags($this->_config['durable_queue']);
				
				if ($_changeQueue && empty($queue)) {
					$this->getQueue()->declareQueue();
				}
				
				if ($_changeQueue) {
					$this->getQueue()->bind($this->getExchange()->getName(), $this->_config['routing_key_binding']);
				}
				
				while (true) {
					$this->getQueue()->consume($_callback, $this->_config['ack_flags'], $this->_config['consumer_tag']);
					//$this->getQueue()->consume('processMessage', AMQP_AUTOACK); //自动ACK应答
				}
			} catch (\AMQPConnectionException $e) {
				$this->error(self::ERROR_CODE[104], 104); // 未连接服务端
			} catch (\AMQPChannelException $e) {
				$this->error(self::ERROR_CODE[104], 104); // 未连接服务端
			} catch (\AMQPExchangeException $e) {
				$this->error(self::ERROR_CODE[104], 104); // 未连接服务端
			} catch (\AMQPQueueException $e) {
				$this->error(self::ERROR_CODE[104], 104); // 未连接服务端
			} catch (\AMQPEnvelopeException $e) {
				$this->error(self::ERROR_CODE[104], 104); // 未连接服务端
			}
		} else {
			$this->error(self::ERROR_CODE[104], 104); // 未连接服务端
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
		if ($this->isErr()) {
			return $this;
		}
		
		if ($this->isConnected()) {
			try {
				// 通道
				if ($this->getChannel() === null || !$this->getChannel()->isConnected()) {
					$channel = new \AMQPChannel($this->getConnection());
					$this->setChannel($channel);
				}
				
				// 交换
				if ($this->getExchange() === null) {
					$exchange = new \AMQPExchange($this->getChannel());
					$this->setExchange($exchange);
				}
				
				$_changeExchange = $this->getExchange()->getName() != $this->_config['exchange'] ||
				                   $this->getExchange()->getType() != $this->_config['exchange_type'] ||
				                   $this->getExchange()->getFlags() != $this->_config['durable_exchange'];
				
				$this->getExchange()->getName() != $this->_config['exchange'] && $this->getExchange()->setName($this->_config['exchange']);
				$this->getExchange()->getType() != $this->_config['exchange_type'] && $this->getExchange()->setType($this->_config['exchange_type']);
				$this->getExchange()->getFlags() != $this->_config['durable_exchange'] && $this->getExchange()->setFlags($this->_config['durable_exchange']);
				
				if ($_changeExchange && empty($exchange)) {
					$this->getExchange()->declareExchange();
				}
				
				// 队列
				if ($this->getQueue() === null) {
					$queue = new \AMQPQueue($this->getChannel());
					$this->setQueue($queue);
				}
				
				$_changeQueue = $this->getQueue()->getName() != $this->_config['queue'] ||
				                $this->getQueue()->getFlags() != $this->_config['durable_queue'];
				
				$this->getQueue()->getName() != $this->_config['queue'] && $this->getQueue()->setName($this->_config['queue']);
				$this->getQueue()->getFlags() != $this->_config['durable_queue'] && $this->getQueue()->setFlags($this->_config['durable_queue']);
				
				if ($_changeQueue && empty($queue)) {
					$this->getQueue()->declareQueue();
				}
				
				if ($_changeQueue) {
					$this->getQueue()->bind($this->getExchange()->getName(), $this->_config['routing_key_binding']);
				}
				
				//定义一个消息，消息内容为Hello World!
				// $msgText = $content;
				// is_array($msgText) && $msgText = Json::je($content);
				
				$msgTexts = $content;
				if (!is_array($content)) {
					$msgTexts = [$content];
				}
				
				foreach ($msgTexts as $item) {
					$this->getExchange()->publish($item, $this->_config['routing_key']);
				}
			} catch (\AMQPConnectionException $e) {
			} catch (\AMQPChannelException $e) {
			} catch (\AMQPExchangeException $e) {
			} catch (\AMQPQueueException $e) {
				$this->error(self::ERROR_CODE[104], 104); // 未连接服务端
			}
		} else {
			$this->error(self::ERROR_CODE[104], 104); // 未连接服务端
		}
		
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isConnected(): bool {
		return $this->_connected && $this->getConnection()->isConnected();
	}
	
	/**
	 * @return \AMQPConnection
	 */
	public function getConnection() {
		return $this->_connection;
	}
	
	/**
	 * @param \AMQPConnection $connection
	 * @return $this
	 */
	public function setConnection($connection) {
		$this->_connection = $connection;
		
		return $this;
	}
	
	/**
	 * @return \AMQPChannel
	 */
	public function getChannel() {
		return $this->_channel;
	}
	
	/**
	 * @param \AMQPChannel $channel
	 * @return $this
	 */
	public function setChannel($channel) {
		$this->_channel = $channel;
		
		return $this;
	}
	
	/**
	 * @return \AMQPExchange
	 */
	public function getExchange() {
		return $this->_exchange;
	}
	
	/**
	 * @param \AMQPExchange $exchange
	 * @return $this
	 */
	public function setExchange($exchange) {
		$this->_exchange = $exchange;
		
		return $this;
	}
	
	/**
	 * @return \AMQPQueue
	 */
	public function getQueue() {
		return $this->_queue;
	}
	
	/**
	 * @param \AMQPQueue $queue
	 * @return $this
	 */
	public function setQueue($queue) {
		$this->_queue = $queue;
		
		return $this;
	}
	
	
}