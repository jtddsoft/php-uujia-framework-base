<?php

namespace uujia\framework\base\common;


use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Container\Container;
use uujia\framework\base\common\lib\Redis\RedisProviderInterface;
use uujia\framework\base\common\lib\Annotation\AutoInjection;

/**
 * Class Redis
 *
 * @package uujia\framework\base\common
 */
class RedisDispatcher extends BaseClass {
	
	// const KEY_CONTAINER_REDIS_ALIAS = 'redisProvider';
	
	/** @var RedisProviderInterface */
	protected $_redisProviderObj;
	
	/** @var Config */
	protected $_configObj;
	
	
	/**
	 * Redis constructor.
	 * 依赖Config
	 *
	 * @param Config                 $configObj
	 * @param RedisProviderInterface $redisProviderObj
	 */
	public function __construct(Config $configObj, RedisProviderInterface $redisProviderObj = null) {
		$this->_configObj = $configObj;
		$this->_redisProviderObj = $redisProviderObj;
		
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
		$this->name_info['intro'] = 'Redis调度类';
	}
	
	/**
	 * 魔术方法
	 *  可直接访问redisProviderObj中方法
	 *
	 * @param $method
	 * @param $args
	 *
	 * @return $this|mixed
	 */
	public function __call($method, $args) {
		// 从redisProviderObj中查找方法
		if (is_callable([$this->getRedisProviderObj(), $method])) {
			return call_user_func_array([$this->getRedisProviderObj(), $method], $args);
		}
		
		// todo: 方法不存在
		// $this->getRedisProviderObj()->error('方法不存在', 1000);
		
		return $this;
	}
	
	/**
	 * 魔术方法
	 *  可直接访问redisProviderObj中方法
	 *
	 * Date: 2020/9/28
	 * Time: 1:43
	 *
	 * @param $method
	 * @param $args
	 * @return mixed|object|Config|null
	 */
	public static function __callStatic($method, $args) {
		$di = Container::getInstance();
		$me = $di->get(static::class);
		
		// 从redisProviderObj中查找方法
		if (is_callable([$me->getRedisProviderObj(), $method])) {
			return call_user_func_array([$me->getRedisProviderObj(), $method], $args);
		}
		
		// todo: 方法不存在
		// $me->getRedisProviderObj()->error('方法不存在', 1000);
		
		return $me;
	}
	
	/**
	 * 加载配置 初始化Redis 并连接Redis服务端
	 * @return $this
	 */
	public function loadConfig() {
		$_redisParam = [
			'enabled'  => true,
			
			// 缓存前缀
			'prefix'   => '',
			// redis主机
			'host'     => '127.0.0.1',
			// redis端口
			'port'     => 6379,
			// 密码
			'password' => '',
			// db ?
			'select'   => 0,
		];
		
		// if (empty($this->getRedisProviderObj())) {
		// 	/** @var Container $containerObj */
		// 	$containerObj = Container::getInstance();
		//
		// 	// 从容器中取出redis 这里用的是别名 【请一定要配置好容器别名列表】
		// 	$this->setRedisProviderObj($containerObj->get(self::KEY_CONTAINER_REDIS_ALIAS));
		// }
		
		$configRedis = $this->getConfigObj()
		                    ->getConfigManagerObj()
		                    ->loadValue('redis.redis');
		$configRedis = array_merge($_redisParam, $configRedis);
		$this->getRedisProviderObj()
		     ->setHost($configRedis['host'])
			 ->setPort($configRedis['port'])
			 ->setPassword($configRedis['password'])
			 ->setPrefix($configRedis['prefix'])
			 ->setSelect($configRedis['select'])
			 ->connect();
		
		return $this;
	}
	
	/**
	 * 获取Redis对象
	 * @return \Redis|\Swoole\Coroutine\Redis
	 */
	public function getRedisObj() {
		return $this->getRedisProviderObj()->getRedisObj();
	}
	
	/**
	 * @return Config
	 */
	public function getConfigObj(): Config {
		return $this->_configObj;
	}
	
	/**
	 * @param Config $configObj
	 * @return RedisDispatcher
	 */
	public function setConfigObj(Config $configObj) {
		$this->_configObj = $configObj;
		
		return $this;
	}
	
	/**
	 * @return RedisProviderInterface
	 */
	public function getRedisProviderObj(): RedisProviderInterface {
		return $this->_redisProviderObj;
	}
	
	/**
	 * @param RedisProviderInterface $redisProviderObj
	 * @return RedisDispatcher
	 */
	public function setRedisProviderObj(RedisProviderInterface $redisProviderObj) {
		$this->_redisProviderObj = $redisProviderObj;
		
		return $this;
	}
	
	
	
}