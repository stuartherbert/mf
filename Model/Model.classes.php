<?php

// ========================================================================
//
// Model/Model.classes.php
//              Classes for defining data models
//
//              Part of the Methodosity Framework for PHP applications
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2007-2009 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2007-08-11   SLH     Consolidated from separate files
// 2007-09-11   SLH     Fixed addFakeField()
// 2008-01-05   SLH     Split DatastoreRecord up to form Model class
// 2008-07-19   SLH     Support for using Datastore_Record as an
//                      proxy for Model classes
// 2008-07-25   SLH     Ensure views always include the primary key
// 2008-07-25   SLH     Models no longer have the definition as an
//                      attribute (makes var_dumping models easy)
// 2008-07-28   SLH     Added ability to reset all stored definitions
//                      (required for unit testing)
// 2008-07-28   SLH     Model_FieldDefinitions no longer store the parent
//                      model definition as an attribute (makes var_dump
//                      a lot more palletable)
// 2008-07-28   SLH     Model_Definitions::reset() is now destroy()
// 2008-08-07   SLH     Model_Definitions no longer include the table
//                      where a model is stored (storage location is
//                      now Datastore-specific)
// 2009-03-09   SLH     Models now support primary keys built from
//                      multiple fields
// 2009-03-11   SLH     Added basic support for many:many relationships
//                      using 'foundVia()' method
// 2009-03-18   SLH     Fixes for supporting complex primary keys
// 2009-03-19   SLH     Primary keys are now always stored internally as
//                      an array
// 2009-03-23   SLH     Added stub for inheritance support
// 2009-03-23   SLH     Instead of Datastore_Record wrapping the Model,
//                      the Model now acts as a wrapper for Datastore_Record
// 2009-03-25   SLH     Added support for extending models w/out having
//                      to use inheritance
// 2009-03-25   SLH     Added Model_Extension interface
// 2009-03-25   SLH     Basic functioning many:many support
// 2009-03-31   SLH     Added support for issetVariable-type overrides
// 2009-05-20   SLH     Added support for auto-conversion for HTML output
// 2009-05-20   SLH     Added support for auto-conversion for XML output
// 2009-05-20   SLH     Added __unset() support to Model
// 2009-05-20   SLH     Added isWriteable()
// 2009-05-20   SLH     Renamed methods for needsSaving support in Model
// 2009-05-20   SLH     requireWritable() renamed to requireWriteable()
// 2009-05-20   SLH     Model extensions can now have get/set methods for
//                      fields
// 2009-05-21   SLH     Performance improvements for handling get/set
// 2009-05-21   SLH     Added basic support for FIEO
// 2009-05-21   SLH     Added basic support for loading a model's data
//                      from $_POST
// 2009-05-26   SLH     Supports the new generic mixin / decorator features
//                      added to Obj
// ========================================================================

