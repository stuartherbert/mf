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
// 2009-03-18   SLH     Fixed up to work with the new task-based approach
// 2009-03-25   SLH     Added tests for model extensions
// ========================================================================

// bootstrap the framework
define('UNIT_TEST', true);
define('APP_TOPDIR', realpath(dirname(__FILE__) . '/../../'));
require_once(APP_TOPDIR . '/mf/mf.inc.php');

// load additional files we explicitly require
__mf_require_once('Testsuite');

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

class Test_Model_User extends Model
{

}

class Test_Model_User_EmailAddress_Ext
{
        public function setEmailAddress($model, $emailAddress)
        {
                $model->emailAddress    = $emailAddress;
                $model->hasEmailAddress = true;
        }
}

Testsuite_registerTests('Model_Definitions_Tests');
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
                /*
                $oDef->hasMany('tags')
                     ->ourFieldsAre(array('name', 'version'))
                     ->theirModelIs('Test_Model_Tag')
                     ->theirFieldsAre(array('noteName', 'noteVersion'))
                     ->joinUsing('Test_Model_Note_Tag', 'tag');
                 */
                
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
                /*
                $oDef->hasMany('notes')
                     ->ourFieldIs('name')
                     ->theirModelIs('Test_Model_Note')
                     ->theirFieldIs('tagName')
                     ->joinUsing('Test_Model_Note_Tag', 'note');
                 */
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

                $oDef = Model_Definitions::get('Test_Model_User');
                $oDef->addField('id');
                $oDef->addField('username');
                $oDef->addField('password');
        }

        public function testCanDefineModel()
        {
        	$this->setupDefineModels();

                $oDef = Model_Definitions::get('Test_Model_Requirement');
                $this->assertTrue($oDef instanceof Model_Definition);
                $this->assertTrue($oDef->getModelName() == 'Test_Model_Requirement');
        }

        public function testAlwaysReceiveTheSameModel()
        {
        	$this->setupDefineModels();

                $oDef1 = Model_Definitions::get('Test_Model_Requirement');
                $oDef2 = Model_Definitions::get('Test_Model_Requirement');

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
                $oDef1 = Model_Definitions::get('Test_Model_Requirement');
                $this->assertTrue($oDef1 instanceof Model_Definition);

                // step 2: get rid of one of the models
                Model_Definitions::destroy('Test_Model_Requirement');

                // step 3: prove that the model definition no longer
                //         exists
                $thrown = false;
                try
                {
                	$oDef2 = Model_Definitions::getIfExists('Test_Model_Requirement');
                }
                catch (Model_E_NoSuchDefinition $e)
                {
                	$thrown = true;
                }
                $this->assertTrue($thrown);

                // step 4: when we re-create the model, prove that we
                // get a different object to work with
                $oDef3 = Model_Definitions::get('Test_Model_Requirement');
                $this->assertNotSame($oDef1, $oDef3);

                // step 5: prove that we do get the same object if we
                // ask for the definition a second time
                $oDef4 = Model_Definitions::get('Test_Model_Requirement');
                $this->assertSame($oDef3, $oDef4);
        }

        public function testCanExtendExistingModel()
        {
                // step 1: prove that we don't have an emailAddress
                //         field

                $user            = new Test_Model_User;
                $extensionThrown = false;

                try
                {
                        $emailAddress = $user->emailAddress;
                }
                catch (Model_E_NoSuchField $e)
                {
                        $exceptionThrown = true;
                }

                $this->assertEquals(true, $exceptionThrown);

                // step 2: now, extend the model to add the emailAddress
                //         field
                $oDef = Model_Definitions::get('Test_Model_User');
                $oDef->addField('emailAddress');
                $oDef->addField('hasEmailAddress')
                     ->setDefaultValue(false);

                $oDef->addExtension('Test_Model_User_EmailAddress_Ext');

                // now, see if the extension works
                //
                // note: we should not have to create a new instance
                //       of the user object!
                $this->assertEquals(false, $user->hasEmailAddress);
                $user->setEmailAddress('stuart@stuartherbert.com');

                $this->assertEquals(true, $user->hasEmailAddress);
                $this->assertEquals('stuart@stuartherbert.com', $user->emailAddress);
        }
}

Testsuite_registerTests('Model_Tests');
class Model_Tests extends PHPUnit_Framework_TestCase
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
        }

        public function testGetCorrectDefinitionAfterDefinitionDeleted()
        {
        	$this->setupDefineModels();

                $req   = new Test_Model_Requirement();
                $oDef1 = $req->getDefinition();

                Model_Definitions::destroy();

                $req2  = new Test_Model_Requirement();
                $oDef2 = $req2->getDefinition();

                $this->assertNotSame($oDef1, $oDef2);
        }
}
?>