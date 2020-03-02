<?php

namespace uujia\framework\base\common\lib;

use uujia\framework\base\common\lib\FactoryCache\Data;
use uujia\framework\base\common\lib\Tree\TreeNode;
use uujia\framework\base\traits\NameBase;

/**
 * Class FactoryCacheTree
 *
 * @package uujia\framework\base\common\lib
 */
class FactoryCacheTree extends TreeNode {
	
	/**
	 * 父级FactoryCacheTree
	 *
	 * @var $_parent FactoryCacheTree
	 */
	protected $_parent = null;
	
	/**
	 * 缓存最后一次实例化的FactoryCacheTree对象
	 *  一般为add或unshift操作时新生产的FactoryCacheTree对象
	 *  以便后续对新对象自定义操作
	 *
	 * @var FactoryCacheTree $_lastNewItem
	 */
	protected $_lastNewItem = null;
	
	/**
	 * 缓存最后一次set实例化的FactoryCacheTree对象
	 *  一般为set操作时新生产的FactoryCacheTree对象
	 *  以便后续对新对象自定义操作
	 *
	 * @var FactoryCacheTree $_lastSetItem
	 */
	protected $_lastSetItem = null;
	
	/**
	 * 节点数据
	 *
	 * @var Data $_data
	 */
	protected $_data = null;
	
	/**
	 * 初始化
	 * @return $this
	 */
	public function init() {
		parent::init();
		
		$this->_data = new Data($this);
		
		return $this;
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name'] = self::class;
		$this->name_info['intro'] = '工厂树管理';
	}
	
	/**
	 * 根据权重顺序 查找符合条件的值
	 *  wFindData(function ($item, $i, $me, $data, $value) {
	 *
	 *  });
	 *
	 * @param \Closure|null $func
	 * @return bool
	 */
	public function wFindData(\Closure $func = null) {
		$result = false;
		
		$this->wForEach(function (&$item, $i, $me) use ($func, &$result) {
			/** @var FactoryCacheTree $item */
			$d = $item->getData();
			$value = $item->getDataValue();
			
			if (!empty($func)) {
				$re = call_user_func_array($func, [&$item, $i, $me, $d, $value]);
				if ($re) {
					$result = [
						'item' => $item,
						'i' => $i,
						'me' => $me,
						'data' => $d,
						'value' => $value,
					];
					
					return false;
				}
			}
			
			return true;
		});
		
		return $result;
	}
	
	/**
	 * 智能读取
	 *  格式：$link = 'a.b'; 会直接按层级输出（层级a -> 层级b）
	 *
	 * @param string $link
	 * @param bool   $notExistCreate
	 * @return FactoryCacheTree|null
	 */
	public function smartGetter($link = '', $notExistCreate = true): FactoryCacheTree {
		return parent::smartGetter($link, $notExistCreate);
	}
	
	/**
	 * 获取
	 *
	 * @param string $key
	 * @return FactoryCacheTree|null
	 */
	public function get(string $key) {
		if (!parent::has($key)) {
			return null;
		}
		
		/** @var FactoryCacheTree $v */
		$v = parent::get($key);
		
		return $v;
	}
	
	/**
	 * 设置Data
	 *  setItemData(function ($data, $it) use (a) {
	 *      dosomething
	 *      return value;
	 *  });
	 *
	 * @param \Closure $factoryFunc
	 * @return FactoryCacheTree
	 */
	public function newItemData(\Closure $factoryFunc) {
		$item = new FactoryCacheTree();
		// $this->_setLastNewItem($item);
		// $item->getData()->set(function ($data, $it) use () {
		// 	$config = include $path;
		//
		// 	return $config;
		// });
		
		$item->getData()->set($factoryFunc);
		
		return $item;
	}
	
	/**
	 * 设置Data（直接设置到key）
	 *  setKeyItemData('key1', function ($data, $it) use (a) {
	 *      dosomething
	 *      return value;
	 *  });
	 *
	 * @param string|int $key
	 * @param \Closure   $factoryFunc
	 * @return FactoryCacheTree
	 */
	public function setKeyNewItemData($key, \Closure $factoryFunc) {
		$item = $this->newItemData($factoryFunc);
		$this->_setLastSetItem($item);
		
		$this->set($key, $item);
		
		return $this;
	}
	
