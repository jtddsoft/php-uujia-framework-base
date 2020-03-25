<?php


namespace uujia\framework\base\common\lib\Cache;


use uujia\framework\base\common\lib\Base\BaseClass;

class CacheDataProvider extends BaseClass {
	
	/**
	 * 是否在收集信息的同时写入缓存
	 *
	 * @var bool
	 */
	protected $_writeCache = true;
	
	
	
	/**
	 * 构建数据 写入缓存
	 *
	 * @return mixed
	 */
	public function make() {
	
	}
	
	/**
	 * @return bool
	 */
	public function isWriteCache(): bool {
		return $this->_writeCache;
	}
	
	/**
	 * @param bool $writeCache
	 *
	 * @return $this
	 */
	public function setWriteCache(bool $writeCache) {
		$this->_writeCache = $writeCache;
		
		return $this;
	}
	
}