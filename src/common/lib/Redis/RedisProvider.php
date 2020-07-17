<?php

namespace uujia\framework\base\common\lib\Redis;

use uujia\framework\base\common\Config;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\traits\ResultTrait;

class RedisProvider extends BaseClass implements RedisProviderInterface {
	use ResultTrait;
	
	/**
	 * @var \Redis|\Swoole\Coroutine\Redis
	 */
	protected $_redisObj;
	
	/**
	 * 是否启用
	 * @var bool
	 */
	protected $_enabled = true;
	
	/**
	 * 前缀
	 * @var string
	 */
	protected $_prefix = '';
	
	/**
	 * 主机或域名
	 * @var string
	 */
	protected $_host = '';
	
	/**
	 * 端口
	 * @var int
	 */
	protected $_port = 6379;
	
	/**
	 * 密码
	 * @var string
	 */
	protected $_password = '';
	
	/**
	 * 密码
	 * @var int
	 */
	protected $_select = 0;
	
	/**
	 * Redis constructor.
	 *
	 * @param \Redis|\Swoole\Coroutine\Redis $redisObj
	 * @param string                         $host
	 * @param int                            $port
	 * @param string                         $password
	 * @param string                         $prefix
	 * @param int                            $select
	 */
	public function __construct($redisObj = null,
	                            string $host = '', int $port = 6379, string $password = '', string $prefix = '',
	                            int $select = 0) {
		$this->_redisObj = $redisObj ?? new \Redis();
		
		$this->_host = $host;
		$this->_post = $port;
		$this->_password = $password;
		$this->_prefix = $prefix;
		$this->_select = $select;
		
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
		$this->name_info['name']  = self::class;
		$this->name_info['intro'] = 'Redis供应商';
	}
	
	/**
	 * 连接
	 *
	 * @return $this
	 */
	public function connect() {
		if ($this->isEnabled()) {
			// connect
			$this->getRedisObj()->connect($this->getHost(), $this->getPort());
			// select
			$this->getRedisObj()->select($this->getSelect());
			// password
			!empty($this->getPassword()) && $this->getRedisObj()->auth($this->getPassword());
			
			if (!$this->getRedisObj()->isConnected()) {
				$this->code(13001); // 连接Redis服务端失败
			}
		}
		
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isEnabled(): bool {
		return $this->_enabled;
	}
	
	/**
	 * @param bool $enabled
	 *
	 * @return RedisProvider
	 */
	public function setEnabled(bool $enabled) {
		$this->_enabled = $enabled;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getPrefix(): string {
		return $this->_prefix;
	}
	
	/**
	 * @param string $prefix
	 *
	 * @return RedisProvider
	 */
	public function setPrefix(string $prefix) {
		$this->_prefix = $prefix;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getHost(): string {
		return $this->_host;
	}
	
	/**
	 * @param string $host
	 *
	 * @return RedisProvider
	 */
	public function setHost(string $host) {
		$this->_host = $host;
		
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getPort(): int {
		return $this->_port;
	}
	
	/**
	 * @param int $port
	 *
	 * @return RedisProvider
	 */
	public function setPort(int $port) {
		$this->_port = $port;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getPassword(): string {
		return $this->_password;
	}
	
	/**
	 * @param string $password
	 *
	 * @return RedisProvider
	 */
	public function setPassword(string $password) {
		$this->_password = $password;
		
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getSelect(): int {
		return $this->_select;
	}
	
	/**
	 * @param int $select
	 */
	public function setSelect(int $select) {
		$this->_select = $select;
		
		return $this;
	}
	
	/**
	 * @return \Redis|\Swoole\Coroutine\Redis
	 */
	public function getRedisObj() {
		return $this->_redisObj;
	}
	
	/**
	 * @param \Redis|\Swoole\Coroutine\Redis $redisObj
	 *
	 * @return RedisProvider
	 */
	public function _setRedisObj($redisObj) {
		$this->_redisObj = $redisObj;
		
		return $this;
	}
	
	
}