<?php

// ========================================================================
//
// Obj/Obj.tests.php
//              Tests for the Obj component
//
//              Part of the Methodosity Framework for PHP Applications
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2008-2010 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2009-05-22   SLH     Created
// 2009-05-25   SLH     Added tests for decorators
// 2009-06-01   SLH     Added test for calling same method on all mixins
// 2009-06-04   SLH     Changes for updated mixin API
// ========================================================================

// bootstrap the framework
define('UNIT_TEST', true);
define('APP_TOPDIR', realpath(dirname(__FILE__) . '/../../'));
require_once(APP_TOPDIR . '/mf/mf.inc.php');

// load additional files we explicitly require
__mf_require_once('Testsuite');

class Test_ObjBase extends Obj
{
        
}

class Test_ObjExt extends Test_ObjBase
{
        public $pubVar = 'hello';

        protected $protVar = 'world';

        public function getPlanet()
        {
                return $this->protVar;
        }

        public function setPlanet($value)
        {
                // var_dump($value);
                $this->protVar = $value;
        }

        public function issetPlanet()
        {
                return isset($this->protVar);
        }

        public function unsetPlanet()
        {
                // NOTE:
                //
                // We *cannot* unset($this->protVar), because that then
                // triggers problems if we call isset($this->protVar)
                // afterwards
                //
                // Instead, we must set the member's value to NULL
                
                $this->protVar = null;
        }

        public function validateCalled()
        {
                $return = array();

                $objs = $this->findObjsForMethod(__FUNCTION__);
                foreach ($objs as $obj)
                {
                        $result = $obj->validateCalled();
                        $return[] = $result;
                }

                return $return;
        }
}

class Test_Obj2 extends Test_ObjBase
{

}

class Test_Obj_BaseMixin extends Obj_Mixin
{
        public    $baseMixinProp = 'silver';
        protected $protVar       = 'gold';

        public function getMetal()
        {
                return $this->protVar;
        }

        public function setMetal($value)
        {
                $this->protVar = $value;
        }

        public function issetMetal()
        {
                return isset($this->protVar);
        }

        public function unsetMetal()
        {
                unset($this->protVar);
        }

        public function validateCalled()
        {
                return get_class($this);
        }
}

class Test_Obj_ExtMixin extends Obj_Mixin
{
        public    $mixinProp = 'fred';
        protected $protVar   = 'trout';

        public function getFish()
        {
                return $this->mixinProtVar;
        }

        public function setFish($value)
        {
                $this->protVar = $value;
        }

        public function issetFish()
        {
                return isset($this->protVar);
        }

        public function unsetFish()
        {
                $this->protVar = null;
        }

        public function validateCalled()
        {
                return get_class($this);
        }
}

class Test_Obj_ExtMixin2 extends Obj_Mixin
{
        public function validateCalled()
        {
                return get_class($this);
        }
}

class Test_Obj_Decorator extends Obj
{
        public    $decoratorProp = 'alice';
        protected $protVar       = 'lisa';

        public function getName()
        {
                return $this->protVar;
        }

        public function setName($value)
        {
                $this->protVar = $value;
        }

        public function issetName()
        {
                return isset($this->protVar);
        }

        public function unsetFish()
        {
                $this->protVar = null;
        }
}

Testsuite_registerTests('Obj_Tests');
class Obj_Tests extends PHPUnit_Framework_TestCase
{
	public function setup()
        {
                Obj_MixinsManager::destroy();

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
                catch (Obj_E_NoSuchProperty $e)
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


        public function testCanGetMixinProperty()
        {
                $this->assertEquals('fred', $this->fixture->mixinProp);
        }

        public function testCanGetMixinPropertyFromExtendedBaseClass()
        {
                // entry conditions
                $this->assertEquals(1, $this->fixture->mixinCount);

                // change state
                __mf_extend('Test_ObjBase', 'Test_Obj_BaseMixin');

                // retest
                $this->assertEquals(2, $this->fixture->mixinCount);
                $this->assertEquals('gold', $this->fixture->metal);
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