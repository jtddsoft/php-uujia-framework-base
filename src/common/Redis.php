<?php

namespace uujia\framework\base\common;


use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Redis\RedisProviderInterface;
use uujia\framework\base\common\traits\NameBase;
use uujia\framework\base\common\lib\Container\Container;

class Redis extends BaseClass {
	
	// const KEY_CONTAINER_REDIS_ALIAS = 'redisProvider';
	
	/** @var RedisProviderInterface $_redisProviderObj */
	protected $_redisProviderObj;
	
	/** @var Config $_configObj */
	protected $_configObj;
	
	
	/**
	 * Redis constructor.
	 * 依赖Config
	 *
	 * @AutoInjection(arg = "redisProviderObj", name = "redisProvider")
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
		$this->name_info['name'] = self::class;
		$this->name_info['intro'] = 'Redis服务';
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
		];
		
		// if (empty($this->getRedisProviderObj())) {
		// 	/** @var Container $containerObj */
		// 	$containerObj = Container::getInstance();
		//
		// 	// 从容器中取出redis 这里用的是别名 【请一定要配置好容器别名列表】
		// 	$this->setRedisProviderObj($containerObj->get(self::KEY_CONTAINER_REDIS_ALIAS));
		// }
		
		$configRedis = $this->getConfigObj()->loadValue('redis', '', 'redis');
		$configRedis = array_merge($_redisParam, $configRedis);
		$this->getRedisProviderObj()
		     ->setHost($configRedis['host'])
			 ->setPort($configRedis['port'])
			 ->setPassword($configRedis['password'])
			 ->setPrefix($configRedis['prefix'])
			 ->connect();
		
		return $this;
	}
	
	/**
	 * 获取Redis对象
	 * @return mixed
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
	 * @return Redis
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
	 * @return Redis
	 */
	public function setRedisProviderObj(RedisProviderInterface $redisProviderObj) {
		$this->_redisProviderObj = $redisProviderObj;
		
		return $this;
	}
	
	
	
}