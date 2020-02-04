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
	 * 获取数据
	 *
	 * @return Data
	 */
	public function getData(): Data {
		return $this->_data;
	}
	
	
	
	
	
	
	
}