class Model extends Obj
implements Iterator
{
        protected $aData                = array();
        protected $primaryKey           = null;
        protected $iterKey              = 0;
        protected $needsSaving          = false;
        protected $readOnly             = false;
        protected $definitionName       = null;

        protected $datastoreProxy       = null;

        const     DATA_START            = 0;
        const     REPLACE_DATA          = 1;
        const     MERGE_DATA            = 2;
        const     DATA_END              = 3;

        public function __construct ($modelName = null, $aData = null, $dataAction = Model::REPLACE_DATA)
        {
                if ($modelName == null)
                {
                        $modelName = get_class($this);
                }

                parent::__construct($modelName);

                $oDef = $this->getDefinition($modelName);
                $this->primaryKey = $oDef->getPrimaryKey();

                if (count($aData) > 0)
                {
                        $this->setFields($aData, $dataAction);
                }
                else
                {
                        $this->setFieldsToDefaults();
                }
        }

        public function getDefinition($modelName = null, $reset = false)
        {
                // we want to use a static variable here for performance
                // reasons, but we also need a way to reliably cope when
                // (not if) the master list of definitions is emptied

                static $oDef = null;

                // special case
                if ($reset)
                {
                	$oDef = null;
                        return;
                }

                if ($modelName == null)
                {
                	$modelName = get_class($this);
                }
                if ($oDef === null)
                {
              	        $oDef = Model_Definitions::get($modelName);
                        Model_Definitions::registerCache($this, $modelName);
                }

                return $oDef;
        }

        public function definitionReset()
        {
        	$this->getDefinition(null, true);
        }

        public function setDatastoreProxy($proxy)
        {
                $this->datastoreProxy = $proxy;
        }

        // ================================================================
        // Support for extending the model
        // ----------------------------------------------------------------

        public function __call($fullMethodName, $origArgs)
        {
                // step 1: do the same that Obj would normally do
                //
                // It may appear bad form to copy Obj's code, but the
                // alternative is to call Obj's __call in a try/catch
                // block, and we want to keep the overhead to a minimum

                // prepare the args to pass to the method

                $args = array($this);
                foreach ($origArgs as $arg)
                {
                        $args[] = $arg;
                }

                $obj = $this->findObjForMethod($fullMethodName);
                if ($obj)
                {
                        return call_user_func_array(array($obj, $fullMethodName), $args);
                }

                // step 2: do we need to call datastore proxy instead?
                if (!isset($this->datastoreProxy))
                {
                        throw new Obj_E_NoSuchMethod($fullMethodName, $this);
                }

                return call_user_func_array(array($this->datastoreProxy, $fullMethodName), $origArgs);
        }

        // ================================================================
        // Support for calling the datastore proxy object
        // ----------------------------------------------------------------

        public function retrieve(Datastore $oDB, $primaryKey)
        {
                $this->datastoreProxy = $oDB->getNewDatastoreProxy($this);
                $this->datastoreProxy->retrieve($oDB, $primaryKey);
        }

        public function store(Datastore $oDB = null)
        {
                if ($oDB == null && $this->datastoreProxy == null)
                {
                        // TODO: throw a better exception here
                        throw new Exception();
                }

                if ($this->datastoreProxy == null)
                {
                        $this->datastoreProxy = $oDB->getNewDatastoreProxy($this);
                }

                return $this->datastoreProxy->store($oDB);
        }

        // ================================================================
        // Support for managing the data held in the record
        // ----------------------------------------------------------------

        public function &getData ()
        {
                return $this->aData;
        }

        public function resetData ()
        {
                $this->requireWriteable();

                $this->aData = array();
                $this->resetNeedsSaving();
        }

        /**
         * set all the fields of this record in one go
         */

        public function setFields ($mData, $dataAction = Model::REPLACE_DATA)
        {
                $this->requireWriteable();

                if ($mData instanceof Model)
                {
                	$aData = $mData->getData();
                }
                else
                {
                	$aData =& $mData;
                }
                constraint_mustBeArray($aData);
                constraint_mustBeGreaterThan($dataAction, Model::DATA_START);
                constraint_mustBeLessThan($dataAction, Model::DATA_END);

                switch ($dataAction)
                {
                        case Model::REPLACE_DATA:
                                $this->replaceDataWithArray($aData);
                                break;

                        case Model::MERGE_DATA:
                                $this->mergeDataFromArray($aData);
                                break;

                        default:
                                debug_unreachable(__FILE__, __LINE__);
                }

                $this->setNeedsSaving();
        }

        public function setFieldsFromPost($post, $prefix = '')
        {
                constraint_mustBeArray($post);

                $this->requireWriteable();
                $oDef = $this->getDefinition();

                $fields = $oDef->getFields();

                foreach ($fields as $field)
                {
                        $fieldName = $field->getName();
                        $postFieldName = $prefix . $fieldName;

                        if (isset($post[$postFieldName]))
                        {
                                // the whole reason why we have a special
                                // method ... to make sure we filter the
                                // input
                                $data = $field->filterInput($post[$fieldName]);
                                $this->$fieldName = $data;
                        }
                }
        }

        protected function replaceDataWithArray($aData)
        {
                $this->requireWriteable();
                $oDef = $this->getDefinition();

                $this->setFieldsToDefaults();
                
                // NOTE:
                //
                // We cannot 'just' replace $this->aData with $a_aData,
                // because that bypasses all the funky stuff that can
                // happen when individual fields are set

                $this->aData = array();
                foreach ($aData as $field => $value)
                {
                        if ($oDef->isValidFieldName($field))
                        {
                                $this->setField($field, $value);
                        }
                }
        }

        protected function mergeDataFromArray($aData)
        {
                $this->requireWriteable();
                $oDef = $this->getDefinition();

                $aKeys = array_keys($aData);
                foreach ($aKeys as $key)
                {
                        if ($oDef->isValidFieldName($key))
                        {
                                // setField throws an exception if there
                                // is a problem

                                $this->setField($key, $aData[$key]);
                        }
                }
        }

        public function hasData ()
        {
                return (count($this->aData) > 0);
        }

        public function isEmpty()
        {
                return (count($this->aData) == 0);
        }

        public function emptyWithoutSave ()
        {
                $this->resetData();
                $this->resetNeedsSaving();
        }

        public function getFields($fields = array())
        {
                constraint_mustBeArray($fields);
                if (count($fields) == 0)
                {
                        $fields = array_keys($this->aData);
                }

                $return = array();
                foreach ($fields as $field)
                {
                        $return[$field] = $this->getField($field);
                }

                return $return;
        }

        public function decodeFieldName ($fieldName)
        {
                $realFieldName = $fieldName;
                $conversion    = null;

                $oDef = $this->getDefinition();
                if ($oDef->isValidFieldName($fieldName))
                {
                        $oFieldDef = $oDef->getField($fieldName);
                        return array($fieldName, null, $oFieldDef);
                }

                // the fieldname *may* need decoding
                $parts = explode('_', $fieldName);
                if (count($parts) == 1)
                {
                        throw new Model_E_NoSuchField($fieldName, $oDef->getModelName());
                }

                $lastPart = count($parts) - 1;
                $escaper = 'escapeOutputFor' . ucfirst($parts[$lastPart]);
                unset($parts[$lastPart]);

                $realFieldName = implode('_', $parts);

                $oFieldDef = $oDef->getField($realFieldName);
                $oFieldDef->requireValidEscaper($escaper);

                return array ($realFieldName, $escaper, $oFieldDef);
        }

        public function getField ($fieldName)
        {
                $oDef = $this->getDefinition();

                // are we getting a real field, or a auto-converted one?
                list($realFieldName, $escaper, $oFieldDef) = $this->decodeFieldName($fieldName);

                $obj = $this->findObjForProperty($realFieldName);
                if ($obj)
                {
                        $return = $obj->$propertyName;
                }
                else
                {
                        $method = 'get' . ucfirst($realFieldName);
                        $obj = $this->findObjForMethod($method);
                        if ($obj)
                        {
                                $return = $obj->$method($this);
                        }
                        else
                        {
                                $return = $this->_getFieldInData($realFieldName);
                        }
                }
                
                // if we have an escaper to call, call it
                if ($escaper === null)
                        return $return;

                return $oFieldDef->escapeOutput($escaper, $realFieldName, $return);
        }

        public function resetField ($fieldName)
        {
                $this->setField($fieldName, null);
        }

        public function setField($fieldName, $data)
        {
                $this->requireWriteable();
                $oDef = $this->getDefinition();

                // validate the contents of the field
                $this->validateField($fieldName, $data);

                $method = 'set' . ucfirst($realFieldName);
                $obj = $this->findObjForMethod($method);
                if ($obj !== null)
                {
                        return $obj->$method($this, $data);
                }
                else
                {
                        return $this->_setFieldInData($fieldName, $data);
                }
        }

        public function hasField ($fieldName)
        {
                $oDef = $this->getDefinition();
                return $oDef->isValidFieldName($fieldName);
        }

        public function issetField($fieldName)
        {
                if (!$this->hasField($fieldName))
                {
                        return false;
                }

                $obj = $this->findObjForProperty($fieldName);
                if ($obj)
                {
                        return isset($obj->$fieldName);
                }

                $method = 'isset' . ucfirst($fieldName);
                $obj = $this->findObjForMethod($method);
                if ($obj)
                {
                        return $obj->$method($this);
                }

                return $this->_issetFieldInData($fieldName);
        }

        /**
         * This method should only be called by Model, its subclasses and
         * any model extensions
         *
         * @param string $fieldName
         * @return mixed
         */

        public function _getFieldInData($fieldName)
        {
                if (!isset($this->aData[$fieldName]))
                {
                        return null;
                }

                return $this->aData[$fieldName];
        }

        /**
         * This method should only be called by Model, its subclasses and
         * any model extensions
         *
         * @param string $fieldName
         * @return boolean
         */

        public function _issetFieldInData($fieldName)
        {                
                // is the field set in the locally stored data?
                if (!isset($this->aData[$fieldName]))
                {
                        return false;
                }

                return true;
        }

        /**
         * This method should only be called by Model, its subclasses and
         * any model extensions
         *
         * @param string $fieldName
         * @param mixed $value
         */
         
        public function _setFieldInData($fieldName, $value)
        {
                if ($value === null)
                {
                        unset($this->aData[$fieldName]);
                }
                else
                {
                        $this->aData[$fieldName] = $value;
                }
                $this->setNeedsSaving();
        }

        public function __get ($fieldName)
        {
                return $this->getField($fieldName);
        }

        public function __set ($fieldName, $value)
        {
                return $this->setField($fieldName, $value);
        }

        public function __isset ($fieldName)
        {
                return $this->issetField($fieldName);
        }

        public function __unset($fieldName)
        {
                return $this->setField($fieldName, null);
        }

        protected function validateData (&$aData)
        {
                foreach ($aData as $fieldName => $value)
                {
                        $this->validateField($fieldName, $value);
                }
        }

        protected function validateField($fieldName, &$value)
        {
                $oDef = $this->getDefinition();

                // step 1: is the field name valid?
                $oField = $oDef->$fieldName;

                // step 2: the field comes with its own validation
                $oField->validateData($value);

                // step 3: does the record also want to validate?
                if ($method = $oField->mustValidateWhenSet())
                {
                        // yes we do
                        $this->requireValidMethod($method);

                        $this->$method($value);
                }
        }

        public function filterAndSetField($fieldName, &$value)
        {
                $oDef = $this->getDefinition();

                $oField = $oDef->$fieldName;
                $oField->filterInput($value);

                $this->$fieldName = $value;
        }

        public function requireValidMethod($method)
        {
                if (!method_exists($this, $method))
                {
                        throw new PHP_E_NoSuchMethod($method, $this);
                }
        }

        public function setFieldsToDefaults()
        {
                $oDef   = $this->getDefinition();
                $fields = $oDef->getFields();

                foreach ($fields as $fieldName => $field)
                {
                        $this->setField($fieldName, $field->getDefaultValue());
                }
        }

        public function setEmptyFieldsToDefaults()
        {
                $oDef   = $this->getDefinition();
                $fields = $oDef->getFields();

                foreach ($fields as $fieldName => $field)
                {
                        if (!isset($this->$fieldName))
                        {
                                $this->setField($fieldName, $field->getDefaultValue());
                        }
                }
        }

        public function setFieldToDefault($fieldName)
        {
                $oDef = $this->getDefinition();
                $oDef->requireValidFieldName($fieldName);

                $fields = $oDef->getFields();
                $this->setField($fieldName, $fields[$fieldName]->getDefaultValue());
        }

        public function getPrimaryKey()
        {
                return $this->getDefinition()->getPrimaryKey();
        }

        public function getFieldDefinitions($fieldNames = array())
        {
                return $this->getDefinition()->getFields($fieldNames);
        }

        public function getMandatoryFields()
        {
                return $this->getDefinition()->getMandatoryFields();
        }

        public function getFieldDefinitionsBySource($source)
        {
                return $this->getDefinition()->getFieldsBySource($source);
        }

        // ----------------------------------------------------------------
        // Support for unique ID
        // ----------------------------------------------------------------

        public function getUniqueId ()
        {
                $this->requireUniqueIdDefined();

                // is our primary key one field, or several?
                if (count($this->primaryKey) == 1)
                {
                        // it is one field
                        return $this->getField(current($this->primaryKey));
                }

                // if we get here, then the primary key is several fields
                $return = array();
                foreach ($this->primaryKey as $field)
                {
                        $return[$field] = $this->getField($field);
                }

                return $return;
        }

        public function resetUniqueId ()
        {
                $this->requireWriteable();

                $this->requireUniqueIdDefined();

                // do we have a primary key consisting of one field, or
                // of several fields?

                if (count($this->primaryKey) == 1)
                {
                        return $this->resetField(current($this->primaryKey));
                }

                // we have several fields in our primary key
                foreach ($this->primaryKey as $field)
                {
                        $this->resetField($field);
                }

                return true;
        }

        public function setUniqueId ($value)
        {
                $this->requireWriteable();

                $this->requireUniqueIdDefined();

                // do we have one field in our primary key, or several
                // fields?
                if (count($this->primaryKey) == 1)
                {
                        // just one field
                        constraint_mustNotBeArray($value);
                        return $this->setField(current($this->primaryKey), $value);
                }

                // if we get here, then we have several fields in our
                // primary key

                constraint_mustBeArray($value);
                foreach ($this->primaryKey as $field)
                {
                        $this->setField($field, $value[$field]);
                }

                return true;
        }

        public function hasUniqueId()
        {
                $this->requireUniqueIdDefined();
                
                // do we have one field in our primary key, or several
                // fields?
                
                if (count($this->primaryKey) == 1)
                {
                        // we have one field
                        return $this->hasField(current($this->primaryKey));
                }

                // we have several fields
                foreach ($this->primaryKey as $field)
                {
                        if (!$this->hasField($field))
                        {
                                return false;
                        }
                }

                return true;
        }

        public function hasUniqueIdDefined()
        {
                return isset($this->primaryKey);
        }

        public function requireUniqueIdDefined()
        {
                if (!$this->hasUniqueIdDefined())
                {
                        throw new A2ExUidNotDefined(get_class($this));
                }
        }

        // ================================================================
        // Support for whether the record needs saving or not
        // ----------------------------------------------------------------

        public function getNeedsSaving ()
        {
                return $this->needsSaving;
        }

        public function resetNeedsSaving()
        {
                $this->requireWriteable();
                $this->needsSaving = false;
        }

        public function setNeedsSaving()
        {
                $this->requireWriteable();
                $this->needsSaving = true;
        }

        // ================================================================
        // Support for marking a record as read only
        // ----------------------------------------------------------------

        public function isReadOnly ()
        {
                return $this->readOnly;
        }

        public function isWriteable()
        {
                return !$this->readOnly;
        }

        public function setWriteable ()
        {
                $this->readOnly = false;
        }

        public function setReadOnly ()
        {
                // readonly objects cannot be saved
                $this->resetNeedsSaving();
                $this->readOnly = true;
        }

        public function requireWriteable()
        {
                if ($this->readOnly === true)
                {
                        throw new Model_E_IsReadOnly($this);
                }
        }

        // ================================================================
        // Interface: Iterator
        // ----------------------------------------------------------------

        public function rewind ()
        {
                reset($this->aData);
                list($this->iterKey, ) = each ($this->aData);
        }

        public function valid()
        {
                if (!isset($this->aData[$this->iterKey]))
                        return false;

                return true;
        }

        public function key()
        {
                return $this->iterKey;
        }

        public function current()
        {
                return $this->aData[$this->iterKey];
        }

        public function next()
        {
                list($this->iterKey, ) = each ($this->aData);
                return $this->valid();
        }

        // ================================================================
        // Support for inter-record relationships
        // ----------------------------------------------------------------

        public function getForeignKeyFor($alias)
        {
                return $this->getDefinition()->getForeignKey($alias);
        }

        // ----------------------------------------------------------------

        public function getFindConditionsFor($alias)
        {
                $aMap = $this->getForeignKeyFor($alias);

                $aConditions = array
                (
                        $aMap['theirField'] => $this->getField($aMap['ourField'])
                );

                // make sure we have a value on the right-hand side of
                // the conditions
                if ($aConditions[$aMap['theirField']] === null)
                {
                        throw new Model_E_ExpectedFieldValue($aMap['ourField']);
                }

                return $aConditions;
        }

        // ----------------------------------------------------------------

        public function getFindConditionsFrom($alias, $oRecord)
        {
                $aMap = $this->getForeignKeyFor($alias);

                $aConditions = array
                (
                        $aMap['ourField'] => $oRecord->getField($aMap['theirField'])
                );

                // make sure we have a value on the right-hand side of
                // the conditions
                if ($aConditions[$aMap['ourField']] === null)
                {
                        throw new Model_E_ExpectedFieldValue($aMap['theirField']);
                }

                return $aConditions;
        }

        // ================================================================
        // different conversions for output

        public function toString()
        {
                return $this->name;
        }

        public function toXml($name = null)
        {
                if ($name === null)
                {
                        $oDef = $this->getDefinition();
                        $name = $oDef->getModelName();
                }

                $return  = '<' . $name . '>';
                $fields = $this->getFields();
                foreach ($fields as $fieldName => $data)
                {
                        $fieldName .= '_xml';
                        $return .= $this->$fieldName;
                }

                $return .= '</' . $name . '>';

                return $return;
        }
}

