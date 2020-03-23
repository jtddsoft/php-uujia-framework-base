<?php

namespace uujia\framework\base\common\lib\Redis;

use uujia\framework\base\common\traits\NameBase;
use uujia\framework\base\common\traits\ResultBase;

class Redis {
	use NameBase;
	use ResultBase;
	
	/**
	 * @var \Redis $_redisObj
	 */
	protected $_redisObj;
	
	/**
	 * 是否启用
	 * @var bool $_enabled
	 */
	protected $_enabled = true;
	
	/**
	 * 前缀
	 * @var string $_prefix
	 */
	protected $_prefix = '';
	
	/**
	 * 主机或域名
	 * @var string $_host
	 */
	protected $_host = '';
	
	/**
	 * 端口
	 * @var int $_port
	 */
	protected $_port = 6379;
	
	/**
	 * 密码
	 * @var string $_password
	 */
	protected $_password = '';
	
	/**
	 * Redis constructor.
	 *
	 * @param \Redis $redisObj
	 * @param string $host
	 * @param int    $port
	 * @param string $password
	 * @param string $prefix
	 */
	public function __construct(\Redis $redisObj = null, string $host = '', int $port = 6379, string $password = '', string $prefix = '') {
		$this->_redisObj = $redisObj ?? new \Redis();
		
		$this->_host = $host;
		$this->_post = $port;
		$this->_password = $password;
		$this->_prefix = $prefix;
		
		$this->init();
	}
	
	/**
	 * 初始化
	 * @return $this
	 */
	public function init() {
		$this->initNameInfo();
		
		return $this;
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name']  = self::class;
		$this->name_info['intro'] = 'Redis连接封装';
	}
	
	/**
	 * 连接
	 *
	 * @return $this
	 */
	public function connect() {
		$this->getRedisObj()->connect($this->getHost(), $this->getPort());
		!empty($this->getPassword()) && $this->getRedisObj()->auth($this->getPassword());
		
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
	 * @return Redis
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
	 * @return Redis
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
	 * @return Redis
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
	 * @return Redis
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
	 * @return Redis
	 */
	public function setPassword(string $password) {
		$this->_password = $password;
		
		return $this;
	}
	
	/**
	 * @return \Redis
	 */
	public function getRedisObj(): \Redis {
		return $this->_redisObj;
	}
	
	/**
	 * @param \Redis $redisObj
	 *
	 * @return Redis
	 */
	public function _setRedisObj(\Redis $redisObj) {
		$this->_redisObj = $redisObj;
		
		return $this;
	}
	
	
}