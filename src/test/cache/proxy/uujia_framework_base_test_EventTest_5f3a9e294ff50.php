<?php 
namespace uujia\framework\base\test\cache\proxy;

use uujia\framework\base\common\lib\Event\EventHandle;
use uujia\framework\base\common\lib\Annotation\EventTrigger;
use uujia\framework\base\common\lib\Annotation\EventListener;
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
}