final class Model_Definitions implements Events_Listener
{
        static private $aInstances = array();
        static private $aCaches    = array();

        static public function get($name)
        {
                // echo " Model_Definitions::get($name) ... ";

                if (!isset(self::$aInstances[$name]))
                {
                        // echo " returning new definition\n";
                        self::$aInstances[$name] = new Model_Definition($name);
                }
                else
                // {
                        // echo " returning existing definition\n";
                // }
                // echo "  Count of definitions: " . count(self::$aInstances) . "\n";
                constraint_modelMustBeCalled(self::$aInstances[$name], $name);
                return self::$aInstances[$name];
        }

        // ----------------------------------------------------------------

        static public function getIfExists($name)
        {
                // $name = self::determineModelName($name);

                if (!isset(self::$aInstances[$name]))
                {
                        throw new Model_E_NoSuchDefinition($name);
                }

                return self::$aInstances[$name];
        }

        // ----------------------------------------------------------------

        static public function dumpDefintions()
        {
                var_dump(self::$aInstances);
        }

        // ----------------------------------------------------------------

        static public function destroy($name = null)
        {
                // echo " Model_Definitions::destroy($name) \n";
                // are we resetting all definitions?
                if ($name === null)
                {
                	self::$aInstances = array();

                        // notify all affected caches
                        foreach (self::$aCaches as $aCaches)
                        {
                        	foreach ($aCaches as $cache)
                                {
                                	$cache->definitionReset();
                                }
                        }
                }

                // no, we are just resetting the specific instance
                if (isset(self::$aInstances[$name]))
                {
                        unset(self::$aInstances[$name]);

                        if (isset(self::$aCaches[$name]))
                        {
                        	foreach (self::$aCaches[$name] as $cache)
                                {
                                	$cache->definitionReset();
                                }
                        }
                }

                // echo "  Count of definitions: " . count(self::$aInstances) . "\n";
        }

