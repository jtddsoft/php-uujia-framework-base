<?php


namespace uujia\framework\base\common\lib\Tree;


use uujia\framework\base\common\lib\FactoryCacheTree;
use uujia\framework\base\traits\NameBase;

/**
 * Class ItemKeys
 *
 * @package uujia\framework\base\common\lib\Tree
 */
class TreeNode implements \Iterator, \ArrayAccess {
	use NameBase;
	
	// 默认权重
	public static $_DEFAULT_WEIGHT = 100;
	
	/**
	 * 父级TreeNode
	 *
	 * @var $_parent TreeNode
	 */
	protected $_parent = null;
	
	/**
	 * 子节点
	 */
	protected $_children = [];
	
	/**
	 * 迭代器游标位置
	 */
	protected $_position = 0;
	
	/**
	 * 权重排序索引
	 *
	 * @var array|null $_weight_index
	 */
	protected $_weight_index = null;
	
	/**
	 * 节点数据
	 *
	 * @var mixed $_data
	 */
	protected $_data = null;
	
	/**
	 * 权重
	 *  越大优先级越高 默认100
	 *
	 * @var int $_weight
	 */
	protected $_weight = 100;
	
	/**
	 * 层级
	 *
	 * @var int $_level
	 */
	protected $_level = 0;
	
	/**
	 * id *
	 *  （并非一定会用到）
	 *
	 * @var int|string $_id
	 */
	protected $_id = '';
	
	/**
	 * 标题 *
	 *  （并非一定会用到）
	 *
	 * @var string $_title
	 */
	protected $_title = '';
	
	/**
	 * 是否启用 *
	 *  （并非一定会用到）
	 *
	 * @var bool $_enabled
	 */
	protected $_enabled = true;
	
	/**
	 * 附加参数 *
	 *  （并非一定会用到）
	 *
	 * @var array $_param
	 */
	protected $_param = [];
	
	
	/**
	 * ItemKeys constructor.
	 *
	 * @param        $parent
	 */
	public function __construct($parent = null) {
		$this->_parent = $parent;
		
		$this->init();
	}
	
	/**
	 * 初始化
	 */
	public function init() {
		$this->initNameInfo();
		
		// 初始化迭代器游标
		$this->_position = 0;
		
		$this->_weight = self::$_DEFAULT_WEIGHT;
		$this->_level = 0;
		$this->_id = uniqid();
		$this->_title = '';
		$this->_enabled = true;
		$this->_param = [];
	}
	
	/**
	 * 类说明初始化
	 */
	public function initNameInfo() {
		$this->name_info['name']  = self::class;
		$this->name_info['intro'] = '树节点';
	}
	
	/**************************************************
	 * Iterator 迭代器方法实现
	 **************************************************/
	
	/**
	 * @inheritDoc
	 */
	public function current() {
		$_key = $this->key();
		
		// return $this->_children[$_key] ?? null;
		return $this->get($_key);
	}
	
	/**
	 * @inheritDoc
	 */
	public function next() {
		++$this->_position;
	}
	
	/**
	 * @inheritDoc
	 */
	public function key() {
		$_keys = array_keys($this->_children);
		
		if ($this->_position >= count($_keys)) {
			return null;
		}
		
		$_key = $_keys[$this->_position];
		
		return $_key;
	}
	
