<?php

namespace uujia\framework\base\common\lib\Tree;



/**
 * Class TreeFunc
 *
 * @package uujia\framework\base\common\Tree
 */
class TreeFunc extends TreeNode {
	
	/**
	 * 父级TreeFunc
	 *
	 * @var $_parent TreeFunc
	 */
	protected $_parent = null;
	
	/**
	 * 缓存最后一次实例化的TreeFunc对象
	 *  一般为add或unshift操作时新生产的TreeFunc对象
	 *  以便后续对新对象自定义操作
	 *
	 * @var TreeFunc $_lastNewItem
	 */
	protected $_lastNewItem = null;
	
	/**
	 * 缓存最后一次set实例化的TreeFunc对象
	 *  一般为set操作时新生产的TreeFunc对象
	 *  以便后续对新对象自定义操作
	 *
	 * @var TreeFunc $_lastSetItem
	 */
	protected $_lastSetItem = null;
	
	/**
	 * 节点数据
	 *
	 * @var TreeFuncData $_data
	 */
	protected $_data = null;
	
	/**
	 * 初始化
	 * @return $this
	 */
	public function init() {
		parent::init();
		
		$this->_data = new TreeFuncData($this);
		
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
			/** @var TreeFunc $item */
			$d = $item->getData();
			$value = $item->getDataValue();
			
			if (!empty($func)) {
				/**
				 * 示例：
				 *
				 * $re = $this->getErrCodeList()->wFindData(function ($item, $i, $me, $data, $value) use ($code) {
				 *      $_err = $value[self::ERROR_CODE_NAME];
				 *      if (array_key_exists($code, $_err)) {
				 *          return true;
				 *      }
				 *
				 *      return false;
				 * });
				 */
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
	 * @return TreeFunc|null
	 */
	public function smartGetter($link = '', $notExistCreate = true): TreeFunc {
		return parent::smartGetter($link, $notExistCreate);
	}
	
	/**
	 * 获取
	 *
	 * @param string $key
	 * @return TreeFunc|null
	 */
	public function get(string $key) {
		if (!parent::has($key)) {
			return null;
		}
		
		/** @var TreeFunc $v */
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
	 * @return TreeFunc
	 */
	public function newItemData(\Closure $factoryFunc) {
		$item = new TreeFunc();
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
	 * @return TreeFunc
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
	 * @return TreeFunc
	 */
	public function unshiftNewItemData(\Closure $factorySubFunc) {
		$item = new TreeFunc();
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
	 * @return TreeFunc
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
	 * @return TreeFunc
	 */
	public function addNewItemData(\Closure $factorySubFunc) {
		$item = new TreeFunc();
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
	 * @return TreeFunc
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
	 * @return TreeFuncData
	 */
	public function getData(): TreeFuncData {
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
	 * @return TreeFunc
	 */
	public function getLastNewItem() {
		return $this->_lastNewItem;
	}
	
	/**
	 * @param TreeFunc $lastNewItem
	 * @return $this
	 */
	public function _setLastNewItem($lastNewItem) {
		$this->_lastNewItem = $lastNewItem;
		
		return $this;
	}
	
	/**
	 * @return TreeFuncData
	 */
	public function getLastNewItemData() {
		$item = $this->getLastNewItem();
		if ($item === null) {
			return null;
		}
		
		return $item->getData();
	}
	
	/**
	 * @return TreeFunc
	 */
	public function getLastSetItem() {
		return $this->_lastSetItem;
	}
	
	/**
	 * @param TreeFunc $lastSetItem
	 * @return $this
	 */
	public function _setLastSetItem($lastSetItem) {
		$this->_lastSetItem = $lastSetItem;
		
		return $this;
	}
	
	/**
	 * @return TreeFuncData
	 */
	public function getLastSetItemData() {
		$item = $this->getLastSetItem();
		if ($item === null) {
			return null;
		}
		
		return $item->getData();
	}
	
}