        // ----------------------------------------------------------------

        static public function registerCache($cache, $modelName)
        {
        	self::$aCaches[$modelName][] = $cache;
        }

        static public function listenToModelExtended($extensionClass, $modelName)
        {
                // var_dump('Model_Definitions::listenToModelExtended() called');
                // a model has just been extended

                // now, give the extension a chance to add new fields to
                // the model's definition

                $oDef = self::get($modelName);
                call_user_func_array(array($extensionClass, 'extendsModelDefinition'), array($oDef));
        }
}

// tell the Events mechanism that we need to be kept in the loop ..
Events_Manager::listensToEvents('Model_Definitions');

// ========================================================================
// ------------------------------------------------------------------------

class Model_Definition
{
        protected $primaryKeys          = null;
        protected $autoPrimaryKey       = true;
        protected $aForeignKeys         = array();
        protected $aFields              = array();
        protected $aMandatoryFields     = array();
        protected $aFieldsBySource      = array();
        protected $aoBehaviours         = array();
        protected $aViews               = array();
        protected $aRelationships       = array();
        protected $aIndices             = array();

        private $modelName              = "";
        private $modelClassName         = "";
        private $oModel                 = null;
        private $oRecord                = null;

        protected $oDefaultView         = null;

        const IS_OPT  = 1;
        const IS_MAND = 2;