	/**
	 * @inheritDoc
	 */
	public function valid() {
		$_keys = array_keys($this->_children);
		
		if ($this->_position >= count($_keys)) {
			return false;
		}
		
		$_key = $_keys[$this->_position];
		
		return isset($this->_children[$_key]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function rewind() {
		$this->_position = 0;
	}
	
	/**************************************************
	 * ArrayAccess 方法实现
	 **************************************************/
	
	/**
	 * @inheritDoc
	 */
	public function offsetExists($offset) {
		return $this->has($offset);
	}
	
	/**
	 * @inheritDoc
	 */
	public function offsetGet($offset) {
		return $this->get($offset);
	}
	
	/**
	 * @inheritDoc
	 */
	public function offsetSet($offset, $value) {
		return $this->set($offset, $value);
	}
	
	/**
	 * @inheritDoc
	 */
	public function offsetUnset($offset) {
		if (is_numeric($offset)) {
			$this->delete($offset);
		} elseif (is_string($offset)) {
			$this->remove($offset);
		}
	}
	
	/**************************************************
	 * 节点操作
	 **************************************************/
	
	/**
	 * 元素个数
	 *
	 * @return int
	 */
	public function count() {
		return count($this->_children);
	}
	
	/**
	 * 获取子节点
	 *
	 * @return array
	 */
	public function children(): array {
		return $this->_children;
	}
	
	/**
	 * 获取item
	 *
	 * @param int      $index
	 * @param TreeNode $value
	 * @return $this|TreeNode
	 */
	public function item(int $index, TreeNode $value = null) {
		if ($value === null) {
			return $this->_children[$index] ?? null;
		} else {
			$this->_children[$index] = $value;
		}
		
		return $this;
	}
	
	/**
	 * 获取权值排序后item
	 *
	 * @param int $wi
	 * @return mixed|null
	 */
	public function witem(int $wi) {
		// 如果权值排序索引映射表为空 就做一次重新排序映射
		$this->_weight_index === null && $this->weight();
		
		if (!array_key_exists($wi, $this->_weight_index)) {
			return null;
		}
		
		// 索引映射
		$i = $this->_weight_index[$wi];
		
		return $this->_children[$i] ?? null;
	}
	
	/**
	 * 头部插入节点
	 *
	 * @param TreeNode|null $item
	 * @return $this
	 */
	public function unshift(TreeNode $item = null) {
		// 如果item传空就创建
		($item === null) && $item = new TreeNode($this->getParent());
		
		// 设置层级
		$item->_setLevel($this->getLevel() + 1);
		
		// 插入
		array_unshift($this->_children, $item);
		
		return $this;
	}
	
	/**
	 * 添加节点
	 *
	 * @param TreeNode|null $item
	 * @return $this
	 */
	public function add(TreeNode $item = null) {
		// 如果item传空就创建
		($item === null) && $item = new TreeNode($this);
		
		// 判断item父级是否为空
		($item->getParent() === null) && $item->_setParent($this);
		
		// 设置层级
		$item->_setLevel($this->getLevel() + 1);
		
		// 插入
		array_push($this->_children, $item);
		
		return $this;
	}
	
	/**
	 * 添加节点 别名add
	 *
	 * @param TreeNode|null $item
	 * @return $this
	 */
	public function push(TreeNode $item = null) {
		return $this->add($item);
	}
	
	/**
	 * 删除
	 *
	 * @param int $index
	 * @return $this
	 */
	public function del(int $index) {
		// 删除
		array_splice($this->_children, $index, 1);
		
		// 清空权值映射表 再用时会重新排序映射
		$this->_weight_index = null;
		
		return $this;
	}
	
	public function delete(int $index) {
		return $this->del($index);
	}
	
	/**
	 * 构建权值排序索引表
	 *  _weight_index
	 *
	 * @return $this
	 */
	public function weight() {
		$this->_weight_index = [];
		$tmp = [];
		
		// 生成权值索引
		foreach ($this->_children as $k => $item) {
			/** @var $item TreeNode */
			$tmp[$k] = $item->getWeight();
		}
		
		// 根据权重值排序
		asort($tmp);
		
		// 只保留索引
		$this->_weight_index = array_keys($tmp);
		
		return $this;
	}
	
	/**
	 * 移除
	 *
	 * @param string $key
	 * @return $this
	 */
	public function remove(string $key) {
		unset($this->_children[$key]);
		
		return $this;
	}
	
	/**
	 * 清空
	 *
	 * @param string $key
	 * @return $this
	 */
	public function clear() {
		$this->_children = [];
		$this->_position = 0;
		
		return $this;
	}
	
	/**
	 * 获取
	 *
	 * @param string $key
	 * @return mixed|null
	 */
	public function get(string $key) {
		$v = $this->has($key) ? $this->_children[$key] : null;
		
		return $v;
	}
	
	/**
	 * 是否存在
	 *
	 * @param string $key
	 * @return bool
	 */
	public function has(string $key): bool {
		return array_key_exists($key, $this->_children);
	}
	
	/**
	 * 设置
	 *
	 * @param string   $key
	 * @param TreeNode $item
	 * @return $this
	 */
	public function set(string $key, TreeNode $item) {
		// 如果item传空就创建
		($item === null) && $item = new TreeNode($this);
		
		// 判断item父级是否为空
		($item->getParent() === null) && $item->_setParent($this);
		
		// 设置key = value
		$this->_children[$key] = $item;
		
		// 设置层级
		$item->_setLevel($this->getLevel() + 1);
		
		return $this;
	}
	
	/**************************************************
	 * 数据操作
	 **************************************************/
	
	/**
	 * 获取and缓存 data
	 *
	 * @param    $value
	 * @return $this|TreeNode
	 */
	public function data($value = null) {
		if ($value === null) {
			return $this->_data ?? null;
		} else {
			$this->_data = $value;
		}
		
		return $this;
	}
	
	/**************************************************
	 * foreach map
	 **************************************************/
	
	/**
	 * 遍历
	 *  forEach(function ($item, $i, $obj) {
	 *      $item->data = 123;
	 *  })
	 *
	 * @param \Closure $func
	 */
	public function forEach(\Closure $func) {
		foreach ($this->_children as $k => &$item) {
			$re = call_user_func_array($func, [&$item, $k, $this]);
			if ($re === false) {
				break;
			}
		}
	}
	
	/**
	 * 遍历
	 *  $re = map(function ($item, $i, $obj) {
	 *      $item->data = 123;
	 *
	 *      return $item;
	 *  })
	 *
	 * @param \Closure $func
	 * @return array
	 */
	public function map(\Closure $func) {
		$_arr = $this->_children;
		
		foreach ($_arr as $k => $item) {
			$re = call_user_func_array($func, [$item, $k, $this]);
			if ($re === false) {
				break;
			}
			
			$arr[$k] = $re;
		}
		
		return $_arr;
	}
	
	/**
	 * 遍历（权重）
	 *  wForEach(function ($item, $i, $obj) {
	 *      $item->data = 123;
	 *  })
	 *
	 * @param \Closure $func
	 */
	public function wForEach(\Closure $func) {
		// 如果权值排序索引映射表为空 就做一次重新排序映射
		$this->_weight_index === null && $this->weight();
		
		foreach ($this->_weight_index as $i => $index) {
			$item = &$this->_children[$index];
			
			$re = call_user_func_array($func, [&$item, $index, $this]);
			if ($re === false) {
				break;
			}
		}
	}
	
	/**
	 * 智能读取
	 *  格式：$link = 'a.b'; 会直接按层级输出（层级a -> 层级b）
	 *
	 * @param string $link
	 * @param bool   $notExistCreate
	 * @return mixed|TreeNode|null
	 */
	public function smartGetter($link = '', $notExistCreate = true) {
		if (empty($path)) {
			return $this;
		}
		
		$_t = $this;
		$_p = explode('.', $link);
		foreach ($_p as $key) {
			if (!$_t->has($key)) {
				if ($notExistCreate) {
					$_class = self::class; // todo: 待验证
					$_item = new $_class;
					$_t->set($key, $_item);
					
					$_t = $_item;
				} else {
					return null;
				}
			} else {
				$_t = $_t->get($key);
			}
		}
		
		return $_t;
	}
	
	/**************************************************
	 * getter setter
	 **************************************************/
	
	/**
	 * 获取父级
	 *
	 * @return TreeNode|mixed
	 */
	public function getParent() {
		return $this->_parent;
	}
	
	/**
	 * @param TreeNode $parent
	 */
	public function _setParent(TreeNode $parent) {
		$this->_parent = $parent;
	}
	
	/**
	 * 获取数据
	 *
	 * @return mixed
	 */
	public function getData() {
		return $this->_data;
	}
	
	/**
	 * @return string
	 */
	public function getTitle(): string {
		return $this->_title;
	}
	
	/**
	 * @param string $title
	 */
	public function setTitle(string $title) {
		$this->_title = $title;
	}
	
	/**
	 * @return int
	 */
	public function getLevel(): int {
		return $this->_level;
	}
	
	/**
	 * @param int $level
	 */
	public function _setLevel(int $level) {
		$this->_level = $level;
	}
	
	/**
	 * @return int
	 */
	public function getWeight(): int {
		return $this->_weight;
	}
	
	/**
	 * @param int $weight
	 */
	public function setWeight(int $weight) {
		$this->_weight = $weight;
	}
	
	/**
	 * @return array
	 */
	public function getWeightIndex(): array {
		return $this->_weight_index;
	}
	
	/**
	 * @param array $weight_index
	 */
	public function _setWeightIndex(array $weight_index) {
		$this->_weight_index = $weight_index;
	}
	
	/**
	 * @return array
	 */
	public function getChildren(): array {
		return $this->_children;
	}
	
	/**
	 * @param array $children
	 */
	public function _setChildren(array $children) {
		$this->_children = $children;
	}
	
	/**
	 * @return bool
	 */
	public function isEnabled(): bool {
		return $this->_enabled;
	}
	
	/**
	 * @param bool $enabled
	 */
	public function setEnabled(bool $enabled) {
		$this->_enabled = $enabled;
	}
	
	/**
	 * 指定key项是否启用
	 *
	 * @param $key
	 * @return bool
	 */
	public function isEnabledKey($key): bool {
		/** @var FactoryCacheTree $item */
		$item = $this->get($key);
		if (empty($item)) {
			return false;
		}
		
		return $item->isEnabled();
	}
	
	/**
	 * 设置指定key项是否启用
	 *
	 * @param      $key
	 * @param bool $enabled
	 * @return $this
	 */
	public function setEnabledKey($key, bool $enabled) {
		/** @var FactoryCacheTree $item */
		$item = $this->get($key);
		if (empty($item)) {
			return $this;
		}
		
		$item->setEnabled($enabled);
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getParam(): array {
		return $this->_param;
	}
	
	/**
	 * @param array $param
	 */
	public function setParam(array $param) {
		$this->_param = $param;
	}
	
	/**
	 * @return int|string
	 */
	public function getId() {
		return $this->_id;
	}
	
	/**
	 * @param int|string $id
	 */
	public function setId($id) {
		$this->_id = $id;
	}
	
	/**
	 * 返回父级id
	 *
	 * @param int $rootReturn
	 * @return int|string
	 */
	public function getPid($rootReturn = 0) {
		try {
			if (empty($this->getParent()) ||
			    !method_exists($this->getParent(), 'getId')) {
				return $rootReturn;
			}
			
			return $this->getParent()->getId();
		} catch (\Exception $e) {
			return $rootReturn;
		}
	}
	
}