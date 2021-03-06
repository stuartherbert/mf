<?php

// ========================================================================
//
// Datastore/Datastore.classes.php
//              Classes for the Datastore component
//
//              Part of the Methodosity Framework for PHP applications
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2007-2010 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2007-08-11   SLH     Consolidated from separate files
// 2007-09-17   SLH     Added support for views in
//                      Datastore_Table::findAllBy()
// 2008-01-06   SLH     Redesigned Datastore_Record to be an encapsulating
//                      layer around a separate Model object
// 2008-01-07   SLH     Made Datastore_Records support iteration without
//                      the user having to create an extra iterator object
//                      of their own
// 2008-02-05   SLH     Connectors can now add new methods to Datastore
// 2008-02-05   SLH     Standardisation of the API for datastore
//                      connectors - even non-SQL ones :)
// 2008-05-22   SLH     Dropped Datastore_Records
//                      Dropped Datastore_RecordsAdapter
//                      Dropped Datastore_SharedRecords
//                      Dropped Datastore_BaseRecord
//                      Dropped Datastore_RecordAdapter
//                      Dropped support for Datastore_Records from
//                      Datastore
//                      Dropped Datastore_Table
//                      Added Datastore-independent Datastore_Query
// 2008-07-17   SLH     Added two passes to createRecords and
//                      updateRecords, to catch errors before making any
//                      changes to the database
// 2008-07-19   SLH     Datastore and Datastore_Record both inherit from
//                      the MF_Core class
// 2008-07-25   SLH     No longer require the Model definition to be
//                      an attribute of models
// 2008-07-26   SLH     Removed unused code
// 2008-08-07   SLH     Added support for datastore-specific storage
//                      mappings
// 2008-08-11   SLH     Added Datastore::newRecord()
// 2008-08-13   SLH     Added test to catch models with no storage
//                      defined
// 2008-08-13   SLH     Fixed bug in Datastore_RdbmsQuery that prevented
//                      building SQL statement with no where clause
// 2008-08-13   SLH     Throw Datastore_E_QueryFailed if we have more
//                      fields to bind than bind slots in the query
// 2009-02-28   SLH     Moved RDBMS classes out into separate module
// 2009-03-17   SLH     Models do not have to be defined before we can
//                      specify where they can be stored (required because
//                      of move to autoload support)
// 2009-03-18   SLH     Fixes for supporting complex primary keys
// 2009-03-19   SLH     More fixes for supporting complex primary keys
// 2009-03-23   SLH     Switched to creating datastore records via the
//                      datastore (to allow for future flexibility)
// 2009-03-25   SLH     Basic many:many support
// 2009-05-20   SLH     Updated to work with latest changes to Model
// 2009-05-23   SLH     No longer needs Core to help w/ calling methods
// 2009-05-23   SLH     Fix for missing storageMap member of Datastore
// 2009-09-15	SLH	Model renamed to DataModel
// ========================================================================

// ========================================================================
// Base class for all types of datastore
// ------------------------------------------------------------------------

class Datastore extends Obj
{
        const ORDER_START = 0;
        const ORDER_ASC   = 1;
        const ORDER_DESC  = 2;
        const ORDER_END   = 3;

        const HINT_START   = 0;
        const HINT_CREATE  = 1;
        const HINT_UPDATE  = 2;
        const HINT_UNKNOWN = 3;
        const HINT_END     = 4;

        public $oConnector = null;

        protected $storageMap = array();

        public function __construct ($oConnector)
        {
                // this is our link to the individual database
                $this->oConnector = $oConnector;
                $this->oConnector->setDatastore($this);
        }

        public function isSameAs(Datastore $oDB)
        {
                // cheapest test of all first
                if ($oDB === $this)
                {
                        return true;
                }

                // we are not looking at ourselves, so we must see what
                // we are looking at

                $aMyDetails    = $this->getDatastoreDetails();
                $aTheirDetails = $oDB->getDatastoreDetails();

                // cheap test first!

                if (count($aMyDetails) != count($aTheirDetails))
                {
                        return false;
                }

                // this test is more expensive, but it is also the
                // absolute test
                $aDiff = array_diff_assoc($aMyDetails, $aTheirDetails);
                if (count($aDiff) > 0)
                {
                        return false;
                }

                return true;
        }

        public function getDatastoreDetails()
        {
                return $this->oConnector->getDetails();
        }

        public function getConnection()
        {
                return $this->oConnector;
        }

        public function escapeString($string)
        {
                return $this->oConnector->escapeString($string);
        }

        // ----------------------------------------------------------------
        // support for datastore-specific storage schemes
        // ----------------------------------------------------------------

        public function storeModel($model)
        {
                // we cannot guarantee that the model exists when
                // storeModel() is called
                //
                // constraint_mustBeValidModel($model);

        	$oStorageMap = $this->oConnector->storeModel($model);
                $this->storageMap[$oStorageMap->name] = $oStorageMap;

                return $oStorageMap;
        }

        public function getStorageForModel($modelName)
        {
                if (!isset($this->storageMap[$modelName]))
                {
                	throw new Datastore_E_StorageUnknown($modelName);
                }

        	return $this->storageMap[$modelName];
        }

        // ----------------------------------------------------------------
        // Support for associating a model with a datastore
        // ----------------------------------------------------------------

        public function getNewDatastoreProxy(DataModel $oModel)
        {
                return $this->oConnector->getNewDatastoreProxy($oModel);
        }

        // ----------------------------------------------------------------
        // Support for individual records
        // ----------------------------------------------------------------

        public function newRecord($modelName)
        {
        	$oMap  = $this->getStorageForModel($modelName);
                $recordClassName = $oMap->recordClassName;

                $return = new $modelName();

                return $return;
        }