        const TYPE_CHAR         = 1;
        const TYPE_INT          = 2;
        const TYPE_SIGNED_INT   = 3;
        const TYPE_BOOLEAN      = 4;
        const TYPE_TEXT         = 5;
        const TYPE_DATETIME     = 6;

        const SOURCE_START      = 1;
        const SOURCE_DB         = 2;
        const SOURCE_NON_DB     = 3;
        const SOURCE_END        = 4;

        public function __construct($modelName)
        {
                // echo "  Creating new definition for $modelName\n";
                $this->setModelName ($modelName);
                $this->setModelClassName ($modelName);

                // create the default view
                $this->oDefaultView = $this->addView('default');
        }

        public function getModelName()
        {
                return $this->modelName;
        }

        public function setModelName($modelName)
        {
                $this->modelName = $modelName;
        }

        /**
         *
         * @return array
         */
        public function getPrimaryKey ()
        {
                return $this->primaryKeys;
        }

        public function setPrimaryKey ($primaryKey)
        {
                if (!is_array($primaryKey))
                {
                        $this->setPrimaryKeys(array($primaryKey));
                }
                else
                {
                        $this->setPrimaryKeys($primaryKey);
                }
        }

        public function setPrimaryKeys($primaryKeys)
        {
                $this->primaryKeys = array();

                foreach ($primaryKeys as $field)
                {
                        if (!$this->isValidFieldName($field))
                        {
                                throw new Model_E_NoSuchField($field, $this->getModelName());
                        }
                        $this->primaryKeys[$field] = $field;
                }
        }

        public function setPrimaryKeyIsAutoGenerated($isAuto = true)
        {
                $this->autoPrimaryKey = $isAuto;
        }

        // ================================================================
        // METHODS FOR WORKING WITH FIELDS
        // ----------------------------------------------------------------

        public function addField ($name)
        {
                return $this->addFieldFromSource($name, Model_Definition::SOURCE_DB);
        }

        public function addFieldFromSource($name, $source)
        {
	        // create the field
                $oField = new Model_FieldDefinition($this, $name);
                $oField->fromSource($source);

                // add it to our list
                $this->aFields[$name] = $oField;

                // we add this field to the default view
                $this->oDefaultView->withField($name);

                // return the field, to allow further specification
                // if necessary
                return $oField;
        }

        public function addFakeField($fieldName)
        {
                return $this->addFieldFromSource($fieldName, Model_Definition::SOURCE_NON_DB);
        }

        public function getField ($name)
        {
                $this->requireValidFieldName($name);
                return $this->aFields[$name];
        }

        public function __get($name)
        {
                $this->requireValidFieldName($name);
                return $this->aFields[$name];
        }

        public function __set($name, $value)
        {
                throw new Exception();
        }

        public function setDefaultForField($name, $default)
        {
                $this->aFields[$name]->setDefaultValue($default);
        }

        // ----------------------------------------------------------------
        // this should only be called from Model_FieldDefinition

        public function setFieldSource (Model_FieldDefinition $oField, $source)
        {
                // add the required data to the definition
                $name = $oField->getName();
                $this->aFieldsBySource[$source][$name] = $oField;
        }

        public function getFieldsBySource($source)
        {
                constraint_mustBeGreaterThan($source, Model_Definition::SOURCE_START);
                constraint_mustBeLessThan($source, Model_Definition::SOURCE_END);

                return $this->aFieldsBySource[$source];
        }

        public function getFieldsBySourceAndView($source, $view)
        {
                $aFieldsFromSource = $this->getFieldsBySource($source);
                $aFieldsFromView   = $this->getFieldsFromView($view);

                // make sure the primary key is part of this view
                $primaryKeys       = $this->getPrimaryKey();
                foreach ($primaryKeys as $primaryKey)
                {
                        $aFieldsFromView[$primaryKey] = $primaryKey;
                }

                return array_intersect_key($aFieldsFromSource, $aFieldsFromView);
        }

        public function getFields ($fieldNames = array())
        {
                // if no names asked for, return the lot
                if (count($fieldNames) == 0)
                        return $this->aFields;

                // return only the requested fields
                $return = array();
                foreach ($fieldNames as $fieldName)
                {
                        $this->requireValidFieldName($fieldName);
                        $return[$fieldName] = $this->aFields[$fieldName];
                }

                return $return;
        }

        public function getMandatoryFields ()
        {
                return $this->aMandatoryFields;
        }

        public function setMandatoryField(Model_FieldDefinition $oField)
        {
                $this->aMandatoryFields[$oField->getName()] = $oField;
        }

        public function isMandatoryField ($fieldName)
        {
                return isset($this->aMandatoryFields[$fieldName]);
        }

        /**
         * examine the fields for a model, and make sure they are
         * fit and proper to go into the model
         *
         * as the meta class gains more knowledge about different
         * types of fields, the tests in this method could be
         * expanded
         */

        public function isValidFieldName($name)
        {
                return isset($this->aFields[$name]);
        }

        public function requireValidFieldName($field)
        {
                // for performance, we do not call isValidFieldName()
                // here

                if (!isset($this->aFields[$field]))
                {
                        throw new Model_E_NoSuchField($field, $this->getModelName());
                }
        }

        // ================================================================
        // Foreign key support
        // ----------------------------------------------------------------

        public function getForeignKey ($alias)
        {
                constraint_mustBeString($alias);

                try
                {
                        $oRelationship = $this->getRelationship($alias);
                }
                catch (Exception $e)
                {
                        throw new Model_E_ForeignKeyNotDefined($this->getModelName(), $alias);
                }

                $map['ourFields']   = $oRelationship->getOurFields();
                $map['theirFields'] = $oRelationship->getTheirFields();

                return $map;
        }

