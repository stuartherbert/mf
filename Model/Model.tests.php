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
// 2009-03-25   SLH     Updated model extension test to reflect new
//                      Model_Extension interface
// 2009-05-20   SLH     Added tests for auto-conversion of model fields
// 2009-05-20   SLH     Added some fundamental tests for Model
// 2009-05-21   SLH     Extensions are now passed as objects, not classes
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

class Test_Model_User_EmailAddress_Ext implements Model_Extension
{
        public function extendsModelDefinition(Model_Definition $oDef)
        {
                $oDef->addField('emailAddress');
                $oDef->addFakeField('hasEmailAddress')
                     ->setDefaultValue(false);
        }

        public function setEmailAddress($model, $emailAddress)
        {
                $model->_setFieldInData('emailAddress', $emailAddress);
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
                $oDef->addExtension(new Test_Model_User_EmailAddress_Ext());

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
                $this->setupDefineModels();
        }

        public function setupDefineModels()
        {
                $oDef = Model_Definitions::get('Test_Model_Requirement');
                $oDef->addField('requirementsUid');
                $oDef->addField('title');
                $oDef->addField('summary');
                $oDef->addField('description');
                $oDef->addField('project')
                     ->setDefaultValue('MF Testing');
        }

        // ================================================================
        // Tests related to the model definition
        // ----------------------------------------------------------------

        public function testGetCorrectDefinitionAfterDefinitionDeleted()
        {
                $req   = new Test_Model_Requirement();
                $oDef1 = $req->getDefinition();

                Model_Definitions::destroy();

                $req2  = new Test_Model_Requirement();
                $oDef2 = $req2->getDefinition();

                $this->assertNotSame($oDef1, $oDef2);
        }

        public function testNewModelsStartWithDefaultValues()
        {
                $req = new Test_Model_Requirement();

                $this->assertFalse(isset($req->requirementsUid));
                $this->assertFalse(isset($req->title));
                $this->assertFalse(isset($req->summary));
                $this->assertFalse(isset($req->description));
                $this->assertTrue (isset($req->project));
                $this->assertEquals('MF Testing', $req->project);
        }

        // ================================================================
        // Tests related to getting / setting field values
        // ----------------------------------------------------------------

        public function testCanGetSetFieldValues()
        {
                $req = new Test_Model_Requirement();

                // all new models start off empty
                $this->assertFalse(isset($req->title));

                // set a title via magic methods
                $firstTitle = 'A title';
                $req->title = $firstTitle;
                $this->assertEquals($firstTitle, $req->title);
                $this->assertEquals($firstTitle, $req->getField('title'));

                // set a title via setField()
                $secondTitle = 'A second title';
                $req->setField('title', $secondTitle);
                $this->assertEquals($secondTitle, $req->title);
                $this->assertEquals($secondTitle, $req->getField('title'));
        }

        public function testCanReplaceADefaultValue()
        {
                $req = new Test_Model_Requirement();

                $this->assertEquals('MF Testing', $req->project);
                $newProject = 'More MF Testing';
                $req->project = $newProject;
                $this->assertEquals($newProject, $req->project);
        }

        public function testCanUnsetAField()
        {
                $req = new Test_Model_Requirement();

                // entry conditions
                $this->assertEquals('MF Testing', $req->project);

                // change the state
                unset($req->project);

                // retest
                $this->assertFalse(isset($req->project));
        }

        public function testCanEmptyAField()
        {
                $req = new Test_Model_Requirement();

                // entry conditions
                $this->assertEquals('MF Testing', $req->project);

                // change the state
                $req->project = null;

                // retest
                $this->assertFalse(isset($req->project));
                $this->assertEquals(null, $req->project);
                $data =& $req->getData();
                $this->assertFalse(isset($data['project']));
                $this->assertFalse(array_key_exists('project', $data));
        }

        public function testCannotAccessFieldsThatDoNotExist()
        {
                $req = new Test_Model_Requirement();

                $pass = false;
                try
                {
                        $dummy = $req->nonExistantField;
                }
                catch (Model_E_NoSuchField $e)
                {
                        $pass = true;
                }
                $this->assertTrue($pass);
        }

        public function testCanResetAFieldToDefaultValue()
        {
                $req = new Test_Model_Requirement();
                unset($req->project);

                // entry conditions
                $this->assertNull($req->project);

                // change state
                $req->setFieldToDefault('project');

                // retest
                $this->assertEquals('MF Testing', $req->project);
        }

        // ================================================================
        // Tests related to automatic field value conversion
        // ----------------------------------------------------------------

        public function testCanConvertFieldToHtml()
        {
                $req = new Test_Model_Requirement();
                $req->title = 'serve & protect';

                // make sure the original title is preserved
                $this->assertEquals('serve & protect', $req->title);

                // make sure the auto-conversion to html occurred
                $this->assertEquals('serve &amp; protect', $req->title_html);
        }

        public function testCanConvertFieldToXml()
        {
                $req = new Test_Model_Requirement();
                $req->title = 'serve & protect';

                // make sure the original title is preserved
                $this->assertEquals('serve & protect', $req->title);

                // make sure the auto-conversion to xml occurred
                $this->assertEquals('<title>serve &amp; protect</title>', $req->title_xml);
        }

