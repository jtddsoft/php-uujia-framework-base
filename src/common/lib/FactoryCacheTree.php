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
	use NameBase;
	
	/**
	 * 父级FactoryCacheTree
	 *
	 * @var $_parent FactoryCacheTree
	 */
	protected $_parent = null;
	
	/**
	 * 节点数据
	 *
	 * @var Data $_data
	 */
	protected $_data = null;
	
	/**
	 * 初始化
	 */
	public function init() {
		parent::init();
		
		$this->_data = new Data($this);
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
	public function get(string $key): FactoryCacheTree {
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
	public function setItemData(\Closure $factoryFunc) {
		$item = new FactoryCacheTree();
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
	public function setKeyItemData($key, \Closure $factoryFunc) {
		$item = $this->setItemData($factoryFunc);
		
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
	public function unshiftItemData(\Closure $factorySubFunc) {
		$item = new FactoryCacheTree();
		
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
	public function unshiftKeyItemData($key, \Closure $factorySubFunc, \Closure $factoryItemFunc = null) {
		if ($this->has($key)) {
			$item = $this->get($key);
		} else {
			$item = $this->setItemData($factoryItemFunc);
			
			$this->set($key, $item);
		}
		
		$item->unshiftItemData($factorySubFunc);
		
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
	public function addItemData(\Closure $factorySubFunc) {
		$item = new FactoryCacheTree();
		
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
	public function addKeyItemData($key, \Closure $factorySubFunc, \Closure $factoryItemFunc = null) {
		if ($this->has($key)) {
			$item = $this->get($key);
		} else {
			$item = $this->setItemData($factoryItemFunc);
			
			$this->set($key, $item);
		}
		
		$item->addItemData($factorySubFunc);
		
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
	
	
	
}