        // ================================================================
        // Support for model instances
        // ----------------------------------------------------------------

        public function getModelClassName()
        {
        	return $this->modelClassName;
        }

        public function setModelClassName($instance)
        {
        	if (!class_exists($instance))
                {
                	throw new PHP_E_NoSuchClass($instance);
                }

                $this->modelClassName = $instance;
        }

        public function getNewModel()
        {
                // do we need to change the record instance?
                if (isset($this->oModel))
                {
                        if (get_class($this->oModel) !== $this->modelClassName)
                        {
                                // yes we do
                                $this->resetModel();

                        }
                        else
                        {
                                // no, we do not
                        }
                }
                else
                {
                        // we have no record instance - create one
                        $this->resetModel();
                }

                return clone $this->oModel;
        }

        protected function resetModel()
        {
                // the class *must* set the
                $oModel = new $this->modelClassName();
                $oDef   = $oModel->getDefinition();

                if ($oDef !== $this)
                {
                        // var_dump($this);
                        // var_dump($oDef);

                        throw new Model_E_IncompatibleDefinition
                        (
                                $this->modelClassName,
                                $oDef->getModelName(),
                                $this->getModelName()
                        );
                }

                // if we get here, then we can change the instance model
                $this->oModel = $oModel;
        }

        public function setModel ($oInstance)
        {
                $this->setModelClassName(get_class($oInstance));
        }

        // ================================================================
        // Support for behaviours
        //
        // This will be replaced with the generic mixin / decorator
        // support before too long
        // ----------------------------------------------------------------

        public function addBehaviour(Datastore_RecordBehaviour_Base $oBehaviour)
        {
                $this->aoBehaviours[] = $oBehaviour;

                $oBehaviour->addFields($this);
        }

        public function &getBehaviours()
        {
                return $this->aoBehaviours;
        }

        // ================================================================
        // Support for views
        // ----------------------------------------------------------------

        public function getFieldsFromView($view = 'default')
        {
                $this->requireValidView($view);
                return $this->aViews[$view]->getFields();
        }

        public function addView($view)
        {
                constraint_mustBeString($view);

                $oView = new Model_View($this, $view);
                $this->aViews[$view] = $oView;

                return $oView;
        }

        public function getView($view)
        {
                $this->requireValidView($view);
                return $this->aViews[$view];
        }

        public function requireValidView($view)
        {
                if (!isset($this->aViews[$view]))
                {
                        throw new Model_E_NoSuchView($this->getModelName(), $view);
                }
        }

        // ================================================================
        // Relationship-based API
        // ----------------------------------------------------------------

        // ----------------------------------------------------------------
        // Create a one:one relationship between this table and another
        // table
        //
        // Use this method when you want findFromThis() to return
        // a Datastore_Record
        //
        // returns: Datastore_Relationship
        //          An object defining the relationship

        public function hasOne($alias)
        {
                constraint_mustBeString($alias);

                $oRelationship = new Model_Relationship($this);
                $oRelationship->hasOne($alias);

                $this->addRelationship($oRelationship, $alias);
                return $oRelationship;
        }

        public function doesHaveOne($oRecordB)
        {
                if ($oRecordB instanceof Model_Base)
                {
                        $theirRecord = $oRecordB->oDef->getModelName();
                }
                else if ($oRecordB instanceof Model_Definition)
                {
                        $theirRecord = $a_oRecordB->getModelName();
                }
                else
                {
                        // FIX ME: what are we going to throw here?
                        throw new Exception();
                }

                // do we have a relationship between this record and
                // record B?

                $oRelationship = $this->getRelationship($theirRecord);
                if ($oRelationship === null)
                {
                        // no, we do not
                        return false;
                }

                return $oRelationship->isHasOne();
        }

        // ----------------------------------------------------------------
        // Create a one:many relationship between this table and another
        // table
        //
        // Use this method when you want findFromThis() to return
        // a Datastore_Recordset
        //
        // returns: Datastore_Relationship
        //          An object defining the relationship

        public function hasMany($alias)
        {
                constraint_mustBeString($alias);

                $oRelationship = new Model_Relationship($this);
                $oRelationship->hasMany($alias);

                $this->addRelationship($oRelationship, $alias);

                return $oRelationship;
        }

        public function doesHaveMany($oRecordB)
        {
                if ($oRecordB instanceof Model_Base)
                {
                        $theirRecord = $oRecordB->oDef->getModelName();
                }
                else if ($oRecordB instanceof Model_Definition)
                {
                        $theirRecord = $a_oRecordB->getModelName();
                }
                else
                {
                        // FIX ME: what are we going to throw here?
                        throw new Exception();
                }

                // do we have a relationship between this record and
                // record B?

                $oRelationship = $this->getRelationship($theirRecord);
                if ($oRelationship === null)
                {
                        // no, we do not
                        return false;
                }

                return $oRelationship->isHasMany();
        }

        // ----------------------------------------------------------------
        // this method should only be called by the Model_Relationship
        // class

        protected function addRelationship($oRelationship, $alias)
        {
                // FIXME: we need to throw an exception if this alias
                //        is already in use
                $this->aRelationships[$alias] = $oRelationship;
        }

        public function getRelationship($alias)
        {
                constraint_mustBeString($alias);

                // FIXME: we need to throw an exception here if the
                //        alias is not valid

                if (!isset($this->aRelationships[$alias]))
                {
                        throw new Exception();
                }

                return $this->aRelationships[$alias];
        }

        public function requireValidRelationshipAlias($alias)
        {
                if (!isset($this->aRelationships[$alias]))
                {
                        // FIXME: we need an exception to throw here
                        throw new Exception();
                }
        }

        // ----------------------------------------------------------------
        // support for more interesting data structures

        public function inherits($modelName)
        {
                // does nothing for now
        }
}

// ========================================================================
// ------------------------------------------------------------------------

class Model_FieldDefinition
{
        protected $name                 = null;
        protected $oType                = null;
        protected $source               = Model_Definition::SOURCE_DB;
        protected $mandatory            = false;
        protected $validateMethod       = false;
        protected $defaultValue         = null;

