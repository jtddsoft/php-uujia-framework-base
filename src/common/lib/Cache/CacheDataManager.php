<?php


namespace uujia\framework\base\common\lib\Cache;


use uujia\framework\base\common\lib\Annotation\AutoInjection;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Redis\RedisProviderInterface;
use uujia\framework\base\common\lib\Tree\TreeFunc;

/**
 * Class CacheDataManager
 *
 * @package uujia\framework\base\common\lib\Cache
 */
class CacheDataManager extends BaseClass implements CacheDataManagerInterface {
	
	/**
	 * 缓存Key前缀
	 * （此处是来自上层的前缀 本层的真实前缀需要以此为基础拼接
	 *  例如：$_cacheKeyPrefix = ['ev'] 要保存 key = ['ss'] 真实Key应为 'ev:ss'）
	 *
	 * @var array $_cacheKeyPrefix
	 */
	protected $_cacheKeyPrefix = ['app'];

	/** @var TreeFunc $_providerList */
	protected $_providerList = null;
	
	/**
	 * Redis对象
	 *
	 * @var RedisProviderInterface $_redisProviderObj
	 */
	protected $_redisProviderObj;
	
	/**
	 * CacheDataManager constructor.
	 *
	 * @param RedisProviderInterface|null $redisProviderObj
	 */
	public function __construct(RedisProviderInterface $redisProviderObj = null) {
		$this->_redisProviderObj = $redisProviderObj;
		// $this->_cacheKeyPrefix = $cacheKeyPrefix;
		
		parent::__construct();
	}
	
	/**
	 * 初始化
	 * @return $this
	 */
	public function init() {
		parent::init();
		
		$this->initManager();
		
		return $this;
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = static::class;
		$this->name_info['intro'] = '缓存供应商管理';
	}
	
	/**
	 * 初始化缓存数据管理
	 */
	public function initManager() {
		// 第一次调用初始化 检查列表是否实例化
		if (empty($this->_providerList)) {
			$this->_providerList = new TreeFunc();
		}
		
		
	}
	
