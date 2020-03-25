<?php


namespace uujia\framework\base\common\lib\Cache;


use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Tree\TreeFunc;

class CacheDataManager extends BaseClass {

	/** @var TreeFunc $_providerList */
	protected $_providerList = null;
	
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
	public function setProvider($key, $itemProvider) {
		$subItemFunc = function ($data, $it, $params) use ($itemProvider) {
			$itemProvider->make();
			
			return $itemProvider;
		};
		
		$itemFunc = function ($data, $it, $params) {
		
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
	
}