        public function createRecord (Datastore_Record $oRecord)
        {
                $oDef  = $oRecord->getDefinition();
                $oMap  = $this->getStorageForModel($oDef->getModelName());

                $oStmt = $this->oConnector->getStatement();
                $oStmt->beCreateStatement($oDef, $oMap);
                $oStmt->bindValues($oRecord);
                $oStmt->execute();

                $oRecord->setDatastoreWhereStored($this);
                $oRecord->resetNeedsSaving();
        }

        public function retrieveRecord (Datastore_Record $oRecord, $fields, $view = 'default')
        {
                $oDef  = $oRecord->getDefinition();
                $oMap  = $this->getStorageForModel($oDef->getModelName());

                $oStmt = $this->oConnector->getStatement();
                $oStmt->beRetrieveStatement($oDef, $oMap, $fields, $view);
                $oStmt->bindValues($fields);

                try
                {
                        $aRecords = $oStmt->execute();
                }
                catch (Exception $e)
                {
                        throw new Datastore_E_RetrieveFailed($e->getMessage(), $e);
                }

                $oRecord->setFields(array_shift($aRecords));
                $oRecord->resetNeedsSaving();
                $oRecord->setDatastoreWhereStored($this);
        }

        public function updateRecord (Datastore_Record $oRecord)
        {
                $oDef  = $oRecord->getDefinition();
                $oMap  = $this->getStorageForModel($oDef->getModelName());

                $oStmt = $this->oConnector->getStatement();
                $oStmt->beUpdateStatement($oDef, $oMap);
                $oStmt->bindValues($oRecord);
                $oStmt->execute();

                $oRecord->resetNeedsSaving();
                $oRecord->setDatastoreWhereStored($this);
        }

        public function deleteRecord (Datastore_Record $oRecord)
        {
                $oDef  = $oRecord->getDefinition();
                $oMap  = $this->getStorageForModel($oDef->getModelName());

                $oStmt = $this->oConnector->getStatement();
                $oStmt->beDeleteStatement($oDef, $oMap);
                $oStmt->bindValues($oRecord);
                $oStmt->execute();

                $oRecord->emptyWithoutSave();
                $oRecord->resetDatastoreWhereStored();
        }

        // ----------------------------------------------------------------
        // Support for a set of records
        // ----------------------------------------------------------------

        /**
         * We have three scenarios to support:
         *
         * a) $records is an array of individual records, or
         * b) $records is an array of sets of records, or
         * c) $records is a mixture of both (a) and (b)
         *
         * All of these must work, somehow :)
         */

        public function createRecords ($records)
        {
        	constraint_mustBeArray($records);

                // step 1: loop over the records, and make sure we are
                //         happy with the contents
                //
                // this allows us to catch errors before attempting to
                // make changes to the underlying datastore

                foreach ($records as $recordLine)
                {
                        if (is_array($recordLine))
                        {
                                foreach ($recordLine as $record)
                                {
                                        constraint_mustBeDatastoreRecord($record);
                                }
                        }
                        else
                        {
                                constraint_mustBeDatastoreRecord($recordLine);
                        }
                }

                // step 2: store the records in the datastore

                foreach ($records as $recordLine)
                {
                	if (is_array($recordLine))
                        {
                                foreach ($recordLine as $record)
                                {
                                        $this->createRecord($recordLine);
                                }
                        }
                        else
                        {
                        	$this->createRecord($recordLine);
                        }
                }
        }

        public function updateRecords ($records)
        {
                constraint_mustBeArray($records);

                // step 1: loop over the records, and make sure we are
                //         happy with the contents
                //
                // this allows us to catch errors before attempting to
                // make changes to the underlying datastore

                foreach ($records as $recordLine)
                {
                	if (is_array($recordLine))
                        {
                        	foreach ($recordLine as $record)
                                {
                                	constraint_mustBeDatastoreRecord($record);
                                }
                        }
                        else
                        {
                        	constraint_mustBeDatastoreRecord($recordLine);
                        }
                }

                // step 2: update each record in the datastore

                foreach ($records as $recordLine)
                {
                        if (is_array($recordLine))
                        {
                                foreach ($recordLine as $record)
                                {
                                        // skip records that have not changed
                                        if ($record->getNeedsSaving())
                                        {
                                                $this->updateRecord($recordLine);
                                        }
                                }
                        }
                        else
                        {
                                if ($record->getNeedSaving())
                                {
                                	$this->updateRecord($recordLine);
                                }
                        }
                }
        }

        public function deleteRecords ($aRecords)
        {
                $oDef  = $oRecords->getDefinition();
                $oMap  = $this->getStorageMap($oDef->getModelName());

                $oStmt = $this->oConnector->getStatement();
                $oStmt->beDeleteStatement($oDef, $oMap);

                foreach ($oRecords as $oRecord)
                {
                        $oStmt->bindValues($oRecord);
                        $oStmt->execute();
                }

                $oRecords->emptyWithoutSave();
        }

        // ----------------------------------------------------------------
        // Support for searching
        // ----------------------------------------------------------------

        public function newQuery()
        {
        	$query = $this->oConnector->getQuery();
                return $query;
        }

        /**
         * because we try to support more than just SQL-based databases,
         * it is impossible for the Datastore class to add any real value
         * here
         *
         * supporting searches is best done in the connector
         */