	/**
	 * 设置（添加或修改）缓存数据供应商
	 *
	 * @param                            $key
	 * @param CacheDataProviderInterface $itemProvider
	 *
	 * @return $this
	 */
	public function regProvider($key, $itemProvider) {
		$this->getContainer()->addAopIgnore(get_class($itemProvider));
		
		$cachePrefixs = $this->_cacheKeyPrefix;
		$subItemFunc = function ($data, $it, $params) use ($itemProvider, $key, $cachePrefixs) {
			/** @var CacheDataProvider $itemProvider */
			
			$_params = $itemProvider->getParams();
			$itemProvider->setParams(array_merge($_params, $params));
			$itemProvider->setCacheKeyPrefix($cachePrefixs);
			$itemProvider->setParent($this);
			$itemProvider->setKey($key);
			// $res = $itemProvider->make();
			// $itemProvider->make();
			//
			// return $itemProvider->getLastReturn();
			return $itemProvider;
		};
		
		$itemFunc = function ($data, $it, $params) use ($key) {
			/** @var TreeFunc $it */
			
			$r = [];
			$r['data'] = $data;
			$r['it'] = $it;
			$r['params'] = $params;
			
			// 缓存是否存在 综合所有缓存供应商
			$r['hasCache'] = function ($it) {
				/** @var TreeFunc $it */
				$it->cleanResults();
				
				// 初始化为存在
				$exist = $it->count() > 0;
				
				// 遍历 只要有一个不存在 就返回不存在
				foreach ($it->wForEachIK() as $i => $item) {
					/** @var TreeFunc $item */
					
					/** @var CacheDataProvider $data */
					$data = $item->getDataValue();
					if (!$data->hasCache()) {
						$exist = false;
						break;
					}
				}
				
				return $exist;
			};
			
			// make
			$r['make'] = function ($it) {
				/** @var TreeFunc $it */
				$it->cleanResults();
				
				// 遍历 只要有一个不存在 就返回不存在
				foreach ($it->wForEachIK() as $i => $item) {
					/** @var TreeFunc $item */
					
					/** @var CacheDataProvider $data */
					$data = $item->getDataValue();
					$data->make();
					
					if ($data->isErr()) {
						$it->assignLastReturn($data->getLastReturn());
						break;
					}
				}
				
				return $it->getLastReturn();
			};
			
			// toCache
			$r['toCache'] = function ($it) {
				/** @var TreeFunc $it */
				$it->cleanResults();
				
				// 遍历 只要有一个不存在 就返回不存在
				foreach ($it->wForEachIK() as $i => $item) {
					/** @var TreeFunc $item */
					
					/** @var CacheDataProvider $data */
					$data = $item->getDataValue();
					$data->toCache();
					
					if ($data->isErr()) {
						$it->assignLastReturn($data->getLastReturn());
						break;
					}
				}
				
				return $it->getLastReturn();
			};
			
			// fromCache
			$r['fromCache'] = function ($it) {
				/** @var TreeFunc $it */
				$it->cleanResults();
				
				// 遍历 只要有一个不存在 就返回不存在
				foreach ($it->wForEachIK() as $i => $item) {
					/** @var TreeFunc $item */
					
					/** @var CacheDataProvider $data */
					$data = $item->getDataValue();
					$data->fromCache();
					
					if ($data->isErr()) {
						$it->assignLastReturn($data->getLastReturn());
						break;
					}
				}
				
				return $it->getLastReturn();
			};
			
			return $r;
			
			// // 获取汇总列表中所有配置
			// /** @var TreeFunc $it */
			// $it->cleanResults();
			//
			// /**
			//  * 遍历指定key下所有缓存供应商收集数据
			//  */
			// $it->wForEach(function ($_item, $index, $me, $params) {
			// 	/** @var TreeFunc $_item */
			// 	/** @var TreeFunc $me */
			//
			// 	$re = $_item->getData()->get($params, true, false);
			//
			// 	// Local返回值复制
			// 	$_item->getData()->setLastReturn($re);
			//
			// 	// 加入到返回值列表
			// 	$me->setLastReturn($re);
			//
			// 	if ($_item->getData()->isErr()) {
			// 		return false;
			// 	}
			//
			// 	return true;
			// }, $params);
			//
			// // return $this->ok();
			// return $it->getLastReturn();
		};
		
		$this->getProviderList()
			->addKeyNewItemData($key, $subItemFunc, $itemFunc)
			
			// set item
			// 获取最后一次配置数据
			->getLastSetItemData()
			// 配置禁用自动缓存（每次调用都要执行 因此不能缓存）
			->setLoadCache(true)
		
			// add subitem
			// 从Data返回Item
			->getParent()
			// 获取最后一次新增的子项
			->getLastNewItemData()
			// 配置禁用自动缓存（每次调用都要执行 因此不能缓存）
			->setLoadCache(false);
		
		return $this;
	}
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
	/**
	 * 获取缓存key前缀
	 *
	 * @return array
	 */
	public function &getCacheKeyPrefix(): array {
		return $this->_cacheKeyPrefix;
	}
	
	/**
	 * 设置缓存key前缀
	 *
	 * @param array $cacheKeyPrefix
	 *
	 * @return $this
	 */
	public function setCacheKeyPrefix(array $cacheKeyPrefix) {
		$this->_cacheKeyPrefix = $cacheKeyPrefix;
		
		return $this;
	}
	
	/**
	 * 获取缓存数据供应商列表对象
	 *
	 * @return TreeFunc
	 */
	public function getProviderList() {
		return $this->_providerList;
	}
	
	/**
	 * 设置缓存数据供应商列表对象
	 * （内部使用）
	 *
	 * @param TreeFunc $providerList
	 *
	 * @return $this
	 */
	public function _setProviderList($providerList) {
		$this->_providerList = $providerList;
		
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
	 * @return $this
	 */
	public function setRedisProviderObj(RedisProviderInterface $redisProviderObj) {
		$this->_redisProviderObj = $redisProviderObj;
		
		return $this;
	}
	
	/**
	 * @return \Redis|\Swoole\Coroutine\Redis
	 */
	public function getRedisObj() {
		return $this->getRedisProviderObj()->getRedisObj();
	}
	
}