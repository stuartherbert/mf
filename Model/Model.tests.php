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
// 2009-03-09   SLH     Added tests for models with complicated primary
//                      keys
// ========================================================================

class Test_Model_Requirement extends Model
{

}

class Test_Model_Project extends Model
{

}

class Test_Model_Proposal extends Model
{

}

class Test_Model_Note extends Model
{

}

class Test_Model_Author extends Model
{

}

class Test_Model_Note_Author extends Model
{

}

class Test_Model_Tag extends Model
{

}

class Test_Model_Note_Tag extends Model
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
        	$oDef = Model_Definitions::get('Test_Model_Requirement');
                $oDef->addField('requirementsUid');
                $oDef->addField('title');
                $oDef->addField('summary');
                $oDef->addField('description');
                $oDef->setPrimaryKey('requirementsUid');

                $oDef = Model_Definitions::get('Test_Model_Note');
                $oDef->addField('name');
                $oDef->addField('version');
                $oDef->addField('value');
                $oDef->addField('authorName');
                $oDef->setPrimaryKey(array('name', 'version'));
                $oDef->hasOne('author')
                     ->ourFieldIs('authorName')
                     ->theirModelIs('Test_Model_Author')
                     ->theirFieldIs('name');
                $oDef->hasMany('tags')
                     ->ourFieldsAre(array('name', 'version'))
                     ->theirModelIs('Test_Model_Tag')
                     ->theirFieldsAre(array('noteName', 'noteVersion'))
                     ->joinUsing('Test_Model_Note_Tag', 'tag');

                $oDef = Model_Definitions::get('Test_Model_Author');
                $oDef->addField('id');
                $oDef->addField('name');
                $oDef->setPrimaryKey('id');
                $oDef->hasMany('notes')
                     ->ourFieldIs('name')
                     ->theirModelIs('Test_Model_Note')
                     ->theirFieldIs('authorName');

                $oDef = Model_Definitions::get('Test_Model_Note_Author');
                $oDef->addField('authorName');
                $oDef->addField('noteName');
                $oDef->addField('noteVersion');
                $oDef->setPrimaryKey(array('authorName', 'noteName', 'noteVersion'));
                $oDef->hasOne('note')
                     ->ourFieldsAre(array('noteName', 'noteVersion'))
                     ->theirModelIs('Test_Model_Note')
                     ->theirFieldsAre(array('name', 'version'));
                $oDef->hasOne('author')
                      ->ourFieldIs('authorName')
                      ->theirModelIs('Test_Model_Note_Author')
                      ->theirFieldIs('name');

                $oDef = Model_Definitions::get('Test_Model_Tag');
                $oDef->addField('name');
                $oDef->setPrimaryKey('name');
                $oDef->hasMany('notes')
                     ->ourFieldIs('name')
                     ->theirModelIs('Test_Model_Note')
                     ->theirFieldIs('tagName')
                     ->joinUsing('Test_Model_Note_Tag', 'note');

                $oDef = Model_Definitions::get('Test_Model_Note_Tag');
                $oDef->addField('noteName');
                $oDef->addField('noteVersion');
                $oDef->addField('tagName');
                $oDef->setPrimaryKey(array('noteName', 'noteVersion', 'tagName'));
                $oDef->hasOne('note')
                     ->ourFieldsAre(array('noteName', 'noteVersion'))
                     ->theirModelIs('Test_Model_Note')
                     ->theirFieldsAre(array('name', 'version'));
                $oDef->hasOne('tag')
                     ->ourFieldIs('tagName')
                     ->theirModelIs('Test_Model_Tag')
                     ->theirFieldIs('name');
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