        public function search(Datastore_Query $query)
        {
                // var_dump($query);
                
                $oStmt = $this->oConnector->getStatement();
                $oStmt->beQueryStatement($query->getRawQuery(), $query->getPrimaryKey());

                $oStmt->bindAnonymousValues($query->getTokens());
                $aRecords = $oStmt->execute();

                // debug_vardump(__FILE__, __LINE__, __FUNCTION__, '$aRecords', $aRecords);

                // special case: the developer requested a single
                // record

                if ($query->queryType == Datastore_Query::FIND_FIRST)
                {
                        reset($aRecords);
                        // debug_vardump(__FILE__, __LINE__, __FUNCTION__, 'first record', current($aRecords));
                	return $query->extractIntoRecords(current($aRecords));
                }

                // if we get here, we are returning more than one set
                // of records

                $return = array();
                foreach ($aRecords as $record)
                {
                	$return[] = $query->extractIntoRecords($record);
                }

                return $return;
        }

        // ----------------------------------------------------------------
        // Miscellaneous operations
        // ----------------------------------------------------------------

        public function deleteAllRecords ($model)
        {
                $oMap  = $this->getStorageForModel($model);
                $oStmt = $this->oConnector->getStatement();
                $oStmt->beTruncateStatement($oMap);
                $oStmt->execute();
        }

        // ----------------------------------------------------------------
        // Support for calling methods defined on the connector
        // ----------------------------------------------------------------

        function __call($funcName, $args)
        {
        	// a method has been called that we do not recognise
                // we will pass this on to the connector

                if (!method_exists($this->oConnector, $funcName))
                {
                	throw new PHP_E_NoSuchMethod($funcName, get_class($this->oConnector));
                }

                // if we get here, then the method exists

                return call_user_func_array(array($this->oConnector, $funcName), $args);
        }
}

// ========================================================================
//
// Base classes for the statement command pattern
//
// ------------------------------------------------------------------------

class Datastore_BaseStatement
{
        protected $oConnector   = null;

        public function __construct($oConnector)
        {
                $this->oConnector = $oConnector;
        }

        public function beCreateStatement(DataModel_Definition $oDef, Datastore_StorageMap $oMap)
        {
        	throw new Datastore_E_OperationNotSupported('create');
        }

        public function beRetrieveStatement(DataModel_Definition $oDef, Datastore_StorageMap $oMap, $retrieveField, $view)
        {
        	throw new Datastore_E_OperationNotSupported('retrieve');
        }

        public function beUpdateStatement(DataModel_Definition $oDef, Datastore_StorageMap $oMap)
        {
        	throw new Datastore_E_OperationNotSupported('update');
        }

        public function beDeleteStatement(DataModel_Definition $oDef, Datastore_StorageMap $oMap)
        {
        	throw new Datastore_E_OperationNotSupported('delete');
        }

        public function beTruncateStatement(Datastore_StorageMap $oMap)
        {
        	throw new Datastore_E_OperationNotSupported('truncate');
        }
}

class Datastore_Passthru_Statement extends Datastore_BaseStatement
{
        const CREATE   = 1;
        const RETRIEVE = 2;
        const UPDATE   = 3;
        const DELETE   = 4;
        const TRUNCATE = 5;

        protected $aOpMap = array
        (
                1 => 'doCreate',
                2 => 'doRetrieve',
                3 => 'doUpdate',
                4 => 'doDelete',
                5 => 'doTruncate',
        );

        protected $operation    = array();
        protected $returnRows   = false;

        public function beCreateStatement(DataModel_Definition $oDef)
        {
                $this->operation['type']  = Datastore_Passthru_Statement::CREATE;
                $this->operation['model'] = $oDef;

                $this->returnRows         = false;
        }

        public function beRetrieveStatement(DataModel_Definition $oDef, $retrieveField, $view)
        {
                $this->operation['type']          = Datastore_Passthru_Statement::RETRIEVE;
                $this->operation['model']         = $oDef;
                $this->operation['retrieveField'] = $retrieveField;
                $this->operation['view']          = $view;

                $this->returnRows                 = true;
        }

        public function beUpdateStatement(DataModel_Definition $oDef)
        {
                $this->operation['type']  = Datastore_Passthru_Statement::UPDATE;
                $this->operation['model'] = $oDef;

                $this->returnRows         = false;
        }

        public function beDeleteStatement(DataModel_Definition $oDef)
        {
                $this->operation['type']  = Datastore_Passthru_Statement::DELETE;
                $this->operation['model'] = $oDef;

                $this->returnRows         = false;
        }

        public function beTruncateStatement($table)
        {
                $this->operation['type']  = Datastore_Passthru_Statement::TRUNCATE;
                $this->operation['table'] = $table;

                $this->returnRows         = false;
        }

        public function bindValues(Datastore_Record $oRecord)
        {
                $this->operation['record'] =& $oRecord->geData();
        }

        public function execute()
        {
                $this->requireValidOperationType();

                $func = $this->aOpMap[$this->operation['type']];
                $this->$func();

                if (!$this->returnRows)
                {
                        return;
                }

                $aReturn = array();
                while ($aRec = $this->oConnector->fetchAssoc())
                {
                        $aReturn[$aRec[$this->primaryKey]] = $aRec;
                }

                if (count($aReturn) == 0)
                {
                        throw new Datastore_E_QueryFailed(get_class($this), 'No matching rows found');
                }

                return $aReturn;
        }

        public function doCreate()
        {
                throw new Datastore_E_OperationNotSupported('create');
        }

        public function doRetrieve()
        {
                throw new Datastore_E_OperationNotSupported('retrieve');
        }

        public function doUpdate()
        {
        	throw new Datastore_E_OperationNotSupported('update');
        }

        public function doDelete()
        {
        	throw new Datastore_E_OperationNotSupported('delete');
        }

        public function doTruncate()
        {
        	throw new Datastore_E_OperationNotSupported('truncate');
        }

