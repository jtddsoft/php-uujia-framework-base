<?php


namespace uujia\framework\base\common\lib\Cache;


use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Redis\RedisProviderInterface;
use uujia\framework\base\common\lib\Tree\TreeFunc;

class CacheDataManager extends BaseClass implements CacheDataManagerInterface {
	
	/**
	 * 缓存Key前缀
	 * （此处是来自上层的前缀 本层的真实前缀需要以此为基础拼接
	 *  例如：$_cacheKeyPrefix = ['ev'] 要保存 key = ['ss'] 真实Key应为 'ev:ss'）
	 *
	 * @var array $_cacheKeyPrefix
	 */
	protected $_cacheKeyPrefix = [];

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
	 * @param RedisProviderInterface|null $redisProvider
	 * @param array                       $cacheKeyPrefix
	 *
	 * @AutoInjection(arg = "redisProviderObj", name = "redisProvider")
	 */
	public function __construct(RedisProviderInterface $redisProvider = null, $cacheKeyPrefix = []) {
		$this->_redisProviderObj = $redisProvider;
		$this->_cacheKeyPrefix = $cacheKeyPrefix;
		
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
		$this->name_info['name'] = self::class;
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
	 * @param $key
	 * @param CacheDataProviderInterface $itemProvider
	 */
	public function regProvider($key, $itemProvider) {
		$cachePrefixs = $this->_cacheKeyPrefix;
		$subItemFunc = function ($data, $it, $params) use ($itemProvider, $key, $cachePrefixs) {
			$_params = $itemProvider->getParams();
			$itemProvider->setParams(array_merge($_params, $_params));
			$itemProvider->setCacheKeyPrefix($cachePrefixs);
			$itemProvider->setParent($this);
			$res = $itemProvider->make();
			
			return $res;
		};
		
		$itemFunc = function ($data, $it, $params) use ($key) {
			// 获取汇总列表中所有配置
			/** @var TreeFunc $it */
			$it->cleanResults();
			
			/**
			 * 遍历指定key下所有缓存供应商收集数据
			 */
			$it->wForEach(function ($_item, $index, $me, $params) {
				/** @var TreeFunc $_item */
				/** @var TreeFunc $me */
				
				$re = $_item->getData()->get($params, true, false);
				
				// Local返回值复制
				$_item->getData()->setLastReturn($re);
				
				// 加入到返回值列表
				$me->setLastReturn($re);
				
				if ($_item->getData()->isErr()) {
					return false;
				}
				
				return true;
			}, $params);
			
			// return $this->ok();
			return $it->getLastReturn();
		};
		
		$this->getProviderList()
			->addKeyNewItemData($key, $subItemFunc, $itemFunc)
			
			// set item
			// 获取最后一次配置数据
			->getLastSetItemData()
			// 配置禁用自动缓存（每次调用都要执行 因此不能缓存）
			->setIsLoadCache(false)
		
			// add subitem
			// 从Data返回Item
			->getParent()
			// 获取最后一次新增的子项
			->getLastNewItemData()
			// 配置禁用自动缓存（每次调用都要执行 因此不能缓存）
			->setIsLoadCache(false);
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