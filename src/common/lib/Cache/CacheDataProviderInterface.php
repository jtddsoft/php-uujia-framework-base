<?php


namespace uujia\framework\base\common\lib\Cache;

/**
 * Interface CacheDataProviderInterface
 * 缓存的提供者需要实现的接口规范
 *  例如：所有的事件为节省资源 事件调度依赖缓存
 *       而这些缓存的来源就来自于收集这些注册事件监听者信息 并将其放入缓存
 *       这里就充当事件监听器的信息收集者
 *       （当然这里只是接口规范 具体收集者可以是Builder 到时由Builder主动注册）
 *
 * @package uujia\framework\base\common\lib\Cache
 */
interface CacheDataProviderInterface {
	
	/**
	 * 构建数据 写入缓存
	 *
	 * @return mixed
	 */
	public function make();
	
	/**
	 * 从缓存读取
	 */
	public function fromCache();
	
	/**
	 * 写入缓存
	 *  需要写入服务器名称（main）、服务类型（event之类）
	 */
	public function toCache();
	
	/**
	 * 清空缓存
	 *
	 * @return $this
	 */
	public function clearCache();
	
	/**
	 * 是否存在缓存
	 * @return bool
	 */
	public function hasCache(): bool;
	
	/**
	 * 是否同时写入缓存
	 *
	 * @return bool
	 */
	public function isWriteCache();
	
	/**
	 * 设置是否写入缓存标记
	 *
	 * @param bool $writeCache
	 *
	 * @return $this
	 */
	public function setWriteCache(bool $writeCache);
	
	/**
	 * 获取输入参数
	 *
	 * @return array
	 */
	public function getParams();
	
	/**
	 * 设置输入参数
	 *
	 * @param array $params
	 * @return $this
	 */
	public function setParams($params);
	
	// /**
	//  * 获取返回值
	//  *
	//  * @return array
	//  */
	// public function getResults();
	//
	// /**
	//  * 设置返回值
	//  *
	//  * @param array $results
	//  * @return $this
	//  */
	// public function setResults($results);
	
	/**
	 * 获取缓存key的前缀
	 *  （可以为应用名称 用来区分多种缓存）
	 *
	 * @return array
	 */
	public function getCacheKeyPrefix();
	
	/**
	 * 设置缓存key的前缀
	 *
	 * @param array $cacheKeyPrefix
	 *
	 * @return $this
	 */
	public function setCacheKeyPrefix($cacheKeyPrefix);
	
	/**
	 * @return CacheDataManagerInterface
	 */
	public function getParent();
	
	/**
	 * @param CacheDataManagerInterface $parent
	 *
	 * @return $this
	 */
	public function setParent($parent);
	
	public function getRedisObj();
	
}