        protected function requireValidOperationType()
        {
                if (!isset($this->operation['type']))
                {
                        throw new Datastore_E_QueryFailed('<unknown>', 'No operation type set');
                }

                if (!isset($this->aOpMap[$this->operation['type']]))
                {
                        throw new Datastore_E_QueryFailed('<unknown>', 'Invalid operation type ' . $this->operation['type']);
                }
        }
}

// ========================================================================
//
// Base class for the Datastore connectors
//
// ------------------------------------------------------------------------

class Datastore_BaseConnector
{
        protected $oStore         = null;
        protected $statementClass = null;
        protected $queryClass     = null;
        protected $proxyClass     = null;
        protected $aDetails       = null;

        public function __construct($statementClass, $queryClass, $proxyClass = 'Datastore_Record')
        {
        	$this->statementClass = $statementClass;
                $this->queryClass     = $queryClass;
                $this->proxyClass     = $proxyClass;
        }

        public function requireConnected()
        {
                if (!$this->isConnected())
                {
                        throw new Datastore_E_NotConnected(get_class($this));
                }
        }

        public function getQuery()
        {
        	$queryClass = $this->queryClass;
                return new $queryClass($this->oStore);
        }

        public function setDatastore(Datastore $oStore)
        {
                $this->oStore = $oStore;
        }

        public function getStatement()
        {
                $statementClass = $this->statementClass;
                return new $statementClass($this);
        }

        public function getDetails()
        {
                return $this->aDetails;
        }

        public function getNewDatastoreProxy(DataModel $oModel)
        {
                $proxyClass = $this->proxyClass;
                return new $proxyClass($oModel);
        }
}

class Datastore_Storage
{
	public $name            = null;
        public $recordClassName = null;

        public function __construct($name)
        {
                $this->name = $name;
        }

        public function actsAs($name)
        {
                $this->recordClassName = $name;
                return $this;
        }
}

// ========================================================================
//
// Datastore_Record
//
// ------------------------------------------------------------------------

class Datastore_Record extends Obj
{
        public    $oModel                       = null;

        protected $oDatastoreWhereStored        = null;
        protected $storageHint                  = Datastore::HINT_UNKNOWN;

        // ================================================================
        // All the functionality aggregated from the DataModel that we are
        // encapsulating

        public function __construct(DataModel $oModel)
        {
                $this->oModel = $oModel;
        }

        // ================================================================
        // Cloning support
        // ----------------------------------------------------------------

        public function __clone()
        {
        	$this->oModel = clone $this->oModel;
        }

        // ================================================================
        // Basic operations
        // ----------------------------------------------------------------

        public function store($oDB = null)
        {
                static $oLastDB = null;

                // some tests do not make sense if we are writing the
                // record to a different datastore
                if ($oDB === $oLastDB)
                {
                        if (!$this->getNeedsSaving())
                        {
                                return;
                        }
                }

                // if the record is empty, there is nothing to save
                if (!$this->hasData())
                {
                        return;
                }

                // if we haven't been told which datastore to use,
                // assume we need to save ourselves into the last one used
                if ($oDB === null)
                {
                       $this->requireStoredInDatastore();
                       $oDB = $this->getDatastoreWhereStored();
                }

                // remember what the last datastore was
                $oLastDB = $oDB;

                if (!$this->execPreBehaviours($oDB, 'store'))
                {
                        return;
                }

                $hint = $this->getStorageHint();
                switch ($hint)
                {
                        case Datastore::HINT_UPDATE:
                               $this->update($oDB);
                               break;

                        default:
                               $this->create($oDB);
                               break;
                }

                $this->execPostBehaviours($oDB, 'store');
        }

        public function retrieve($oDB, $uid, $view = 'default')
        {
                $this->beforeRetrieve($oDB);
                $this->execPreBehaviours($oDB, 'retrieve');

                $primaryKey = $this->getPrimaryKey();

                // do we have a simple or complex primary key?
                if (count($primaryKey) == 1)
                {
                        $fields = array(current($primaryKey) => $uid);
                }
                else
                {
                        // we have a complex primary key
                        $fields = array();
                        foreach ($primaryKey as $key)
                        {
                                $fields[$key] = $uid[$key];
                        }
                }

                // now, retrieve the record
                $oDB->retrieveRecord($this, $fields, $view);

                $this->execPostBehaviours($oDB, 'retrieve');
                $this->afterRetrieve($oDB);
        }

        protected function retrieve_($oDB, $aMethod, $aArgs)
        {
                // NOTE: we do not call *this* record's beforeRetrieve()
                //       because we are retrieving data from another
                //       record

                // step 1: decode the parameters that we have

                if (!isset($aMethod[0]))
                {
                        // FIXME: throw our own exception
                        throw new Exception();
                }

                $alias = $aMethod[0];
                if (isset($aMethod[1]))
                {
                        $view = $aMethod[1];
                }
                else
                {
                        $view = 'default';
                }

                // step 2: is this a valid relationship?
                //
                //         this will throw an exception if the alias is
                //         not valid

                $oRelationship = $this->oModel->getDefinition()->getRelationship($alias);

                // step 3: clone the object we are returning
                //
                //         Datastore_Relationship will ensure it is the
                //         correct type

                $oQuery = $oDB->newQuery();

                // we need to build up the list of field/value pairs to request
                $theirFields = $oRelationship->getTheirFields();
                $theirValues = $this->getFields($oRelationship->getOurFields());

                reset($theirFields);
                reset($theirValues);

                $keyPairs = array();
                foreach ($theirFields as $theirField)
                {
                        $keyPairs[$theirField] = current($theirValues);
                        next($theirValues);
                }

                if ($oRelationship->hasOne())
                {

                        $oQuery->findFirst($oRelationship->getTheirModelName(), $view)
                               ->withForeignKeys($keyPairs);
                }
                else if ($oRelationship->hasManyToMany())
                {
                        $oQuery->findEvery($oRelationship->getFindViaModelName())
                               ->withForeignKeys($keyPairs)
                               ->includingOnly($oRelationship->getFindViaModelAlias());
                }
                else
                {
                	$oQuery->findEvery($oRelationship->getTheirModelName(), $view)
                               ->withForeignKeys($keyPairs);
                }

                // step 4: retrieve the data
                $return = $oDB->search($oQuery);

                // NOTE: we do not call *this* record's afterRetrieve()
                //       because we are retrieving data from another
                //       record

                return $return;
        }

