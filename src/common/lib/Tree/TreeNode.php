<?php


namespace uujia\framework\base\common\lib\Tree;


use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\traits\ResultTrait;

/**
 * Class ItemKeys
 *
 * @package uujia\framework\base\common\lib\Tree
 */
class TreeNode extends BaseClass implements \Iterator, \ArrayAccess {
	use ResultTrait;
	
	// 默认权重
	const DEFAULT_WEIGHT = 100;
	
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
	 * key别名
	 */
	protected $_aliasKeys = [];
	
	/**
	 * key映射
	 */
	protected $_asKeys = [];
	
	/**
	 * 迭代器游标位置
	 */
	protected $_position = 0;
	
	/**
	 * 权重排序索引
	 *
	 * @var array|null $_weightIndex
	 */
	protected $_weightIndex = null;
	
	/**
	 * 迭代时启用权重
	 *
	 * @var bool $_iteratorWeight
	 */
	protected $_iteratorWeight = false;
	
	/**
	 * 缓存最后一次实例化的TreeNode对象
	 *  一般为add或unshift操作时新生产的TreeNode对象
	 *  以便后续对新对象自定义操作
	 *
	 * @var TreeNode $_lastNewItem
	 */
	protected $_lastNewItem = null;
	
	/**
	 * 缓存最后一次set实例化的TreeNode对象
	 *  一般为set操作时新生产的TreeNode对象
	 *  以便后续对新对象自定义操作
	 *
	 * @var TreeNode $_lastSetItem
	 */
	protected $_lastSetItem = null;
	
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
	 * 所属key
	 *
	 * @var int|string $_key
	 */
	protected $_key = '';
	
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
		