        public function testCanConvertModelToXml()
        {
                $req = new Test_Model_Requirement();
                $req->title = 'serve & protect';

                // make sure the original title is preserved
                $this->assertEquals('serve & protect', $req->title);

                // convert the object to xml
                $this->assertEquals('<Test_Model_Requirement><project>MF Testing</project><title>serve &amp; protect</title></Test_Model_Requirement>', $req->toXml());
        }

        // ================================================================
        // Tests related to iterator support
        // ----------------------------------------------------------------

        public function testCanIterateOverAModel()
        {
                $expected = array('title' => 'fred', 'project' => 'MF Testing');

                $req = new Test_Model_Requirement();
                $req->title = 'fred';

                // make sure only expected fields are seen
                foreach ($req as $field => $value)
                {
                        $this->assertEquals($expected[$field], $value);
                        unset($expected[$field]);
                }

                // make sure all the expected fields have been seen
                $this->assertEquals(0, count($expected));
        }

        // ================================================================
        // Tests related to manipulating all the fields in one go
        // ----------------------------------------------------------------

        public function testCanGetFieldsAsAReferencedArray()
        {
                $req = new Test_Model_Requirement();
                $data =& $req->getData();

                // entry conditions
                $this->assertEquals('MF Testing', $data['project']);
                $this->assertEquals('MF Testing', $req->project);

                // now, make a change in $data; it should also be reflected
                // in $req
                $data['project'] = 'More MF Testing';

                $this->assertEquals('More MF Testing', $data['project']);
                $this->assertEquals('More MF Testing', $req->project);
        }

        public function testCanGetFieldsAsACopiedArray()
        {
                $req = new Test_Model_Requirement();
                $data =& $req->getFields();

                // entry conditions
                $this->assertEquals('MF Testing', $data['project']);
                $this->assertEquals('MF Testing', $req->project);

                // now, make a change in $data; it should not be reflected
                // in $req
                $data['project'] = 'More MF Testing';

                $this->assertEquals('More MF Testing', $data['project']);
                $this->assertEquals('MF Testing', $req->project);
        }

        public function testCanResetAllFieldsAtOnce()
        {
                $req = new Test_Model_Requirement();
                $req->title = 'fred';

                // entry conditions
                $this->assertEquals('fred', $req->title);
                $this->assertTrue(isset($req->title));
                $this->assertEquals('MF Testing', $req->project);
                $this->assertTrue(isset($req->project));

                // change state
                $req->resetData();

                // retest
                $this->assertEquals(null, $req->title);
                $this->assertFalse(isset($req->title));
                $this->assertEquals(null, $req->project);
                $this->assertFalse(isset($req->project));
        }

        public function testCanSetAllFieldsAtOnce()
        {
                $req = new Test_Model_Requirement();

                $newData = array (
                        'requirementsUid'       => 1,
                        'title'                 => 'fred',
                        'summary'               => 'trout',
                        'description'           => 'salmon',
                        'project'               => 'More MF Testing'
                );

                // change the state
                $req->setFields($newData);

                // have we changed everything?
                foreach ($req as $field => $value)
                {
                        $this->assertEquals($newData[$field], $value);
                        unset($newData[$field]);
                }

                // now, have we got everything?
                $this->assertEquals(0, count($newData));
        }

        public function testCanMergeFieldsFromLargerArray()
        {
                $req = new Test_Model_Requirement();

                $newData = array (
                        'requirementsUid'       => 1,
                        'title'                 => 'fred',
                        'summary'               => 'trout',
                        'description2'          => 'salmon',
                        'project2'              => 'More MF Testing',
                );

                $endData = array (
                        'requirementsUid'       => 1,
                        'title'                 => 'fred',
                        'summary'               => 'trout',
                        'project'               => 'MF Testing',
                );

                // change the state
                $req->setFields($newData, Model::MERGE_DATA);

                // have we changed everything?
                foreach ($req as $field => $value)
                {
                        $this->assertEquals($endData[$field], $value);
                        unset($endData[$field]);
                }

                // now, have we got everything?
                $this->assertEquals(0, count($endData));
        }

        public function testCanSetAllFieldsToDefaultsAtOnce()
        {
                $req = new Test_Model_Requirement();

                // entry conditions
                $newData = array (
                        'requirementsUid'       => 1,
                        'title'                 => 'fred',
                        'summary'               => 'trout',
                        'description'           => 'salmon',
                        'project'               => 'More MF Testing'
                );
                $req->setFields($newData);
                foreach ($req as $field => $value)
                {
                        $this->assertEquals($newData[$field], $value);
                        unset($newData[$field]);
                }
                $this->assertEquals(0, count($newData));

                // change state
                $req->setFieldsToDefaults();

                // retest
                $this->assertFalse(isset($req->requirementsUid));
                $this->assertFalse(isset($req->title));
                $this->assertFalse(isset($req->summary));
                $this->assertFalse(isset($req->description));
                $this->assertEquals('MF Testing', $req->project);
        }

