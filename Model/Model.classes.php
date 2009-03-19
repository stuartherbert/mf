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
// ========================================================================

// ========================================================================
// TODO: make relationships support primary keys containing more than one
//       field
// ========================================================================

class Model
implements Iterator
{
        protected $aData                = array();
        protected $primaryKey           = null;
        protected $iterKey              = 0;
        protected $needSave             = false;
        protected $readOnly             = false;
        protected $definitionName       = null;

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

                $oDef = $this->getDefinition($modelName);
                $this->primaryKey = $oDef->getPrimaryKey();

                if (count($aData) > 0)
                {
                        $this->setData($aData, $dataAction);
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

        // ================================================================
        // Support for managing the data held in the record
        // ----------------------------------------------------------------

        public function &getData ()
        {
                return $this->aData;
        }

        public function resetData ()
        {
                $this->requireWritable();

                $this->aData = array();
                $this->resetNeedSave();
        }

        /**
         * set all the fields of this record in one go
         */

        public function setData ($mData, $dataAction = Model::REPLACE_DATA)
        {
                $this->requireWritable();

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

                $this->setFieldsToDefaults();
                $this->setNeedSave();
        }

        protected function replaceDataWithArray($aData)
        {
                $this->requireWritable();
                $oDef = $this->getDefinition();

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
                $this->requireWritable();
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
                $this->resetNeedSave();
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

        public function getField ($fieldName)
        {
                $oDef = $this->getDefinition();

                // the field must exist
                $oDef->requireValidFieldName($fieldName);

                // do we have a getter for this method?
                $method = 'get' . ucfirst($fieldName);
                if (method_exists($this, $method))
                {
                        return $this->$method();
                }

                // do we have any data for this field?
                if (isset($this->aData[$fieldName]))
                {
                        return $this->aData[$fieldName];
                }

                // we're out of ideas, so return null
                return null;
        }

        public function resetField ($fieldName)
        {
                $this->requireWritable();

                $this->setField($fieldName, null);
                $this->setNeedSave();
        }

        public function setField ($fieldName, $data)
        {
                $this->requireWritable();
                $oDef = $this->getDefinition();

                // are we trying to set a field that has been defined?
                $oDef->requireValidFieldName($fieldName);

                // validate the field
                // throws an exception if things are not good
                $this->validateField($fieldName, $data);

                // do we have a setter defined?
                $method = 'set' . ucfirst($fieldName);
                if (method_exists($this, $method))
                {
                        if ($this->$method($data) !== false)
                        {
                                $this->setNeedSave();
                        }

                        return;
                }

                // special case: are we actually resetting the field?
                if ($data === null)
                {
                        if (!isset($this->aData[$fieldName]))
                        {
                                // we're not changing the state of the field
                                return;
                        }

                        unset($this->aData[$fieldName]);
                        $this->setNeedSave();
                        return;
                }

                // if we get here, then we'll just store the data nice
                // and quiet like

                $this->aData[$fieldName] = $data;
                $this->setNeedSave();
        }

        public function hasField ($fieldName)
        {
                $oDef = $this->getDefinition();
                if (!$oDef->isValidFieldName($fieldName))
                {
                        return false;
                }

                if (!isset($this->aData[$fieldName]))
                {
                        return false;
                }

                return true;
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
                return $this->hasField($fieldName);
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
                $this->requireWritable();

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
                $this->requireWritable();

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

        public function getNeedSave ()
        {
                return $this->needSave;
        }

        public function resetNeedSave()
        {
                $this->requireWritable();
                $this->needSave = false;
        }

        public function setNeedSave()
        {
                $this->requireWritable();
                $this->needSave = true;
        }

        // ================================================================
        // Support for marking a record as read only
        // ----------------------------------------------------------------

        public function isReadOnly ()
        {
                return $this->readOnly;
        }

        public function resetReadOnly ()
        {
                $this->readOnly = false;
        }

        public function setReadOnly ()
        {
                // readonly objects cannot be saved
                $this->resetNeedSave();
                $this->readOnly = true;
        }

        public function requireWritable()
        {
                if ($this->readOnly == true)
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

        public function toString()
        {
                return $this->name;
        }
}

final class Model_Definitions
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
}

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
        // Support for record instances
        // ----------------------------------------------------------------

        public function getNewRecord ()
        {
                // do we need to change the record instance?
                if (isset($this->oRecord))
                {
                        if (get_class($this->oRecord->oModel) !== $this->modelClassName)
                        {
                                // yes we do
                                $this->resetRecord();

                        }
                        else
                        {
                                // no, we do not
                        }
                }
                else
                {
                        // we have no record instance - create one
                        $this->resetRecord();
                }

                return clone $this->oRecord;
        }

        protected function resetRecord()
        {
                // the class *must* set the
                $oRecord = new Datastore_Record($this->modelClassName);
                if ($oRecord->oDef !== $this)
                {
                        throw new Model_E_IncompatibleDefinition
                        (
                                $this->recordClassName,
                                $oRecord->oDef->getModelName(),
                                $this->getModelName()
                        );
                }

                // if we get here, then we can change the instance record
                $this->oRecord = $oRecord;
        }

        // ================================================================
        // Support for behaviours
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

        public function asType(Model_Type_I_Datatype $oType)
        {
                $this->oType = $oType;
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

        public function foundVia($alias, $aliasAlias)
        {
                constraint_mustBeString($alias);
                constraint_mustBeString($aliasAlias);

                $this->findViaAlias = $alias;
                $this->findViaAliasAlias = $aliasAlias;

                $this->relationship = $this->relationship & Model_Relationship::MANY_TO_MANY;
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

class Model_Type_Generic
{
        public function getDefaultValue()
        {
                return null;
        }

        public function validateData(&$a_mData)
        {
                // do nothing
        }
}

?>