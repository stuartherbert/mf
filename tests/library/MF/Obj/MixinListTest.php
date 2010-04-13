<?php

__mf_init_tests('Obj');

class MF_Obj_MixinListTest extends PHPUnit_Framework_TestCase
{
	public function setup()
        {
                MF_Obj_MixinsManager::destroy();

                $this->fixture = new Test_ObjExt();
                __mf_extend('Test_ObjExt', 'Test_Obj_ExtMixin');
        }

        public function testReturnsNullIfNoClassesForMethod()
        {
                // entry conditions
                $mixins = MF_Obj_MixinsManager::getMixinsFor('Test_ObjExt');

                // try and get a method that does not exist
                $this->assertNull($mixins->getClassnamesForMethod('doesNotExist'));
        }
}

?>