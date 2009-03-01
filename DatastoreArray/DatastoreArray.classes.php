<?php

// ========================================================================
//
// DatastoreArray/DatastoreArray.classes.php
//              Classes for the DatastoreArray component
//
//              Part of the Modular Framework for PHP applications
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
// When         Who     Change
// ------------------------------------------------------------------------
// 2008-03-16   SLH     Separated out from the Datastore component
// ========================================================================

// ========================================================================
// When         Who     Note
// ------------------------------------------------------------------------
// 2008-07-17   SLH     TODO Ensure all tests work
// ========================================================================

// ========================================================================
//
// Support for arrays as datastores (e.g. sessions)
//
// Needs moving out into its own component
//
// ------------------------------------------------------------------------

class DatastoreArray_Statement extends DatastoreRdbms_BaseStatement
{
        public function doCreate()
        {
                $aDB     =& $this->oConnector->getConnection();
                $oDef    =  $this->operation['model'];
                $aRecord =& $this->operation['record'];

                $table          =  $oDef->getTable();
                $primaryKey     =  $oDef->getPrimaryKey();
                $aFields        =  $oDef->getFieldsBySource(Model_Definition::SOURCE_DB);

                if (!isset($aDB[$table]))
                        $aDB[$table] = array();

                $uid = $aRecord[$primaryKey];

                foreach ($aFields as $fieldName => $oField)
                {
                        $aDB[$table][$uid][$fieldName] = $aRecord[$fieldName];
                }
        }

        public function doRetrieve()
        {
                $aDB     =& $this->oConnector->getConnection();
                $oDef    =  $this->operation['model'];
                $aRecord =& $this->operation['record'];

                $table          =  $oDef->getTable();
                $primaryKey     =  $oDef->getPrimaryKey();
                $aFields        =  $oDef->getFieldsBySource(Model_Definition::SOURCE_DB);

                $uid = $this->extractUid($primaryKey, $aConditions);

                if (!isset($this->aDB[$table][$uid]))
                {
                        throw new Datastore_E_RetrieveFailed('Row not found');
                }

                foreach ($aFields as $fieldName => $oField)
                {
                        if (isset($this->aDB[$table][$uid][$fieldName]))
                        {
                                $aRecord[$fieldName] = $this->aDB[$table][$uid][$fieldName];
                        }
                }
        }

        public function doUpdate()
        {
                $aDB     =& $this->oConnector->getConnection();
                $oDef    =  $this->operation['model'];
                $aRecord =& $this->operation['record'];

                $table          =  $oDef->getTable();
                $primaryKey     =  $oDef->getPrimaryKey();
                $aFields        =  $oDef->getFieldsBySource(Model_Definition::SOURCE_DB);

                $uid = $aRecord[$primaryKey];

                unset($aDB[$table][$uid]);
                foreach ($aFields as $fieldName => $oField)
                {
                        $aDB[$table][$uid][$fieldName] = $aRecord[$fieldName];
                }
        }

        public function doDelete()
        {
                $aDB     =& $this->oConnector->getConnection();
                $oDef    =  $this->operation['model'];
                $aRecord =& $this->operation['record'];

                $table          =  $oDef->getTable();
                $primaryKey     =  $oDef->getPrimaryKey();
                $aFields        =  $oDef->getFieldsBySource(Model_Definition::SOURCE_DB);

                $uid = $aRecord[$primaryKey];

                if (isset($aDB[$table][$uid]))
                        unset($aDB[$table][$uid]);
        }

        public function doTruncate()
        {
                $aDB     =& $this->oConnector->getConnection();
                $oDef    =  $this->operation['model'];

                $table          =  $oDef->getTable();

                if(isset($aDB[$table]))
                {
                        unset($aDB[$table]);
                }
        }
}

class DatastoreArray_Connector extends Datastore_BaseConnector
{
        protected $oStore = null;
        protected $aDB    = null;

        public function __construct(&$aDB)
        {
                parent::construct('Datastore_Array_Statement');
                $this->connect($aDB);
        }

        public function connect(&$aDB)
        {
                $this->aDB =& $aDB;
        }

        public function disconnect()
        {
                $this->aDB = null;
        }

        public function isConnected()
        {
                return ($this->aDB != null);
        }

        public function &getConnection()
        {
                return $this->aDB;
        }
}

?>