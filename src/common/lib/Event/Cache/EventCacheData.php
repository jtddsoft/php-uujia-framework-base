<?php


namespace uujia\framework\base\common\lib\Event\Cache;

use uujia\framework\base\common\consts\EventConst;
use uujia\framework\base\common\lib\Base\BaseClass;
use uujia\framework\base\common\lib\Utils\Json;

/**
 * Class EventCacheData
 *
 * @package uujia\framework\base\common\lib\Event\Cache
 */
class EventCacheData extends BaseClass implements EventCacheDataInterface {
	
	/**
	 * 服务器名称
	 *  例如：main
	 *
	 * @var string
	 */
	protected $_serverName = '';
	
	/**
	 * 服务类型
	 *  例如：event
	 *
	 * @var string
	 */
	protected $_serverType = '';
	
	/**
	 * 本地执行的完整类名
	 *
	 * @var string
	 */
	protected $_classNameSpace = '';
	
	/**
	 * 触发时的附加参数
	 *
	 * @var array
	 */
	protected $_param = [];
	
	
	/**
	 * EventCacheData constructor.
	 */
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * 复位 属性归零
	 *
	 * @param array $exclude
	 *
	 * @return $this
	 */
	public function reset($exclude = []) {
		(!in_array('serverName', $exclude)) && $this->_serverName = '';
		(!in_array('serverType', $exclude)) && $this->_serverType = '';
		(!in_array('classNameSpace', $exclude)) && $this->_classNameSpace = '';
		(!in_array('param', $exclude)) && $this->_param = [];
		
		return parent::reset($exclude);
	}
	
	/**************************************************************
	 * data
	 **************************************************************/
	
	/**
	 * 加载缓存数据
	 *
	 * @param array $cacheData
	 * @param bool  $isReset
	 *
	 * @return $this
	 */
	public function load($cacheData = [], $isReset = true) {
		$isReset && $this->reset();
		
		$this->setServerName($cacheData[EventConst::CACHE_SP_SERVERNAME] ?? '')
		     ->setServerType($cacheData[EventConst::CACHE_SP_SERVERTYPE] ?? '')
		     ->setClassNameSpace($cacheData[EventConst::CACHE_SP_CLASSNAMESPACE] ?? '')
		     ->setParam($cacheData[EventConst::CACHE_SP__PARAM] ?? []);
		
		return $this;
	}
	
	/**
	 * 载入缓存数据
	 *
	 * @param array|string $cacheData
	 * @param bool         $isReset
	 *
	 * @return $this
	 */
	public function parse($cacheData, $isReset = true) {
		$isReset && $this->reset();
		
		if (is_string($cacheData)) {
			if (!Json::isJson($cacheData)) {
				return $this;
			}
			
			$_dataCache = Json::decode($cacheData);
		} elseif (is_array($cacheData)) {
			$_dataCache = $cacheData;
		} else {
			return $this;
		}
		
		return $this->load($_dataCache, false);
	}
	
	/**************************************************************
	 * get set
	 **************************************************************/
	
	/**
	 * @return string
	 */
	public function getServerName(): string {
		return $this->_serverName;
	}
	
	/**
	 * @param string $serverName
	 *
	 * @return $this
	 */
	public function setServerName(string $serverName) {
		$this->_serverName = $serverName;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getServerType(): string {
		return $this->_serverType;
	}
	
	/**
	 * @param string $serverType
	 *
	 * @return $this
	 */
	public function setServerType(string $serverType) {
		$this->_serverType = $serverType;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getClassNameSpace(): string {
		return $this->_classNameSpace;
	}
	
	/**
	 * @param string $classNameSpace
	 *
	 * @return $this
	 */
	public function setClassNameSpace(string $classNameSpace) {
		$this->_classNameSpace = $classNameSpace;
		
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
	 *
	 * @return $this
	 */
	public function setParam(array $param) {
		$this->_param = $param;
		
		return $this;
	}
	
	
}