        protected function beforeRetrieve($oDB)
        {
        }

        protected function afterRetrieve($oDB)
        {
        }

        public function delete()
        {
                $this->deleteFrom($this->getDatastoreWhereStored());
        }

        public function deleteFrom($oDB)
        {
                $uid = $this->getUniqueId();

                if ($uid === null)
                {
                        throw new Datastore_E_NoValueForPrimaryKey($this->getPrimaryKey());
                }

                $this->beforeDelete($oDB);
                if (!$this->execPreBehaviours($oDB, $this, 'delete'))
                {
                        return;
                }

                $oDB->deleteRecord($this);

                $this->emptyWithoutSave();

                $this->execPreBehaviours($oDB, $this, 'delete');
                $this->afterDelete($oDB);
        }

        protected function beforeDelete($oDB)
        {
        }

        protected function afterDelete($oDB)
        {
        }

        public function create($oDB = null)
        {
                if (!$this->getNeedsSaving())
                        return;

                if ($oDB === null)
                {
                       $this->requireStoredInDatastore();
                       $oDB = $this->getDatastoreWhereStored();
                }

                $this->beforeCreate($oDB);
                if (!$this->execPreBehaviours($oDB, $this, 'create'))
                {
                        return;
                }

                $oDB->createRecord($this);

                $this->resetNeedsSaving();
                $this->execPostBehaviours($oDB, $this, 'create');
                $this->afterCreate($oDB);
        }

        protected function beforeCreate($oDB)
        {
        }

        protected function afterCreate($oDB)
        {
        }

        public function update($oDB = null)
        {
                if (!$this->getNeedsSaving())
                        return;

                if ($oDB === null)
                {
                       $this->requireStoredInDatastore();
                       $oDB = $this->getDatastoreWhereStored();
                }

                $this->beforeUpdate($oDB);
                if (!$this->execPreBehaviours($oDB, 'update'))
                {
                        return;
                }

                $oDB->updateRecord($this);

                $this->resetNeedsSaving();
                $this->execPostBehaviours($oDB, 'update');
                $this->afterUpdate($oDB);
        }

        protected function beforeUpdate($oDB)
        {
        }

        protected function afterUpdate($oDB)
        {
        }

        // ================================================================
        // Support for doing things before and after an operation
        // ----------------------------------------------------------------

        protected function execPreBehaviours($oDB, $operation)
        {
                // if we have no Behaviours, bail early
                $aBehaviours =& $this->oModel->getDefinition()->getBehaviours();
                if (count($aBehaviours) == 0)
                {
                        return true;
                }

                $operation = 'pre' . ucfirst($operation) . 'Record';
                $continue  = true;

                foreach ($aBehaviours as $oBehaviour)
                {
                        if (!$oBehaviour->$operation($oDB, $this->oModel))
                        {
                                $continue = false;
                        }
                }

                return $continue;
        }

        protected function execPostBehaviours($oDB, $operation)
        {
                // if we have no Behaviours, bail early
                $aBehaviours =& $this->oModel->getDefinition()->getBehaviours();
                if (count($aBehaviours) == 0)
                {
                        return;
                }

                $operation = 'post' . ucfirst($operation) . 'Record';

                foreach ($aBehaviours as $oBehaviour)
                {
                        $oBehaviour->$operation($oDB, $this->oModel);
                }
        }

        public function retrieveWithConditions($oDB, $field, $value, $view = 'default')
        {
                $this->beforeRetrieve($oDB);
                $this->execPreBehaviours($oDB, 'retrieve');

                $oDB->retrieveRecord($this, $field, $value, $view);

                $this->execPostBehaviours($oDB, 'retrieve');
                $this->afterRetrieve($oDB);
        }

        // ================================================================
        // Support for inter-record relationships
        // ----------------------------------------------------------------

        /**
         * find this record from the data held in a related record
         *
         * there must be a foreign key for this record defined in the
         * related record
         */

        public function findFrom_ ($oDB, $aMethod, $aArgs)
        {
                constraint_mustBeArray($aMethod);
                constraint_mustBeArray($aArgs);

                if (!isset($aArgs[1]))
                {
                        $aArgs[1] = false;
                }
                return $this->findFrom($oDB, $aMethod[0], $aArgs[0], $aArgs[1]);
        }

        public function findFrom ($oDB, $alias, $oRecord, $aConditions = null)
        {
                $aConditions = $this->getFindConditionsFrom($alias, $oRecord);

                if (is_array($aConditions))
                {
                        foreach ($aConditions as $key => $value)
                        {
                                $aConditions[$key] = $value;
                        }
                }

                return $this->findByKey($oDB, $aConditions);
        }

