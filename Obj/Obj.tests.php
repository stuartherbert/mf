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
// Copyright    (c) 2008-2009 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2009-05-22   SLH     Created
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
}

class Test_Obj_BaseMixin extends Obj
{
        public    $baseMixinProp = 'silver';
        protected $protVar       = 'gold';

        public function getMetal($orig)
        {
                return $this->protVar;
        }

        public function setMetal($orig, $value)
        {
                $this->protVar = $value;
        }

        public function issetMetal($orig)
        {
                return isset($this->protVar);
        }

        public function unsetMetal($orig)
        {
                unset($this->protVar);
        }
}

// we think it is interesting to make the mixin also extends Obj :)
class Test_Obj_ExtMixin extends Obj
{
        public    $mixinProp = 'fred';
        protected $protVar   = 'trout';

        public function getFish($orig)
        {
                return $this->mixinProtVar;
        }

        public function setFish($orig, $value)
        {
                $this->protVar = $value;
        }

        public function issetFish($orig)
        {
                return isset($this->protVar);
        }

        public function unsetFish($orig)
        {
                $this->protVar = null;
        }
}

class Test_Obj2 extends Obj
{

}

Testsuite_registerTests('Obj_Tests');
class Obj_Tests extends PHPUnit_Framework_TestCase
{
	public function setup()
        {
                Obj_MixinDefinitions::destroy();

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
}

?>