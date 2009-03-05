<?php

// ========================================================================
//
// Model/Model.tests.php
//              Tests for the Model component
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
// 2008-07-28   SLH     Created
// 2008-08-07   SLH     Models no longer know/care about which 'table'
//                      they are stored in
// ========================================================================

class Test_Model_Requirements extends Model
{

}

class Test_Model_Project extends Model
{

}

class Test_Model_Proposal extends Model
{

}

class Model_Definitions_Tests extends PHPUnit_Framework_TestCase
{
	public function setup()
        {
        	Model_Definitions::destroy();
        }

        public function setupDefineModels()
        {
        	$oDef = Model_Definitions::get('Test_Model_Requirements');
                $oDef->addField('requirementsUid');
                $oDef->addField('title');
                $oDef->addField('summary');
                $oDef->addField('description');
        }

        public function testCanDefineModel()
        {
        	$this->setupDefineModels();

                $oDef = Model_Definitions::get('Test_Model_Requirements');
                $this->assertTrue($oDef instanceof Model_Definition);
                $this->assertTrue($oDef->getModelName() == 'Test_Model_Requirements');
        }

        public function testAlwaysReceiveTheSameModel()
        {
        	$this->setupDefineModels();

                $oDef1 = Model_Definitions::get('Test_Model_Requirements');
                $oDef2 = Model_Definitions::get('Test_Model_Requirements');

                $this->assertSame($oDef1, $oDef2);
        }

        public function testThrowsExceptionWhenModelIsNotDefined()
        {
                $thrown = false;

        	try
                {
                	$oDef = Model_Definitions::getIfExists('Test_Model_FooBar');
                }
                catch (Model_E_NoSuchDefinition $e)
                {
                	$thrown = true;
                }

                $this->assertTrue($thrown);
        }

        public function testThrowsExceptionWhenModelDoesNotExist()
        {
        	$thrown = false;

                try
                {
                	$oDef = Model_Definitions::get('Test_Model_FooBar');
                }
                catch (PHP_E_NoSuchClass $e)
                {
                	$thrown = true;
                }

                $this->assertTrue($thrown);
        }

        public function testCanUndefineAModel()
        {
                // ensure we have some models for this test
        	$this->setupDefineModels();

                // step 1: prove that we have models for this test
                $oDef1 = Model_Definitions::get('Test_Model_Requirements');
                $this->assertTrue($oDef1 instanceof Model_Definition);

                // step 2: get rid of one of the models
                Model_Definitions::destroy('Test_Model_Requirements');

                // step 3: prove that the model definition no longer
                //         exists
                $thrown = false;
                try
                {
                	$oDef2 = Model_Definitions::getIfExists('Test_Model_Requirements');
                }
                catch (Model_E_NoSuchDefinition $e)
                {
                	$thrown = true;
                }
                $this->assertTrue($thrown);

                // step 4: when we re-create the model, prove that we
                // get a different object to work with
                $oDef3 = Model_Definitions::get('Test_Model_Requirements');
                $this->assertNotSame($oDef1, $oDef3);

                // step 5: prove that we do get the same object if we
                // ask for the definition a second time
                $oDef4 = Model_Definitions::get('Test_Model_Requirements');
                $this->assertSame($oDef3, $oDef4);
        }
}

class Model_Tests extends PHPUnit_Framework_TestCase
{
        public function setup()
        {
                Model_Definitions::destroy();
        }

        public function setupDefineModels()
        {
                $oDef = Model_Definitions::get('Test_Model_Requirements');
                $oDef->addField('requirementsUid');
                $oDef->addField('title');
                $oDef->addField('summary');
                $oDef->addField('description');
        }

        public function testGetCorrectDefinitionAfterDefinitionDeleted()
        {
        	$this->setupDefineModels();

                $req   = new Test_Model_Requirements();
                $oDef1 = $req->getDefinition();

                Model_Definitions::destroy();

                $req2  = new Test_Model_Requirements();
                $oDef2 = $req2->getDefinition();

                $this->assertNotSame($oDef1, $oDef2);
        }
}
?>