        // ================================================================
        // Tests related to models knowing whether or not they need saving
        // ----------------------------------------------------------------

        public function testNewModelsNeedSaving()
        {
                $req = new Test_Model_Requirement();
                $this->assertTrue($req->getNeedsSaving());
        }

        public function testCanExplicitlyMarkAModelAsNotNeedsSaving()
        {
                $req = new Test_Model_Requirement();

                // entry conditions
                $this->assertTrue($req->getNeedsSaving());

                // change the state
                $req->resetNeedsSaving();

                // retest
                $this->assertFalse($req->getNeedsSaving());
        }
        
        public function testChangingAFieldMarksModelAsNeedsSaving()
        {
                $req = new Test_Model_Requirement();
                $req->resetNeedsSaving();
                
                // entry conditions
                $this->assertFalse(isset($req->title));
                $this->assertFalse($req->getNeedsSaving());

                // change the model
                $req->title = 'fred';

                // retest
                $this->assertEquals('fred', $req->title);
                $this->assertTrue($req->getNeedsSaving());
        }

        public function testCanExplicitlyMarkAModelAsNeedsSaving()
        {
                $req = new Test_Model_Requirement();
                $req->resetNeedsSaving();

                // entry conditions
                $this->assertFalse($req->getNeedsSaving());

                // change the state
                $req->setNeedsSaving();

                // retest
                $this->assertTrue($req->getNeedsSaving());

        }

        // ================================================================
        // Tests related to whether a model is readonly or not
        // ----------------------------------------------------------------

        public function testModelIsWriteableByDefault()
        {
                $req = new Test_Model_Requirement();
                $this->assertFalse($req->isReadOnly());
                $this->assertTrue($req->isWriteable());
        }

        public function testCanSetAFieldOnAWriteableModel()
        {
                $req = new Test_Model_Requirement();

                // entry conditions
                $this->assertTrue($req->isWriteable());
                $this->assertFalse(isset($req->title));

                // change state
                $req->title = 'fred';

                // retest
                $this->assertTrue($req->isWriteable());
                $this->assertTrue(isset($req->title));
        }

        public function testCannotSetAFieldOnAReadOnlyModel()
        {
                $req = new Test_Model_Requirement();
                $req->setReadOnly();

                // entry conditions
                $this->assertFalse($req->isWriteable());
                $this->assertTrue($req->isReadOnly());

                // change state ... should not be allowed
                $pass = false;
                try
                {
                        $req->title = 'fred';
                }
                catch (Model_E_IsReadOnly $e)
                {
                        $pass = true;
                }

                // retest
                $this->assertTrue($pass);
        }

        public function testCannotResetAReadOnlyModel()
        {
                $req = new Test_Model_Requirement();
                // we set a title in case resetData() attempts to restore
                // the default values for the model
                $req->title = 'fred';
                $req->setReadOnly();

                // entry conditions
                $this->assertFalse($req->isWriteable());
                $this->assertTrue($req->isReadOnly());
                $this->assertEquals('fred', $req->title);
                $this->assertEquals('MF Testing', $req->project);

                // change state ... should not be allowed
                $pass = false;
                try
                {
                        $req->resetData();
                }
                catch (Model_E_IsReadOnly $e)
                {
                        $pass = true;
                }

                // retest
                $this->assertTrue($pass);
                $this->assertEquals('fred', $req->title);
                $this->assertEquals('MF Testing', $req->project);
        }

        public function testCanMakeAModelWriteable()
        {
                $req = new Test_Model_Requirement();
                $req->setReadOnly();

                // entry conditions
                $this->assertFalse($req->isWriteable());
                $this->assertTrue($req->isReadOnly());
                $this->assertEquals(null, $req->title);
                $pass = false;
                try
                {
                        $req->title = 'fred';
                }
                catch (Model_E_IsReadOnly $e)
                {
                        $pass = true;
                }
                $this->assertTrue($pass);
                $this->assertEquals(null, $req->title);

                // change state
                $req->setWriteable();
                $req->title = 'fred';

                // retest
                $this->assertTrue($req->isWriteable());
                $this->assertFalse($req->isReadOnly());
                $this->assertEquals('fred', $req->title);
        }

        public function testCanRequireAWriteableModel()
        {
                $req = new Test_Model_Requirement();

                // entry conditions
                $this->assertTrue($req->isWriteable());
                $req->requireWriteable();

                // change state
                $req->setReadOnly();

                // retest
                $pass = false;
                try
                {
                        $req->requireWriteable();
                }
                catch (Model_E_IsReadOnly $e)
                {
                        $pass = true;
                }
                $this->assertTrue($pass);
        }

        public function testCannotMarkAReadOnlyModelAsNeedsSaving()
        {
                $req = new Test_Model_Requirement();
                $req->resetNeedsSaving();
                $req->setReadOnly();

                // entry conditions
                $this->assertFalse($req->isWriteable());
                $this->assertFalse($req->getNeedsSaving());

                // change state
                $pass = false;
                try
                {
                        $req->setNeedsSaving();
                }
                catch (Model_E_IsReadOnly $e)
                {
                        $pass = true;
                }

                // retest
                $this->assertTrue($pass);
        }
}

?>