        protected $modelName            = null;

        public function __construct(Model_Definition $oDef, $fieldName)
        {
                constraint_mustBeString($fieldName);
                $this->name = $fieldName;

                $this->modelName = $oDef->getModelName();

                // we must always have a type
                // users can override this if they wish
                $this->oType = new Model_Type_Generic();
        }

        // ================================================================
        // Support for field names
        // ================================================================

        public function getName()
        {
                return $this->name;
        }

        // ================================================================
        // Support for datatypes
        //
        // ================================================================

        // ----------------------------------------------------------------

        public function asType(Model_Type $oType)
        {
                $this->oType = $oType;
                $oDef->updateDispatchMapFromType($this->name, $this->oType);
        }

        // ================================================================
        // Support for data sources
        // ----------------------------------------------------------------

        public function fromSource($source)
        {
                constraint_mustBeGreaterThan($source, Model_Definition::SOURCE_START);
                constraint_mustBeLessThan($source, Model_Definition::SOURCE_END);

                $this->source = $source;

                // update the cache that Datastore_MetaRecord holds
                $oDef = Model_Definitions::getIfExists($this->modelName);
                $oDef->setFieldSource($this, $source);
        }

        // ================================================================
        // Support for mandatory fields
        // ----------------------------------------------------------------

        public function asMandatory()
        {
                $this->mandatory = true;

                // update the cache that Datastore_MetaRecord holds
                $oDef = Model_Definitions::getIfExists($this->modelName);
                $oDef->setMandatoryField($this);
        }

        public function isMandatory()
        {
                return $this->mandatory;
        }

        // ================================================================
        // Support for field validation
        // ----------------------------------------------------------------

        public function validateWhenSet()
        {
                $this->validateMethod = 'validate' . ucfirst($this->name);
        }

        public function mustValidateWhenSet()
        {
                return $this->validateMethod;
        }

        public function validateData(&$data)
        {
                $this->oType->validateData($data);
        }

        public function getValidateMethod()
        {
                return $this->validateMethod;
        }

        // ================================================================
        // Support for default values
        // ----------------------------------------------------------------

        public function getDefaultValue()
        {
                // if we do not have a default value, we rely on
                // the underlying datatype to have one
                if ($this->defaultValue === null)
                       return $this->oType->getDefaultValue();

                return $this->defaultValue;
        }

        public function setDefaultValue($mValue)
        {
                $this->defaultValue = $mValue;
        }

        // ================================================================
        // Support for filter input / escape output
        // ----------------------------------------------------------------

        public function filterInput($data)
        {
                if (!isset($this->oType))
                        return $data;

                return $this->oType->filterInput($data);
        }

        public function hasEscaper($escaper)
        {
                if (!isset($this->oType))
                        return false;

                if (!method_exists($this->oType, $escaper))
                        return false;

                return true;
        }

        public function requireValidEscaper($escaper)
        {
                if (!$this->hasEscaper($escaper))
                {
                        throw new Model_E_NoSuchEscaper(get_class($this->oType), $escaper);
                }
        }

        public function escapeOutput($escaper, $fieldName, $data)
        {
                $this->requireValidEscaper($escaper);
                return $this->oType->$escaper($fieldName, $data);
        }
}

// ========================================================================
// ------------------------------------------------------------------------

class Model_Relationship
{
        // ================================================================
        // HAS_* CONSTANTS
        //
        // These constants are used to keep track of what type of
        // relationship we are definining
        //
        // The constants are treated as bitwise flags.  Some combinations
        // of the flags may be invalid.
        //
        // Valid combinations are:
        //
        //      HAS_ONE
        //      HAS_ONE | BELONGS_TO
        //      HAS_MANY
        //      HAS_MANY | BELONGS_TO
        //
        // Invalid combinations will result in an exception being thrown

        // ----------------------------------------------------------------
        // Used in robustness checks, to make sure that the HAS_* constant
        // used is within boundaries

        const HAS_MIN = 1;

        // ----------------------------------------------------------------
        // Used to indicate a one:one relationship between two tables

        const HAS_ONE = 1;

        // ----------------------------------------------------------------
        // Used to indicate a one:many relationship between two tables

        const HAS_MANY = 2;

        // ----------------------------------------------------------------
        // Used to indicate a many:many relationship between two tables

        const MANY_TO_MANY = 4;

        // ----------------------------------------------------------------
        // Used in robustness checks, to make sure that the HAS_* constant
        // used is within boundaries

        const HAS_MAX = 7;

        // ----------------------------------------------------------------
        // What type of relationship are we modelling?

        protected $relationship = null;

        // ----------------------------------------------------------------
        // Record A's model

        protected $ourModelDef    = null;
        protected $ourFieldType   = null;
        protected $ourFields      = null;

        // ----------------------------------------------------------------
        // Record B's model

        protected $theirModelDef  = null;
        protected $theirFieldType = null;
        protected $theirFields    = null;

        // Record B's name
        //
        // When a relationship is defined, we cannot guarantee that record
        // B's model has been defined.  For convenience, we support
        // defining a relationship before B has been defined, and we do
        // our robustness checks the first time we need record B's model

        protected $theirModelName = null;

        // ----------------------------------------------------------------
        // many:many join table support

        protected $findViaModel   = null;

        // ================================================================
        // Constructor
        // ================================================================

        public function __construct(Model_Definition $ourModelDef)
        {
                $this->ourModelDef = $ourModelDef;
        }

        // ================================================================
        // Use these methods to create a new relationship!
        // ================================================================

        // ----------------------------------------------------------------
        // Set the relationship to be one:one
        //
        // We set the relationship to be one:one (HAS_ONE), and we set
        // record B in the relationship to be $a_szRecordB.
        //
        // returns: self

        public function hasOne()
        {
                if ($this->relationship === null)
                {
                        $this->relationship = Model_Relationship::HAS_ONE;
                        return $this;
                }
                else
                {
                	return $this->relationship & Model_Relationship::HAS_ONE;
                }
        }

