<?php

__mf_init_tests('Obj');

class MF_Obj_ExtensibleTest extends PHPUnit_Framework_TestCase
{
	public function setup()
        {
                MF_Obj_MixinsManager::destroy();

                $this->fixture = new Test_ObjExt();
                __mf_extend('Test_ObjExt', 'Test_Obj_ExtMixin');
        }

        public function testCanAddAMixin()
        {
                // entry conditions
                MF_Obj_MixinsManager::destroy();
                // var_dump("Slate wiped clean; starting again");
                
                $this->fixture = new Test_ObjExt();
                $this->assertEquals('Test_ObjExt', get_class($this->fixture));
                $this->assertEquals(0, $this->fixture->mixinsCount);

                // change state
                __mf_extend('Test_ObjExt', 'Test_Obj_ExtMixin');
                $this->assertEquals(1, $this->fixture->mixinsCount);
                $this->assertEquals('Test_Obj_ExtMixin', $this->fixture->doSomethingInTheMixin());

                // we deliberately extend the base class of the fixture,
                // rather than the class of the fixture, as it is much
                // more of a torture test!
                __mf_extend('Test_ObjBase', 'Test_Obj_BaseMixin');
                // __mf_extend('Test_ObjExt', 'Test_Obj_BaseMixin');

                // retest
                $this->assertEquals(2, $this->fixture->mixinsCount);
                // $this->assertEquals($expectedMethods, $this->fixture->getMixinMethods());
                $this->assertEquals('Test_Obj_ExtMixin', $this->fixture->doSomethingInTheMixin());
                $this->assertEquals('Test_Obj_BaseMixin', $this->fixture->doSomethingInTheBaseMixin());

                // add in another mixin
                __mf_extend('Test_ObjExt', 'Test_Obj_ParentMixin');

                // retest
                $this->assertEquals(3, $this->fixture->mixinsCount);

                // now add in a mixin that's a child class of an existing
                // mixin that is in use
                __mf_extend('Test_ObjExt', 'Test_Obj_ChildMixin');

                // retest
                $this->assertEquals(4, $this->fixture->mixinsCount);
        }

        public function testCannotAddANonMixin()
        {
                // entry conditions
                $this->assertEquals('Test_ObjExt', get_class($this->fixture));
                $this->assertEquals(1, $this->fixture->mixinsCount);

                // attempt to change state
                try
                {
                        __mf_extend('Test_ObjBase', 'Test_Obj_Decorator');
                }
                catch (MF_PHP_E_ConstraintFailed $e)
                {
                        $this->assertTrue(true);
                }

                // we test a second time, to ensure 100% code coverage
                // of the code that checks such things
                try
                {
                        __mf_extend('Test_ObjBase', 'Test_Obj_Decorator');
                }
                catch (MF_PHP_E_ConstraintFailed $e)
                {
                        $this->assertTrue(true);
                }
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
                $this->assertEquals('hello', $this->fixture->pubVar);

                // retest
                $this->assertTrue(isset($this->fixture->planet));
                $this->assertTrue(isset($this->fixture->pubVar));
        }

        public function testCanUnsetFakeMember()
        {
                // entry conditions
                $this->assertEquals('world', $this->fixture->planet);
                $this->assertEquals('hello', $this->fixture->pubVar);

                // change state
                unset($this->fixture->planet);
                unset($this->fixture->pubVar);

                // retest
                $this->assertFalse(isset($this->fixture->planet));
                $this->assertNull($this->fixture->planet);
                $this->assertFalse(isset($this->fixture->pubVar));
                $this->assertNull($this->fixture->pubVar);
        }

        public function testCannotAccessNonExistentMember()
        {
                $this->assertNull($this->fixture->doesNotExist);
                /*
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
                 */
        }

        public function testCanGetMixinPropertyFromExtendedBaseClass()
        {
                // entry conditions
                $this->assertEquals('Test_ObjExt', get_class($this->fixture));
                $this->assertEquals(1, $this->fixture->mixinsCount);

                // change state
                __mf_extend('Test_ObjBase', 'Test_Obj_BaseMixin');

                // retest
                $this->assertEquals(2, $this->fixture->mixinsCount);
                $this->assertEquals('gold', $this->fixture->metal);
        }

        public function testCanGetMixinProperty()
        {
                $this->assertEquals('fred', $this->fixture->mixinProp);
        }