	/**
	 * 插入到顶部Data
	 *  unshiftItemData(function ($data, $it) use (a) {
	 *      dosomething
	 *      return value;
	 *  });
	 *
	 * @param \Closure $factorySubFunc
	 * @return FactoryCacheTree
	 */
	public function unshiftNewItemData(\Closure $factorySubFunc) {
		$item = new FactoryCacheTree();
		$this->_setLastNewItem($item);
		
		$item->getData()->set($factorySubFunc);
		
		$this->unshift($item);
		
		return $this;
	}
	
	/**
	 * 插入到顶部Data（直接设置到key）
	 *  unshiftKeyItemData('key1', function ($data, $it) use (a) {
	 *      dosomething
	 *      return value;
	 *  });
	 *
	 * @param string|int $key
	 * @param \Closure   $factoryItemFunc
	 * @param \Closure   $factorySubFunc
	 * @return FactoryCacheTree
	 */
	public function unshiftKeyNewItemData($key, \Closure $factorySubFunc, \Closure $factoryItemFunc = null) {
		if ($this->has($key)) {
			$item = $this->get($key);
		} else {
			$item = $this->newItemData($factoryItemFunc);
			
			$this->set($key, $item);
		}
		
		$this->_setLastSetItem($item);
		$item->unshiftNewItemData($factorySubFunc);
		
		return $this;
	}
	
	/**
	 * 添加到尾部Data
	 *  addItemData(function ($data, $it) use (a) {
	 *      dosomething
	 *      return value;
	 *  });
	 *
	 * @param \Closure $factorySubFunc
	 * @return FactoryCacheTree
	 */
	public function addNewItemData(\Closure $factorySubFunc) {
		$item = new FactoryCacheTree();
		$this->_setLastNewItem($item);
		
		$item->getData()->set($factorySubFunc);
		
		$this->add($item);
		
		return $this;
	}
	
	/**
	 * 添加到尾部Data（直接设置到key）
	 *  addKeyItemData('key1',
	 *      function ($data, $it) use (a) {
	 *          dosomething
	 *          return value;
	 *      },
	 *      function ($data, $it) use (a) {
	 *          dosomething
	 *          return value;
	 *      }
	 *  );
	 *
	 * @param string|int $key
	 * @param \Closure   $factoryItemFunc
	 * @param \Closure   $factorySubFunc
	 * @return FactoryCacheTree
	 */
	public function addKeyNewItemData($key, \Closure $factorySubFunc, \Closure $factoryItemFunc = null) {
		if ($this->has($key)) {
			$item = $this->get($key);
		} else {
			$item = $this->newItemData($factoryItemFunc);
			
			$this->set($key, $item);
		}
		
		$item->addNewItemData($factorySubFunc);
		
		return $this;
	}
	
	/**
	 * 获取数据
	 *
	 * @return Data
	 */
	public function getData(): Data {
		return $this->_data;
	}
	
	/**
	 * 获取数据值
	 *
	 * @param array $param
	 * @return mixed
	 */
	public function getDataValue($param = []) {
		return $this->getData()->get($param);
	}
	
	/**
	 * 获取指定key项数据值
	 *
	 * @param       $key
	 * @param array $param
	 * @return mixed
	 */
	public function getKeyDataValue($key, $param = []) {
		$item = $this->get($key);
		if ($item === null) {
			return null;
		}
		
		return $item->getDataValue($param);
	}
	
	/**************************************************
	 * getter setter
	 **************************************************/
	
	/**
	 * @return FactoryCacheTree
	 */
	public function getLastNewItem() {
		return $this->_lastNewItem;
	}
	
	/**
	 * @param FactoryCacheTree $lastNewItem
	 * @return $this
	 */
	public function _setLastNewItem($lastNewItem) {
		$this->_lastNewItem = $lastNewItem;
		
		return $this;
	}
	
	/**
	 * @return Data
	 */
	public function getLastNewItemData() {
		$item = $this->getLastNewItem();
		if ($item === null) {
			return null;
		}
		
		return $item->getData();
	}
	
	/**
	 * @return FactoryCacheTree
	 */
	public function getLastSetItem() {
		return $this->_lastSetItem;
	}
	
	/**
	 * @param FactoryCacheTree $lastSetItem
	 * @return $this
	 */
	public function _setLastSetItem($lastSetItem) {
		$this->_lastSetItem = $lastSetItem;
		
		return $this;
	}
	
	/**
	 * @return Data
	 */
	public function getLastSetItemData() {
		$item = $this->getLastSetItem();
		if ($item === null) {
			return null;
		}
		
		return $item->getData();
	}
	
}