        public function find_ ($oDB, $aMethod, $aArgs)
        {
                constraint_mustBeArray($aMethod);
                constraint_mustBeArray($aArgs);

                if (!isset($aMethod[0]))
                {
                        // FIXME: we need an exception to throw here
                        throw new Exception();
                }

                // does the requested alias exist?
                $oDef->requireValidRelationshipAlias($aMethod[0]);

                // do we have a model to search for?
                if (isset($aArgs[0]))
                {
                        $oRecord = $aArgs[0];
                }
                else
                {
                        $oRelationship = $this->oDef->getRelationship($aMethod[0]);
                        $oRecord       = $oRelationship->cloneTheirRecord();
                }

                if (!$oRecord instanceof Datastore_I_Relationship)
                {
                        // FIXME: we need an exception to throw here
                        throw new Exception();
                }

                // if we get here, then
                //
                // a) we have a valid alias
                // b) we have a valid model to save the data into

                $aConditions = $this->getFindConditionsFor($aMethod[0]);
                if ($oRecord->findByKey($oDB, $aConditions))
                {
                        return $oRecord;
                }

                return false;
        }

        /**
         *
         * @param       Datastore $oDB
         *              the datastore to search for the record
         * @param       array $aConditions
         *              The criteria to use to search for the record
         */

        public function findByKey($oDB, $aConditions)
        {
                constraint_mustBeArray($aConditions);

                $oAdapter = $oDB->getAdapterFor($this);
                $return   = $oAdapter->findByKey($this, $aConditions);

                if (!$return)
                {
                        // we did not find the record
                        //
                        // if the record is expected to exist, we throw
                        // an exception

                        $oDef = $this->oModel->getDefinition();

                        $mustExist = false;
                        foreach ($aConditions as $field => $value)
                        {
                                if ($oDef->isValidFieldName($field)
                                    && $oDef->isMandatoryField($field))
                                {
                                        $mustExist = true;
                                }
                        }

                        if ($mustExist)
                        {
                                throw new Datastore_E_RelatedDataNotFound
                                (
                                        $oDef->getModelName(),
                                        $aConditions
                                );
                        }
                        else
                        {
                                return false;
                        }
                }

                return true;
        }

        // ================================================================
        // Support for keeping track of which datastore our record
        // is stored in
        // ----------------------------------------------------------------

        public function getDatastoreWhereStored()
        {
                return $this->oDatastoreWhereStored;
        }

        public function setDatastoreWhereStored($oDB)
        {
                $this->oDatastoreWhereStored = $oDB;
                $this->setStorageHint(Datastore::HINT_UPDATE);
        }

        public function resetDatastoreWhereStored()
        {
                $this->oDatastoreWhereStored = null;
                $this->setStorageHint(Datastore::HINT_UNKNOWN);
        }

        public function isStoredInDatastore($oDB = null)
        {
                if (!isset($this->oDatastoreWhereStored))
                        return false;

                if ($oDB === null)
                        return true;

                return $this->oDatastoreWhereStored->isSameAs($oDB);
        }

        public function requireStoredInDatastore()
        {
                if (!$this->isStoredInDatastore())
                {
                        throw new Datastore_E_NeedDatastore(get_class($this));
                }
        }

        public function setStorageHint($hint)
        {
                constraint_mustBeInteger($hint);
                constraint_mustBeGreaterThan($hint, Datastore::HINT_START);
                constraint_mustBeLessThan   ($hint, Datastore::HINT_END);

                $this->storageHint = $hint;
        }

        public function getStorageHint()
        {
                return $this->storageHint;
        }

        public function resetStorageHint()
        {
                $this->storageHint = Datastore::HINT_UNKNOWN;
        }

        // ----------------------------------------------------------------
        // Support for dynamic methods (specifically, retrieve_())
        // ----------------------------------------------------------------

        /**
         * we do not know whether the method we are looking for belongs
         * to this Datastore_Record, or to the model that we are trying
         * to encapsulate
         *
         * this method's job is to try and find out
         */

        public function findValidObjectForMethod($methodName, $fullMethodName)
        {
        	$recordHasMethod = false;
                $modelHasMethod  = false;

                if (method_exists($this, $methodName))
                {
                	$recordHasMethod = true;
                }

                if (method_exists($this->oModel, $fullMethodName))
                {
                	$modelHasMethod = true;
                }

                // I'm sure that overlapping methods will cause confusion
                // so let's ensure it cannot happen
                if ($recordHasMethod && $modelHasMethod)
                {
                        throw new Datastore_E_OverlappingMethods($methodName, get_class($this), get_class($this->oModel));
                }

                // return the object that
                if ($modelHasMethod)
                {
                	return array($this->oModel, $fullMethodName);
                }
                else if ($recordHasMethod)
                {
                	return array($this, $methodName);
                }

                // if we get here, then we do not have the method
                throw new PHP_E_NoSuchMethod($methodName, get_class($this));
        }

        public function __call($fullMethodName, $aArgs)
        {
                $aMethod = explode('_', $fullMethodName);

                // determine the name of the method to call
                $methodName = $aMethod[0] . '_';
                array_shift($aMethod);

                // does the method exist?
                list($objectToCall, $methodToCall) = $this->findValidObjectForMethod($methodName, $fullMethodName);

                // additional checks required to call Datastore_Record's
                // fake methods
                if ($objectToCall == $this)
                {
                        // make sure we have enough parameters to make this work
                        //
                        // the most common mistake is forgetting to pass in the
                        // datastore, so let's check for that

                        if (count($aArgs[0]) == 0)
                        {
                                throw new Datastore_E_ExpectedDatastore(1);
                        }

                        if (!$aArgs[0] instanceof Datastore)
                        {
                                throw new Datastore_E_ExpectedDatastore(1);
                        }

                        $oDB = $aArgs[0];
                        array_shift($aArgs);

                        return $this->$methodName($oDB, $aMethod, $aArgs);
                }
                else
                {
                        return call_user_func_array(array($objectToCall, $methodToCall), $aArgs);
                }
        }
}