		parent::__construct();
	}
	
	/**
	 * 初始化
	 * @return $this
	 */
	public function init() {
		parent::init();
		
		// 初始化迭代器游标
		$this->_position = 0;
		
		$this->_weight = self::DEFAULT_WEIGHT;
		$this->_level = 0;
		$this->_key = '';
		$this->_id = uniqid();
		$this->_title = '';
		$this->_enabled = true;
		$this->_param = [];
		
		return $this;
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
		if ($this->isIteratorWeight()) {
			$_key = $this->wkeys();
		} else {
			$_keys = array_keys($this->_children);
			
			if ($this->_position >= count($_keys)) {
				return null;
			}
			
			$_key = $_keys[$this->_position];
		}
		
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
	public function &children(): array {
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
		$this->_weightIndex === null && $this->weight();
		
		if (!array_key_exists($wi, $this->_weightIndex)) {
			return null;
		}
		
		// 索引映射
		$i = $this->_weightIndex[$wi];
		
		return $this->_children[$i] ?? null;
	}
	
	/**
	 * 获取权值排序后key
	 *
	 * @param int $wi
	 * @return mixed|null
	 */
	public function wkeys() {
		// 如果权值排序索引映射表为空 就做一次重新排序映射
		$this->_weightIndex === null && $this->weight();
		
		return $this->_weightIndex;
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
		$this->_setLastNewItem($item);
		
		// 判断item父级是否为空
		($item->getParent() === null) && $item->_setParent($this);
		
		// 设置层级
		$item->_setLevel($this->getLevel() + 1);
		
		// 设置key
		$item->_setKey(0);
		
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
		$this->_setLastNewItem($item);
		
		// 判断item父级是否为空
		($item->getParent() === null) && $item->_setParent($this);
		
		// 设置层级
		$item->_setLevel($this->getLevel() + 1);
		
		// 设置key
		$item->_setKey($this->count());
		
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
		$this->_weightIndex = null;
		
		return $this;
	}
	
	public function delete(int $index) {
		return $this->del($index);
	}
	
	/**
	 * 构建权值排序索引表
	 *  _weightIndex
	 *
	 * @return $this
	 */
	public function weight() {
		$this->_weightIndex = [];
		$tmp = [];
		
		// 生成权值索引
		foreach ($this->_children as $k => $item) {
			/** @var $item TreeNode */
			$tmp[$k] = $item->getWeight();
		}
		
		// 根据权重值排序
		asort($tmp);
		
		// 只保留索引
		$this->_weightIndex = array_keys($tmp);
		
		return $this;
	}
	
	/**
	 * 移除
	 *
	 * @param string $key
	 * @return $this
	 */
	public function remove(string $key) {
		$this->hasAlias($key) && $key = $this->getAlias($key);
		
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
		$this->hasAlias($key) && $key = $this->getAlias($key);
		
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
		$this->hasAlias($key) && $key = $this->getAlias($key);
		
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
		$this->hasAlias($key) && $key = $this->getAlias($key);
		
		// 如果item传空就创建
		($item === null) && $item = new TreeNode($this);
		$this->_setLastSetItem($item);
		
		// 判断item父级是否为空
		($item->getParent() === null) && $item->_setParent($this);
		
		// 设置key = value
		$this->_children[$key] = $item;
		
		// 设置层级
		$item->_setLevel($this->getLevel() + 1);
		
		// 设置key
		$item->_setKey($key);
		
		return $this;
	}
	
	/**************************************************
	 * 别名key
	 * key键名为a，b是a的别名，用b先翻译成a再去访问
	 **************************************************/
	
	/**
	 * 获取别名key列表
	 *
	 * @return array
	 */
	public function &aliasKeys() {
		return $this->_aliasKeys;
	}
	
	/**
	 * 清空别名列表
	 *
	 * @return $this
	 */
	public function clearAlias() {
		$this->_aliasKeys = [];
		
		return $this;
	}
	
	/**
	 * 根据别名获取真实key
	 *
	 * @param string $a
	 *
	 * @return string|null
	 */
	public function getAlias(string $a) {
		if (!$this->hasAlias($a)) {
			return null;
		}
		
		return $this->_aliasKeys[$a];
	}
	
	/**
	 * 别名是否存在
	 *
	 * @param string $a
	 *
	 * @return bool
	 */
	public function hasAlias(string $a): bool {
		return array_key_exists($a, $this->_aliasKeys);
	}
	
	/**
	 * 配置别名
	 *
	 * @param string|array $a
	 * @param string       $k
	 *
	 * @return $this
	 */
	public function setAlias($a, string $k = '') {
		if (is_array($a)) {
			foreach ($a as $aKey => $aValue) {
				$this->_aliasKeys[$aKey] = $aValue;
			}
		} else {
			$this->_aliasKeys[$a] = $k;
		}
		
		return $this;
	}
	
	/**
	 * 移除别名
	 *
	 * @param string $a
	 *
	 * @return $this
	 */
	public function removeAlias(string $a) {
		unset($this->_aliasKeys[$a]);
		
		return $this;
	}
	
	/**************************************************
	 * 映射key
	 * key键名为b，a为b的真实名称映射，key虽是b但应该用a去访问
	 **************************************************/
	
	/**
	 * 获取映射key列表
	 *
	 * @return array
	 */
	public function &asKeys() {
		return $this->_asKeys;
	}
	
	/**
	 * 清空映射列表
	 *
	 * @return $this
	 */
	public function clearAs() {
		$this->_asKeys = [];
		
		return $this;
	}
	
	/**
	 * 根据映射获取真实key
	 *  用于对key重新取名 但表示含义还是原有名称
	 *  例如：一个class按正常情况只会在容器中存在1个实例 这是由key决定的唯一的
	 *       如果想存在多个就需要多个不同名称的key 但仍表示这个class
	 *       容器在自动注入时仍会按真实class名称创建实例
	 *
	 * @param string $a
	 *
	 * @return string|null
	 */
	public function getAs(string $a) {
		if (!$this->hasAs($a)) {
			return null;
		}
		
		return $this->_asKeys[$a];
	}
	
	/**
	 * 映射Key是否存在
	 *
	 * @param string $a
	 *
	 * @return bool
	 */
	public function hasAs(string $a): bool {
		return array_key_exists($a, $this->_asKeys);
	}
	
	/**
	 * 配置映射
	 *
	 * @param string|array $a
	 * @param string       $k
	 *
	 * @return $this
	 */
	public function setAs($a, string $k = '') {
		if (is_array($a)) {
			foreach ($a as $aKey => $aValue) {
				$this->_asKeys[$aKey] = $aValue;
			}
		} else {
			$this->_asKeys[$a] = $k;
		}
		
		return $this;
	}
	
	/**
	 * 移除映射
	 *
	 * @param string $a
	 *
	 * @return $this
	 */
	public function removeAs(string $a) {
		unset($this->_asKeys[$a]);
		
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
	 *  forEach(function ($item, $k, $me, $params) {
	 *      $item->data = 123;
	 *  })
	 *
	 * @param \Closure $func
	 * @param array    $params
	 *
	 * @return $this
	 */
	public function forEach(\Closure $func, $params = []) {
		foreach ($this->_children as $k => &$item) {
			$re = call_user_func_array($func, [&$item, $k, $this, $params]);
			if ($re === false) {
				break;
			}
		}
		
		return $this;
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
	 * @param array    $params
	 * @return array
	 */
	public function map(\Closure $func, $params = []) {
		$_arr = $this->_children;
		
		foreach ($_arr as $k => $item) {
			$re = call_user_func_array($func, [$item, $k, $this, $params]);
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
	 * @param array    $params
	 */
	public function wForEach(\Closure $func, $params = []) {
		// 如果权值排序索引映射表为空 就做一次重新排序映射
		$this->_weightIndex === null && $this->weight();
		
		foreach ($this->_weightIndex as $i => $index) {
			$item = &$this->_children[$index];
			
			$re = call_user_func_array($func, [&$item, $index, $this, $params]);
			if ($re === false) {
				break;
			}
		}
	}
	
	/**
	 * @return iterable
	 */
	public function wForEachI(): iterable {
		// 如果权值排序索引映射表为空 就做一次重新排序映射
		$this->_weightIndex === null && $this->weight();
		
		foreach ($this->_weightIndex as $i => $index) {
			$item = $this->_children[$index];
			
			yield $item;
		}
	}
	
	/**
	 * @return iterable
	 */
	public function wForEachIK(): iterable {
		// 如果权值排序索引映射表为空 就做一次重新排序映射
		$this->_weightIndex === null && $this->weight();
		
		foreach ($this->_weightIndex as $i => $index) {
			yield $i => $this->_children[$index];
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
					$this->_setLastSetItem($_item);
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
	 * @return $this
	 */
	public function _setParent(TreeNode $parent) {
		$this->_parent = $parent;
		
		return $this;
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
	 * @return $this
	 */
	public function setTitle(string $title) {
		$this->_title = $title;
		
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getLevel(): int {
		return $this->_level;
	}
	
	/**
	 * @param int $level
	 * @return $this
	 */
	public function _setLevel(int $level) {
		$this->_level = $level;
		
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getWeight(): int {
		return $this->_weight;
	}
	
	/**
	 * @param int $weight
	 * @return $this
	 */
	public function setWeight(int $weight) {
		$this->_weight = $weight;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getWeightIndex(): array {
		return $this->_weightIndex;
	}
	
	/**
	 * @param array $weight_index
	 * @return $this
	 */
	public function _setWeightIndex(array $weight_index) {
		$this->_weightIndex = $weight_index;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function &getChildren(): array {
		return $this->_children;
	}
	
	/**
	 * @param array $children
	 * @return $this
	 */
	public function _setChildren(array $children) {
		$this->_children = $children;
		
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isEnabled(): bool {
		return $this->_enabled;
	}
	
	/**
	 * @param bool $enabled
	 * @return $this
	 */
	public function setEnabled(bool $enabled) {
		$this->_enabled = $enabled;
		
		return $this;
	}
	
	/**
	 * 指定key项是否启用
	 *
	 * @param $key
	 * @return bool
	 */
	public function isEnabledKey($key): bool {
		/** @var TreeFunc $item */
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
		/** @var TreeFunc $item */
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
	public function &getParam(): array {
		return $this->_param;
	}
	
	/**
	 * @param array $param
	 * @return $this
	 */
	public function setParam(array $param) {
		$this->_param = $param;
		
		return $this;
	}
	
	/**
	 * @param $value
	 * @return $this
	 */
	public function addParam($value) {
		$this->_param[] = $value;
		
		return $this;
	}
	
	/**
	 * @param $key
	 * @param $value
	 * @return $this
	 */
	public function addKeyParam($key, $value) {
		$this->_param[$key][] = $value;
		
		return $this;
	}
	
	/**
	 * @return int|string
	 */
	public function getId() {
		return $this->_id;
	}
	
	/**
	 * @param int|string $id
	 * @return $this
	 */
	public function setId($id) {
		$this->_id = $id;
		
		return $this;
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
	
	/**
	 * @return bool
	 */
	public function isIteratorWeight(): bool {
		return $this->_iteratorWeight;
	}
	
	/**
	 * @param bool $iteratorWeight
	 * @return $this
	 */
	public function setIteratorWeight(bool $iteratorWeight) {
		$this->_iteratorWeight = $iteratorWeight;
		
		return $this;
	}
	
	/**
	 * @return TreeNode
	 */
	public function getLastNewItem() {
		return $this->_lastNewItem;
	}
	
	/**
	 * @param TreeNode $lastNewItem
	 * @return $this
	 */
	public function _setLastNewItem($lastNewItem) {
		$this->_lastNewItem = $lastNewItem;
		
		return $this;
	}
	
	/**
	 * @return TreeNode
	 */
	public function getLastSetItem() {
		return $this->_lastSetItem;
	}
	
	/**
	 * @param TreeNode $lastSetItem
	 * @return $this
	 */
	public function _setLastSetItem($lastSetItem) {
		$this->_lastSetItem = $lastSetItem;
		
		return $this;
	}
	
	/**
	 * @return string|int
	 */
	public function getKey() {
		return $this->_key;
	}
	
	/**
	 * @param string|int $key
	 *
	 * @return $this
	 */
	public function _setKey($key) {
		$this->_key = $key;
		
		return $this;
	}
	
}