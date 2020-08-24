<?php


namespace uujia\framework\base\common;

use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\MQ\MQCollection;

/**
 * Class MQ
 * MQ的集合
 *  （仅做对象汇总 并不能完全统一方法）
 *
 * @package uujia\framework\base\common
 */
class MQ extends BaseClass {
	
	/**
	 * @var MQCollection
	 */
	protected $_mqCollectionObj;
	
	/**
	 * MQ constructor.
	 *
	 * @param MQCollection $mqCollectionObj
	 */
	public function __construct(MQCollection $mqCollectionObj) {
		$this->_mqCollectionObj = $mqCollectionObj;
		
		parent::__construct();
	}
	
	/**
	 * 魔术方法
	 *  可直接访问MQCollection中方法
	 *
	 * @param $method
	 * @param $args
	 *
	 * @return $this|mixed
	 */
	public function __call($method, $args) {
		// 从MQCollection中查找方法
		if (is_callable([$this->getMqCollectionObj(), $method])) {
			return call_user_func_array([$this->getMqCollectionObj(), $method], $args);
		}
		
		// todo: 方法不存在
		$this->getMqCollectionObj()->error('方法不存在', 1000);
		
		return $this;
	}
	
	/**
	 * 获取MQ集合对象
	 *  getMqCollectionObj的别名
	 *
	 * @return MQCollection
	 */
	public function mqObj() {
		return $this->getMqCollectionObj();
	}
	
	/**
	 * @return MQCollection
	 */
	public function getMqCollectionObj() {
		return $this->_mqCollectionObj;
	}
	
	/**
	 * @param MQCollection $mqCollectionObj
	 *
	 * @return $this
	 */
	public function _setMqCollectionObj($mqCollectionObj) {
		$this->_mqCollectionObj = $mqCollectionObj;
		
		return $this;
	}
}