        public function testCanSetMixinProperty()
        {
                // entry conditions
                $this->assertEquals(1, $this->fixture->mixinsCount);
                $this->assertEquals('fred', $this->fixture->mixinProp);

                // change state
                $this->fixture->mixinProp = 'harry';

                // retest
                $this->assertEquals('harry', $this->fixture->mixinProp);
        }

        public function testCanIssetMixinProperty()
        {
                // entry conditions
                $this->assertEquals(1, $this->fixture->mixinsCount);
                $this->assertEquals('fred', $this->fixture->mixinProp);

                // retest
                $this->assertEquals(true, isset($this->fixture->mixinProp));
        }

        public function testCanUnsetMixinProperty()
        {
                // entry conditions
                $this->assertEquals(1, $this->fixture->mixinsCount);
                $this->assertEquals('fred', $this->fixture->mixinProp);

                // change state
                unset($this->fixture->mixinProp);

                // retest
                $this->assertEquals(false, $this->fixture->mixinProp);
                $this->assertNull($this->fixture->mixinProp);
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

        public function testCanCallSameMethodOnAllMixins()
        {
                $this->assertEquals('Test_ObjExt', get_class($this->fixture));
                __mf_extend('Test_ObjBase', 'Test_Obj_BaseMixin');

                // entry conditions
                $this->assertEquals(2, $this->fixture->mixinsCount);

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
                $decorator = new Test_Obj_Decorator();
                $this->fixture->addDecorator($decorator);

                // entry conditions
                $this->assertEquals(3, $this->fixture->mixinsCount);

                // change state
                $result = $this->fixture->validateCalled();

                // test results
                $this->assertEquals(array('Test_Obj_ExtMixin2', 'Test_Obj_ExtMixin', 'Test_Obj_BaseMixin', 'Test_Obj_Decorator'), $result);

        }

        public function testMixinCanAccessOrigObjProperties()
        {
                $this->assertEquals('Test_ObjExt', get_class($this->fixture));
                __mf_extend('Test_ObjExt', 'Test_Obj_ExtMixin2');

                $this->assertEquals('world', $this->fixture->getExtMixin2ProtVar());
        }

        public function testMixinCanSetOrigObjProperties()
        {
                $this->assertEquals('Test_ObjExt', get_class($this->fixture));
                __mf_extend('Test_ObjExt', 'Test_Obj_ExtMixin2');

                $this->assertEquals('world', $this->fixture->getExtMixin2ProtVar());

                $this->fixture->setExtMixin2ProtVar('helen');
                $this->assertEquals('helen', $this->fixture->getExtMixin2ProtVar());
                $this->assertEquals('helen', $this->fixture->planet);
        }

        public function testMixinCanTestOrigObjPropertiesForIsset()
        {
                $this->assertEquals('Test_ObjExt', get_class($this->fixture));
                __mf_extend('Test_ObjExt', 'Test_Obj_ExtMixin2');

                $this->assertEquals('world', $this->fixture->getExtMixin2ProtVar());

                $this->assertTrue($this->fixture->issetExtMixin2ProtVar());
        }

        public function testMixinCanUnsetOriObjProperties()
        {
                $this->assertEquals('Test_ObjExt', get_class($this->fixture));
                __mf_extend('Test_ObjExt', 'Test_Obj_ExtMixin2');

                $this->assertEquals('world', $this->fixture->getExtMixin2ProtVar());
                $this->assertTrue($this->fixture->issetExtMixin2ProtVar());

                $this->fixture->unsetExtMixin2ProtVar();

                $this->assertFalse($this->fixture->issetExtMixin2ProtVar());
                $this->assertFalse(isset($this->fixture->planet));
        }

        public function testMixinReturnsNullWhenAccessingNonExistantProperties()
        {
                $this->assertEquals('Test_ObjExt', get_class($this->fixture));
                __mf_extend('Test_ObjExt', 'Test_Obj_ExtMixin2');

                $this->assertNull($this->fixture->getExtMixin2NonVar());
        }

        public function testMixinThrowsExceptionWhenSettingNonExistantProperties()
        {
                $this->assertEquals('Test_ObjExt', get_class($this->fixture));
                __mf_extend('Test_ObjExt', 'Test_Obj_ExtMixin2');
                
                $caughtException = false;
                try
                {
                        $this->fixture->setExtMixin2NonVar('fred');
                }
                catch (Exception $e)
                {
                        if ($e instanceof MF_Obj_E_NoSuchProperty)
                        {
                                $caughtException = true;
                        }
                }
                $this->assertTrue($caughtException);
        }

        public function testMixinDoesNotThrowExceptionWhenTestingNonExistantProperties()
        {
                $this->assertEquals('Test_ObjExt', get_class($this->fixture));
                __mf_extend('Test_ObjExt', 'Test_Obj_ExtMixin2');

                $this->assertFalse($this->fixture->issetExtMixin2NonVar());

                $caughtException = false;
                try
                {
                        $this->fixture->unsetExtMixin2NonVar();
                }
                catch (Exception $e)
                {
                        $caughtException = true;
                }
                $this->assertFalse($caughtException);
        }

        public function testMixinThrowsExceptionWhenCallingNonExistantMethod()
        {
                $this->assertEquals('Test_ObjExt', get_class($this->fixture));
                __mf_extend('Test_ObjExt', 'Test_Obj_ExtMixin2');

                $caughtException = false;
                try
                {
                        $this->fixture->doSomethingInTheMixinThatDoesNotExist();
                }
                catch (MF_Obj_E_NoSuchMethod $e)
                {
                        $caughtException = true;
                }

                $this->assertTrue($caughtException);
        }

        public function testMixinCanCallMethodOnOrigObject()
        {
                $this->assertEquals('Test_ObjExt', get_class($this->fixture));
                __mf_extend('Test_ObjExt', 'Test_Obj_ExtMixin2');

                $this->assertEquals('harry', $this->fixture->callMethodOnOrigObject());
        }

        public function testThrowsExceptionOnInvalidMixin()
        {
                try
                {
                        $this->fixture->forceAnInvalidMixin();
                }
                catch (MF_PHP_E_ConstraintFailed $e)
                {
                        $this->assertTrue(true);
                }
        }
        // ================================================================
        // Decorator tests
        // ----------------------------------------------------------------

        public function testCanAddDecorator()
        {
                // entry conditions
                $this->assertFalse(isset($this->fixture->decoratorProp));
                $this->assertfalse(isset($this->fixture->name));

                // change state
                $decorator = new Test_Obj_Decorator();
                __mf_extend($this->fixture, $decorator);

                // retest
                $this->assertTrue(isset($this->fixture->decoratorProp));
                $this->assertTrue(isset($this->fixture->name));
                $this->assertEquals('alice', $this->fixture->decoratorProp);
                $this->assertEquals('lisa',  $this->fixture->name);
        }

        public function testCannotAddDecoratorToNonExtensibleObject()
        {
                $obj       = new Test_Obj_Decorator();
                $decorator = new Test_Obj_Decorator();

                $caughtException = false;
                try
                {
                        __mf_extend($obj, $decorator);
                }
                catch (MF_PHP_E_ConstraintFailed $e)
                {
                        $caughtException = true;
                }

                $this->assertTrue($caughtException);
        }

        public function testCanGetDecoratorProperty()
        {
                // entry conditions
                $this->assertFalse(isset($this->fixture->decoratorProp));
                $this->assertfalse(isset($this->fixture->name));

                // change state
                $decorator = new Test_Obj_Decorator();
                __mf_extend($this->fixture, $decorator);

                // retest
                $this->assertTrue(isset($this->fixture->decoratorProp));
                $this->assertTrue(isset($this->fixture->name));
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

        public function testCanCallDecoratorMethod()
        {
                $decorator = new Test_Obj_Decorator();
                $this->fixture->addDecorator($decorator);

                // entry conditions
                $this->assertEquals('alice', $this->fixture->decoratorProp);

                // call the methods
                $this->assertEquals('Test_Obj_Decorator', $this->fixture->doSomethingWithADecorator());
                $this->assertEquals(true, $this->fixture->firstParamIsExtendedObject(get_class($this->fixture)));
        }

        public function testCanResetDecoratorList()
        {
                $decorator = new Test_Obj_Decorator();
                $this->fixture->addDecorator($decorator);

                // entry conditions
                $this->assertEquals('alice', $this->fixture->decoratorProp);

                // change state
                $this->fixture->resetDecorators();

                // retest
                $this->assertFalse(isset($this->fixture->decoratorProp));
        }
}
 
?>