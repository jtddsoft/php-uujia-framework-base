<?php 
namespace uujia\framework\base\test\cache\proxy;

use uujia\framework\base\common\lib\Event\EventHandle;
use uujia\framework\base\common\lib\Annotation\{EventTrigger, EventListener};
// use uujia\framework\base\common\lib\Annotation\EventListener;
use uujia\framework\base\common\lib\Event\EventHandleInterface;
use uujia\framework\base\common\lib\Event\Name\EventName;
class uujia_framework_base_test_EventTest_5f3a9e294ff50 extends \uujia\framework\base\test\EventTest
{
    use \uujia\framework\base\common\lib\Aop\AopProxy;
    public function addBefore($a = [1, 2], $b = 's', $c = 1, $d = true) : EventHandle
    {
        return $this->_aopCall(function () use(&$a, &$b, &$c, &$d) {
            return parent::addBefore($a, $b, $c, $d);
        }, 'addBefore', func_get_args());
    }
    public function addAfter()
    {
        return $this->_aopCall(function () {
            return parent::addAfter();
        }, 'addAfter', func_get_args());
    }
    public function onAddBefore()
    {
        return $this->_aopCall(function () {
            return parent::onAddBefore();
        }, 'onAddBefore', func_get_args());
    }
    public function __construct(RunnerManagerInterface $runnerManagerObj, EventName $eventNameObj)
    {
        return $this->_aopCall(function () use(&$runnerManagerObj, &$eventNameObj) {
            return parent::__construct($runnerManagerObj, $eventNameObj);
        }, '__construct', func_get_args());
    }
    public function init()
    {
        return $this->_aopCall(function () {
            return parent::init();
        }, 'init', func_get_args());
    }
    public function initNameInfo()
    {
        return $this->_aopCall(function () {
            return parent::initNameInfo();
        }, 'initNameInfo', func_get_args());
    }
    public function parse($triggerName = '')
    {
        return $this->_aopCall(function () use(&$triggerName) {
            return parent::parse($triggerName);
        }, 'parse', func_get_args());
    }
    public function _trigger()
    {
        return $this->_aopCall(function () {
            return parent::_trigger();
        }, '_trigger', func_get_args());
    }
    public function handle()
    {
        return $this->_aopCall(function () {
            return parent::handle();
        }, 'handle', func_get_args());
    }
    public function triggerEventName($triggerName = '', $param = [])
    {
        return $this->_aopCall(function () use(&$triggerName, &$param) {
            return parent::triggerEventName($triggerName, $param);
        }, 'triggerEventName', func_get_args());
    }
    public function ten($triggerName = '', $param = [])
    {
        return $this->_aopCall(function () use(&$triggerName, &$param) {
            return parent::ten($triggerName, $param);
        }, 'ten', func_get_args());
    }
    public function triggerMethod($method = '', $param = [])
    {
        return $this->_aopCall(function () use(&$method, &$param) {
            return parent::triggerMethod($method, $param);
        }, 'triggerMethod', func_get_args());
    }
    public function tm($method = '', $param = [])
    {
        return $this->_aopCall(function () use(&$method, &$param) {
            return parent::tm($method, $param);
        }, 'tm', func_get_args());
    }
    public function getUuid() : string
    {
        return $this->_aopCall(function () {
            return parent::getUuid();
        }, 'getUuid', func_get_args());
    }
    public function setUuid(string $uuid)
    {
        return $this->_aopCall(function () use(&$uuid) {
            return parent::setUuid($uuid);
        }, 'setUuid', func_get_args());
    }
    public function getTriggerName() : string
    {
        return $this->_aopCall(function () {
            return parent::getTriggerName();
        }, 'getTriggerName', func_get_args());
    }
    public function setTriggerName(string $triggerName)
    {
        return $this->_aopCall(function () use(&$triggerName) {
            return parent::setTriggerName($triggerName);
        }, 'setTriggerName', func_get_args());
    }
    public function getRunnerManagerObj()
    {
        return $this->_aopCall(function () {
            return parent::getRunnerManagerObj();
        }, 'getRunnerManagerObj', func_get_args());
    }
    public function setRunnerManagerObj(RunnerManagerInterface $runnerManagerObj)
    {
        return $this->_aopCall(function () use(&$runnerManagerObj) {
            return parent::setRunnerManagerObj($runnerManagerObj);
        }, 'setRunnerManagerObj', func_get_args());
    }
    public function getEventNameObj()
    {
        return $this->_aopCall(function () {
            return parent::getEventNameObj();
        }, 'getEventNameObj', func_get_args());
    }
    public function setEventNameObj(EventName $eventNameObj)
    {
        return $this->_aopCall(function () use(&$eventNameObj) {
            return parent::setEventNameObj($eventNameObj);
        }, 'setEventNameObj', func_get_args());
    }
    public function getParam()
    {
        return $this->_aopCall(function () {
            return parent::getParam();
        }, 'getParam', func_get_args());
    }
    public function setParam(array $param)
    {
        return $this->_aopCall(function () use(&$param) {
            return parent::setParam($param);
        }, 'setParam', func_get_args());
    }
    public function isPropagationStopped() : bool
    {
        return $this->_aopCall(function () {
            return parent::isPropagationStopped();
        }, 'isPropagationStopped', func_get_args());
    }
    public function assignFromArray($arr)
    {
        return $this->_aopCall(function () use(&$arr) {
            return parent::assignFromArray($arr);
        }, 'assignFromArray', func_get_args());
    }
    public function assignToArray()
    {
        return $this->_aopCall(function () {
            return parent::assignToArray();
        }, 'assignToArray', func_get_args());
    }
    public function isStopped() : bool
    {
        return $this->_aopCall(function () {
            return parent::isStopped();
        }, 'isStopped', func_get_args());
    }
    public function setStopped(bool $stopped)
    {
        return $this->_aopCall(function () use(&$stopped) {
            return parent::setStopped($stopped);
        }, 'setStopped', func_get_args());
    }
    public function assign($obj)
    {
        return $this->_aopCall(function () use(&$obj) {
            return parent::assign($obj);
        }, 'assign', func_get_args());
    }
    public function reset($exclude = [])
    {
        return $this->_aopCall(function () use(&$exclude) {
            return parent::reset($exclude);
        }, 'reset', func_get_args());
    }
    public function di()
    {
        return $this->_aopCall(function () {
            return parent::di();
        }, 'di', func_get_args());
    }
    public function getDI()
    {
        return $this->_aopCall(function () {
            return parent::getDI();
        }, 'getDI', func_get_args());
    }
    public function getContainer()
    {
        return $this->_aopCall(function () {
            return parent::getContainer();
        }, 'getContainer', func_get_args());
    }
    public function _setContainer(ContainerInterface $container)
    {
        return $this->_aopCall(function () use(&$container) {
            return parent::_setContainer($container);
        }, '_setContainer', func_get_args());
    }
    public function getReflection() : Reflection
    {
        return $this->_aopCall(function () {
            return parent::getReflection();
        }, 'getReflection', func_get_args());
    }
    public function _setReflection(Reflection $reflection)
    {
        return $this->_aopCall(function () use(&$reflection) {
            return parent::_setReflection($reflection);
        }, '_setReflection', func_get_args());
    }
}