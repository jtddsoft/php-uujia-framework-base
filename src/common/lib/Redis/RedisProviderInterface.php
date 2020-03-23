<?php


namespace uujia\framework\base\common\lib\Redis;


interface RedisProviderInterface {
	
	/**
	 * @return mixed
	 */
	public function connect();
	
	/**
	 * @return mixed
	 */
	public function getRedisObj();
	
	public function isEnabled(): bool;
	
	/**
	 * @param bool $enabled
	 * @return RedisProviderInterface
	 */
	public function setEnabled(bool $enabled);
	
	/**
	 * @return string
	 */
	public function getPrefix(): string;
	
	/**
	 * @param string $prefix
	 * @return RedisProviderInterface
	 */
	public function setPrefix(string $prefix);
	
	/**
	 * @return string
	 */
	public function getHost(): string;
	
	/**
	 * @param string $host
	 * @return RedisProviderInterface
	 */
	public function setHost(string $host);
	
	/**
	 * @return int
	 */
	public function getPort(): int;
	
	/**
	 * @param int $port
	 * @return RedisProviderInterface
	 */
	public function setPort(int $port);
	
	/**
	 * @return string
	 */
	public function getPassword(): string;
	
	/**
	 * @param string $password
	 * @return RedisProviderInterface
	 */
	public function setPassword(string $password);
	
}