// ========================================================================
// Query classes
// ------------------------------------------------------------------------

class Datastore_Query
{
        const FIND_FIRST = 1;
        const FIND_ALL   = 2;

        const TYPE_VIEW         = 1;
        const TYPE_FIELD        = 2;
        const TYPE_EXPRESSION   = 3;
        const TYPE_JOIN         = 4;

        public $searchTerms     = array();
        public $rawQuery        = null;
        public $tokens          = array();
        public $extractInto     = array();
        public $orderBy         = null;
        public $rowsPerPage     = null;
        public $pageNo          = null;

        public $fieldsToBind    = array();

        public $queryType       = null;

        protected $oDB          = null;
        protected $currentView  = null;
        protected $currentModel = null;

        public function __construct(Datastore $oDB)
        {
        	$this->oDB = $oDB;
        }

        public function resetForNextQuery()
        {
        	$this->searchTerms = array();
                $this->rawQuery    = null;
                $this->tokens      = array();
                $this->extractInto = array();
                $this->orderBy     = null;
                $this->rowsPerPage = null;
                $this->pageNo      = null;

                $this->fieldsToBind = array();
                $this->queryType    = null;
                $this->currentView  = null;
                $this->currentModel = null;
        }

	public function findFirst($model, $view = 'default')
        {
                $this->resetForNextQuery();
        	$this->queryType = Datastore_Query::FIND_FIRST;

                $this->extractView($model, $view);

                $this->searchTerms[] = array
                (
                        'type'  => Datastore_Query::TYPE_VIEW,
                        'view'  => $this->currentView,
                );

                $this->primaryKey    = $this->currentView->oDef->getPrimaryKey();
                $this->extractInto[] = $this->currentView;

                return $this;
        }

        public function findEvery($model, $view = 'default')
        {
                $this->resetForNextQuery();
        	$this->queryType = Datastore_Query::FIND_ALL;

                $this->extractView($model, $view);
                $this->searchTerms[] = array
                (
                        'type'  => Datastore_Query::TYPE_VIEW,
                        'view'  => $this->currentView,
                );

                $this->primaryKey    = $this->currentView->oDef->getPrimaryKey();
                $this->extractInto[] = $this->currentView;

                return $this;
        }

        public function findAll($alias, $record, $view = 'default')
        {
        	constraint_mustBeString($alias);

                $this->resetForNextQuery();

                if ($record instanceof Datastore_Record)
                {
                	$model = $record->oModel;
                }
                else if ($record instanceof DataModel)
                {
                	$model = $record;
                }
                else
                {
                	throw new Exception();
                }

                $oRelationship     = $model->getDefinition()->getRelationship($alias);
                $modelNameToFind   = $oRelationship->getTheirModelName();

                $this->currentView = DataModel_Definitions::get($modelNameToFind)->getView($view);

                if ($oRelationship->hasOne())
                {
                	$this->queryType = Datastore_Query::FIND_FIRST;
                }
                else
                {
                        $this->queryType = Datastore_Query::FIND_ALL;
                }

                $this->searchTerms[] = array
                (
                        'type'  => Datastore_Query::TYPE_VIEW,
                        'view'  => $this->currentView,
                );

                $oMap = $this->oDB->getStorageForModel($this->currentView->oDef->getModelName());

                $fields = $oRelationship->getTheirFields();
                $values = $model->getFields($oRelationship->getOurFields());

                reset($values);
                
                foreach ($fields as $fieldName)
                {
                        $this->searchTerms[] = array
                        (
                                'type'  => Datastore_Query::TYPE_FIELD,
                                'table' => $oMap->getTable(),
                                'field' => $fieldName,
                                'value' => current($values),
                        );

                        next($values);
                }

                $this->primaryKey    = $this->currentView->oDef->getPrimaryKey();
                $this->extractInto[] = $this->currentView;

                return $this;
        }

        public function findRaw($query, $primaryKey, $tokens = array())
        {
                constraint_mustBeString($query);
                $this->resetForNextQuery();

        	$this->rawQuery   = $query;
                $this->tokens     = $tokens;

                if (!is_array($this->primaryKey))
                {
                        $this->primaryKey = array($primaryKey => $primaryKey);
                }
                else
                {
                        foreach ($primaryKey as $field)
                        {
                                $this->primaryKey[$field] = $field;
                        }
                }
                
                return $this;
        }

        protected function extractView($model, $view = 'default')
        {
                // we want to store the view
                if (is_string($model))
                {
                        $this->currentView = DataModel_Definitions::get($model)->getView($view);
                }
                else if ($model instanceof Datastore_Record)
                {
                	$this->currentView = $model->getDefinition()->getView($view);
                }
                else if ($model instanceof DataModel)
                {
                        $this->currentView = $model->getDefinition()->getView($view);
                }
                else if ($model instanceof DataModel_View)
                {
                        $this->currentView = $model;
                }
                else
                {
                	// TODO throw a decent exception
                        throw new Exception();
                }
        }

        public function withUniqueId($value)
        {
                $oMap = $this->oDB->getStorageForModel($this->currentView->oDef->getModelName());

                if (!is_array($value))
                {
                        $values = array(current($this->primaryKey) => $value);
                }
                else
                {
                        $values = $value;
                }

                foreach ($this->primaryKey as $field)
                {
                        $this->searchTerms[] = array
                        (
                                'type'  => Datastore_Query::TYPE_FIELD,
                                'table' => $oMap->getTable(),
                                'field' => $field,
                                'value' => $values[$field]
                        );
                }

                return $this;
        }

