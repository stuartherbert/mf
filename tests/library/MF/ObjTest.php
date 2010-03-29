<?php

__mf_init_tests('Obj');

class MF_Obj_Tests extends PHPUnit_Framework_TestCase
{
	public function setup()
        {
                MF_Obj_MixinsManager::destroy();

                $this->fixture = new Test_ObjExt();
                __mf_extend('Test_ObjExt', 'Test_Obj_ExtMixin');
        }

        public function testCanGetFakeMember()
        {
                $this->assertEquals('world', $this->fixture->planet);
        }

        public function testCanSetFakeMember()
        {
                // entry conditions
                $this->assertEquals('world', $this->fixture->planet);

                // change state
                $this->fixture->planet = 'pluto';

                // retest
                $this->assertEquals('pluto', $this->fixture->planet);
        }

        public function testCanIssetFakeMember()
        {
                // entry conditions
                $this->assertEquals('world', $this->fixture->planet);

                // retest
                $this->assertTrue(isset($this->fixture->planet));
        }

        public function testCanUnsetFakeMember()
        {
                // entry conditions
                $this->assertEquals('world', $this->fixture->planet);

                // change state
                unset($this->fixture->planet);

                // retest
                $this->assertFalse(isset($this->fixture->planet));
                $this->assertNull($this->fixture->planet);
        }

        public function testCannotAccessNonExistentMember()
        {
                $pass = false;
                try
                {
                        $dummy = $this->fixture->doesNotExist;
                }
                catch (MF_Obj_E_NoSuchProperty $e)
                {
                        $pass = true;
                }
                $this->assertTrue($pass);
        }

        public function testCanAddAMixin()
        {
                // entry conditions
                $this->assertEquals('Test_ObjExt', get_class($this->fixture));
                $this->assertEquals(1, $this->fixture->mixinCount);

                // change state
                __mf_extend('Test_ObjBase', 'Test_Obj_BaseMixin');

                // retest
                $this->assertEquals(2, $this->fixture->mixinCount);
        }

        public function testCanGetMixinPropertyFromExtendedBaseClass()
        {
                // entry conditions
                $this->assertEquals('Test_ObjExt', get_class($this->fixture));
                $this->assertEquals(1, $this->fixture->mixinCount);

                // change state
                __mf_extend('Test_ObjBase', 'Test_Obj_BaseMixin');

                // retest
                $this->assertEquals(2, $this->fixture->mixinCount);
                $this->assertEquals('gold', $this->fixture->metal);
        }

        public function testCanGetMixinProperty()
        {
                $this->assertEquals('fred', $this->fixture->mixinProp);
        }

        public function testCanSetMixinProperty()
        {
                // entry conditions
                $this->assertEquals(1, $this->fixture->mixinCount);
                $this->assertEquals('fred', $this->fixture->mixinProp);

                // change state
                $this->fixture->mixinProp = 'harry';

                // retest
                $this->assertEquals('harry', $this->fixture->mixinProp);
        }

        public function testEachMixinObjUniqueToExtendedObject()
        {
                $obj = new Test_Obj2();

                // entry conditions
                $this->assertEquals('fred', $this->fixture->mixinProp);
                $this->assertTrue  ($obj->extendsObj);
                $this->assertFalse ($obj->hasMixins());

                // change state
                __mf_extend('Test_Obj2', 'Test_Obj_ExtMixin');

                // retest to prove object extended
                $this->assertEquals('fred', $obj->mixinProp);

                // change state again
                $this->fixture->mixinProp = 'harry';
                $obj->mixinProp          = 'larry';

                // retest to prove each mixin has separate state
                $this->assertEquals('harry', $this->fixture->mixinProp);
                $this->assertEquals('larry', $obj->mixinProp);
        }

        public function testCanGetDecoratorProperty()
        {
                $decorator = new Test_Obj_Decorator();

                // change state
                $this->fixture->addDecorator($decorator);

                // retest
                $this->assertEquals('alice', $this->fixture->decoratorProp);
                $this->assertEquals('lisa',  $this->fixture->name);
        }

        public function testCanSetDecoratorProperty()
        {
                $decorator = new Test_Obj_Decorator();
                $this->fixture->addDecorator($decorator);

                // entry conditions
                $this->assertEquals('alice', $this->fixture->decoratorProp);

                // change state
                $this->fixture->decoratorProp = 'katie';

                // retest
                $this->assertEquals('katie', $this->fixture->decoratorProp);
                $this->assertEquals('katie', $decorator->decoratorProp);
        }

        public function testCanCallSameMethodOnAllMixins()
        {
                $this->assertEquals('Test_ObjExt', get_class($this->fixture));
                __mf_extend('Test_ObjBase', 'Test_Obj_BaseMixin');

                // entry conditions
                $this->assertEquals(2, $this->fixture->mixinCount);

                // change state
                $result = $this->fixture->validateCalled();

                // test results
                $this->assertEquals(array('Test_Obj_ExtMixin', 'Test_Obj_BaseMixin'), $result);
        }

        public function testCanCallSameMethodWhenMultipleMixinsPerClass()
        {
                $this->assertEquals('Test_ObjExt', get_class($this->fixture));
                __mf_extend('Test_ObjBase', 'Test_Obj_BaseMixin');
                __mf_extend('Test_ObjExt', 'Test_Obj_ExtMixin2');

                // entry conditions
                $this->assertEquals(3, $this->fixture->mixinCount);

                // change state
                $result = $this->fixture->validateCalled();

                // test results
                $this->assertEquals(array('Test_Obj_ExtMixin', 'Test_Obj_ExtMixin2', 'Test_Obj_BaseMixin'), $result);

        }
}
 
?>