<?php


namespace uujia\framework\base\common\lib\Redis;


interface RedisProviderInterface {
	
	public function connect();
	
	public function getRedisObj();
	
	public function isEnabled(): bool;
	
	public function setEnabled(bool $enabled);
	
	public function getPrefix(): string;
	
	public function setPrefix(string $prefix);
	
	public function getHost(): string;
	
	public function setHost(string $host);
	
	public function getPort(): int;
	
	public function setPort(int $port);
	
	public function getPassword(): string;
	
	public function setPassword(string $password);
	
}