        // ----------------------------------------------------------------
        // Set the relationship to be one:many
        //
        // We set the relationship to be one:many (HAS_MANY), and we set
        // record B in the relationship to be $a_szRecordB.
        //
        // returns: self

        public function hasMany()
        {
                if ($this->relationship === null)
                {
                        $this->relationship = Model_Relationship::HAS_MANY;
                        return $this;
                }
                else
                {
                	return $this->relationship & Model_Relationship::HAS_MANY;
                }
        }

        public function hasManyToMany()
        {
                if ($this->relationship === null)
                {
                        $this->relationship = Model_Relationship::MANY_TO_MANY;
                        return false;
                }

                return $this->relationship & Model_Relationship::MANY_TO_MANY;
        }
        
        // ================================================================
        // Use these methods to add detail to the relationship
        // ================================================================

        // ----------------------------------------------------------------
        // Set the name of record B

        public function theirModelIs($modelB)
        {
                constraint_mustBeString($modelB);
                $this->theirModelName = $modelB;

                return $this;
        }

        // ----------------------------------------------------------------
        // Set the name of the key into record B
        //
        // param:       $a_szField      string          in
        //              The name of the field in record B
        //
        // returns:     Datastore_Relationship
        //              Returns $this

        public function theirFieldIs($fieldName)
        {
                constraint_mustBeString($fieldName);
                $this->theirFields = array($fieldName);
                return $this;
        }

        public function theirFieldsAre($fieldNames)
        {
                constraint_mustBeArray($fieldNames);
                $this->theirFields = $fieldNames;
                return $this;
        }

        // ----------------------------------------------------------------
        // Get/set the name of the key into record B
        //
        // param:       $a_szField      string          in
        //              The name of the field in record B
        //              If left blank, this method acts as a getter
        //
        // returns:     Datastore_Relationship
        //              Returns $this if $a_szField is not blank
        //
        // returns:     string
        //              Returns the name of the field if $a_szField is
        //              blank, and the field name has been set
        //
        // returns:     null
        //              Returns null if $a_szField is blank, and the field
        //              name has not been set yet

        public function ourFieldIs($fieldName)
        {
                constraint_mustBeString($fieldName);
                $this->ourFields = array($fieldName);
                return $this;
        }

        public function ourFieldsAre($fieldNames)
        {
                constraint_mustBeArray($fieldNames);
                $this->ourFields = $fieldNames;
                return $this;
        }

        // ----------------------------------------------------------------
        // teach us about a join table

        public function foundVia($modelName, $modelAlias)
        {
                constraint_mustBeString($modelName);
                constraint_mustBeString($modelAlias);

                $this->findViaModelName  = $modelName;
                $this->findViaModelAlias = $modelAlias;
                $this->relationship      = Model_Relationship::MANY_TO_MANY;

                return $this;
        }
        
        // ================================================================
        // Use these methods to learn about the relationship
        // ================================================================

        // ----------------------------------------------------------------
        //
        // returns:     string
        //              Returns the name of the field if $a_szField is
        //              blank, and the field name has been set
        //
        // returns:     null
        //              Returns null if $a_szField is blank, and the field
        //              name has not been set yet

        public function getTheirFields()
        {
                return $this->theirFields;
        }

        public function getOurFields()
        {
                return $this->ourFields;
        }

        public function getTheirModelName()
        {
                return $this->theirModelName;
        }

        public function getFindViaModelName()
        {
                return $this->findViaModelName;
        }

        public function getFindViaModelAlias()
        {
                return $this->findViaModelAlias;
        }

        public function cloneTheirRecord()
        {
                $this->requireTheirModelDefinition();
                $oRecordB = $this->theirModelDef->getNewRecord();

                return $oRecordB;
        }

        // ================================================================
        // Helper methods for checking on the state of this object
        // ================================================================

        public function requireRelationshipDefined()
        {
                if ($this->relationship === null)
                {
                        throw new Exception();
                }
        }

        public function requireTheirModelName()
        {
                if ($this->theirModelName === null)
                {
                        // FIXME: what exception are we going to throw?

                        throw new Exception();
                }
        }

        public function requireTheirModelDefinition()
        {
                $this->requireTheirModelName();

                if (isset($this->theirModelDef))
                {
                        return;
                }

                $this->theirModelDef = Model_Definitions::get($this->theirModelName);
        }
}

// ========================================================================
// ------------------------------------------------------------------------

class Model_View
{
        protected $name         = null;
        protected $aFields      = array();

        public $oDef = null;

        function __construct(Model_Definition $oDef, $viewName)
        {
                constraint_mustBeString($viewName);

                $this->oDef = $oDef;
                $this->name = $viewName;
        }

        public function withField($fieldName)
        {
                // the field must be a valid field
                // Model_Definition::getField() will do the check
                $this->aFields[$fieldName] = $this->oDef->getField($fieldName);

                return $this;
        }

        public function withAllFields()
        {
                $this->aFields = $this->oDef->getFields();
        }

        public function exceptField($fieldName)
        {
                if (isset($this->aFields[$fieldName]))
                {
                        unset($this->aFields[$fieldName]);
                }
        }

        public function getFields()
        {
                return $this->aFields;
        }
}

// ========================================================================
// ------------------------------------------------------------------------

interface Model_Type
{
        public function getDefaultValue();
        public function filterInput($data);
        public function validateData(&$data);
}

class Model_Type_Generic extends Obj
        implements Model_Type
{
        public function getDefaultValue()
        {
                return null;
        }

        public function filterInput($data)
        {
                // do nothing
                return $data;
        }

        public function validateData(&$data)
        {
                // do nothing
        }

        public function escapeOutputForHtml($name, $data)
        {
                return htmlentities($data);
        }

        public function escapeOutputForXml($name, $data)
        {
                $convertedData = str_replace(array('<', '>', '&'), array ('&lt;', '&gt;', '&amp;'), $data);

                // convert anything else into numerical entities
                // to be done

                return '<' . $name . '>' . $convertedData . '</' . $name . '>';
        }
}

// ========================================================================
// ------------------------------------------------------------------------

interface Model_Extension
{
        static public function extendsModelDefinition(Model_Definition $oDef);
}

?>