        // from now on, we must pass in an array of key/value pairs

        public function withForeignKey($key, $value)
        {
                return $this->withForeignKeys(array($key => $value));
        }
        
        public function withForeignKeys($keys)
        {
                constraint_mustBeArray($keys);

                $oMap = $this->oDB->getStorageForModel($this->currentView->oDef->getModelName());

                foreach ($keys as $field => $value)
                {
                        $this->searchTerms[] = array
                        (
                                'type'  => Datastore_Query::TYPE_FIELD,
                                'table' => $oMap->getTable(),
                                'field' => $field,
                                'value' => $value
                        );
                }

                return $this;
        }

        public function including($alias, $view = 'default')
        {
        	$oRelationship = $this->currentView->oDef->getRelationship($alias);
                $theirModelName = $oRelationship->getTheirModelName();
                $theirModelDef  = DataModel_Definitions::get($theirModelName);

                $this->searchTerms[] = array
                (
                        'type'       => Datastore_Query::TYPE_VIEW,
                        'view'       => $theirModelDef->getView($view),
                );

                $oOurMap   = $this->oDB->getStorageForModel($this->currentView->oDef->getModelName());
                $oTheirMap = $this->oDB->getStorageForModel($theirModelName);

                $this->searchTerms[] = array
                (
                        'type'        => Datastore_Query::TYPE_JOIN,
                        'ourTable'    => $oOurMap->getTable(),
                        'ourFields'   => $oRelationship->getOurFields(),
                        'theirTable'  => $oTheirMap->getTable(),
                        'theirFields' => $oRelationship->getTheirFields(),
                );

                $this->extractInto[] = $theirModelDef->getView($view);

                return $this;
        }

        public function includingOnly($alias, $view = 'default')
        {
        	$oRelationship = $this->currentView->oDef->getRelationship($alias);
                $theirModelName = $oRelationship->getTheirModelName();
                $theirModelDef  = DataModel_Definitions::get($theirModelName);

                $this->searchTerms[] = array
                (
                        'type'       => Datastore_Query::TYPE_VIEW,
                        'view'       => $theirModelDef->getView($view),
                );

                $oOurMap   = $this->oDB->getStorageForModel($this->currentView->oDef->getModelName());
                $oTheirMap = $this->oDB->getStorageForModel($theirModelName);

                $this->searchTerms[] = array
                (
                        'type'        => Datastore_Query::TYPE_JOIN,
                        'ourTable'    => $oOurMap->getTable(),
                        'ourFields'   => $oRelationship->getOurFields(),
                        'theirTable'  => $oTheirMap->getTable(),
                        'theirFields' => $oRelationship->getTheirFields(),
                );

                $this->extractInto = array($theirModelDef->getView($view));

                return $this;
        }

        public function matchingExpression($expression, $tokens = null)
        {
                if ($tokens === null)
                {
                	$tokens = array();
                }

        	$this->searchTerms[] = array
                (
                        'type'   => Datastore_Query::TYPE_EXPRESSION,
                        'exp'    => $expression,
                        'tokens' => $tokens,
                );

                return $this;
        }

        public function extractInto($model, $view = 'default')
        {
        	if (is_string($model))
                {
                	$oDef = DataModel_Definitions::get($model);
                }
                else if ($model instanceof Datastore_Record)
                {
                	$oDef = $model->getDefinition();
                }
                else if ($model instanceof DataModel)
                {
                	$oDef = $model->getDefinition();
                }
                else
                {
                	throw new Exception();
                }

                $this->extractInto[] = $oDef->getView($view);

                return $this;
        }

        public function orderBy($orderBy)
        {
                constraint_mustBeString($orderBy);
        	$this->orderBy = $orderBy;

                return $this;
        }

        public function rowsPerPage($rowsPerPage)
        {
        	$this->rowsPerPage = $rowsPerPage;

                return $this;
        }

        public function limitToPage($pageNo)
        {
        	$this->pageNo = $pageNo;

                return $this;
        }

        public function go()
        {
        	return $this->oDB->search($this);
        }

        // ----------------------------------------------------------------
        // these methods are used by the datastore

        public function getRawQuery()
        {
        	if (!isset($this->rawQuery))
                {
                        $this->fieldsToBind = array();
                	list($this->rawQuery, $this->tokens) = $this->buildRawQuery();
                        // debug_vardump(__FILE__, __LINE__, __FUNCTION__, 'rawQuery', $this->rawQuery);
                        // debug_vardump(__FILE__, __LINE__, __FUNCTION__, 'tokens', $this->tokens);
                }

                return $this->rawQuery;
        }

        public function getTokens()
        {
        	return $this->tokens;
        }

        public function getPrimaryKey()
        {
        	return $this->primaryKey;
        }

        public function getCountingQuery()
        {
        	return $this->countingQuery;
        }

        public function extractIntoRecords($aFields)
        {
                if (count($this->extractInto) == 1)
                {
                        $oView = $this->extractInto[0];
                        $recordName = $oView->oDef->getModelName();
                        $record = new $recordName();

                        // debug_vardump(__FILE__, __LINE__, __FUNCTION__, '$aFields', $aFields);
                        $record->setFields($aFields);
                        // debug_vardump(__FILE__,__LINE__, __FUNCTION__, 'getData()', $record->getData());
                        return $record;
                }

                // if we get here, we ned to return more than one
                // record

                $return = array
                (
                        '__raw' => $aFields,
                );

        	foreach($this->extractInto as $oView)
                {
                        $recordName = $oView->oDef->getModelName();
                	$record = new $recordName();
                        $record->setFields($aFields);
                        $return[$oView->oDef->getModelName()] = $record;
                }

                return $return;
        }
}

?>
