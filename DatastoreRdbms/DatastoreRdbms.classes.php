<?php

// ========================================================================
//
// DatastoreRdbms/DatastoreRdbms.classes.php
//              Base classes for relational database (RDBMS) support
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
// 2009-02-28   SLH     Separated out from Datastore library
// 2009-03-10   SLH     Fixes for models with complex primary keys
// ========================================================================

// ========================================================================
//
// Generic SQL support
//
// ------------------------------------------------------------------------

class DatastoreRdbms_Statement extends Datastore_BaseStatement
{
        protected $oConnector   = null;

        protected $primaryKey   = null;

        protected $sql          = null;
        protected $sqlToExecute = null;
        protected $fieldsToBind = array();
        protected $returnRows   = false;

        public function __construct($oConnector)
        {
                $this->oConnector = $oConnector;
        }

        // ================================================================
        // Tell this statement what to be
        // ----------------------------------------------------------------

        public function beCreateStatement(Model_Definition $oDef, Datastore_Storage $oMap)
        {
                $this->fieldsToBind = array();

                $table          =  $oMap->getTable();
                $primaryKey     =  $oDef->getPrimaryKey();
                $aFields        =  $oDef->getFieldsBySource(Model_Definition::SOURCE_DB);

                $keys   = "";
                $values = "";
                $append = false;

                foreach ($aFields as $field => $oField)
                {
                        if ($append)
                        {
                                $keys .= ",";
                                $values .= ",";
                        }
                        $append = true;

                        $keys   .= $field;
                        $values .= '?';

                        $this->fieldsToBind[] = $field;
                }

                $this->sql        = "insert into $table ( $keys ) values ( $values );";
                $this->returnRows = false;
        }

        public function beRetrieveStatement(Model_Definition $oDef, Datastore_Storage $oMap, $retrieveField, $view)
        {
                $this->fieldsToBind = array();

                $table          =  $oMap->getTable();
                $primaryKey     =  $oDef->getPrimaryKey();
                $aFields        =  $oDef->getFieldsBySourceAndView(Model_Definition::SOURCE_DB, $view);

                $sql            = 'select ';
                $append         = false;

                foreach ($aFields as $field => $oField)
                {
                        if ($append)
                        {
                                $sql .= ',';
                        }
                        $append = true;

                        $sql .= $field;
                }

                $sql .= ' from ' . $table . ' where ';

                if (is_string($retrieveField))
                {
                        $sql .= $retrieveField . ' = ?';
                        $this->fieldsToBind[] = $retrieveField;
                }
                else
                {
                        $append = false;

                        foreach ($retrieveField as $field => $value)
                        {
                                if ($append)
                                {
                                        $sql .= ' and ';
                                }
                                $append = true;

                                $sql .= $field . ' = ?';
                                $this->fieldsToBind[] = $field;
                        }
                }

                $this->sql        = $sql;
                $this->returnRows = true;
                $this->primaryKey = $primaryKey;
        }

        public function beUpdateStatement(Model_Definition $oDef, Datastore_Storage $oMap)
        {
                $this->fieldsToBind = array();

                $table          =  $oMap->getTable();
                $primaryKey     =  $oDef->getPrimaryKey();
                $aFields        =  $oDef->getFieldsBySource(Model_Definition::SOURCE_DB);

                $sql    = "update $table set ";
                $append = false;

                foreach ($aFields as $field => $oField)
                {
                        // skip over the primary key
                        if ($field == $primaryKey)
                        {
                                continue;
                        }

                        if ($append)
                        {
                                $sql .= ', ';
                        }
                        $append = true;

                        $sql .= $field . " = ?";
                        $this->fieldsToBind[] = $field;
                }

                $sql .= " where $primaryKey = ?";
                $this->fieldsToBind[] = $primaryKey;

                $this->sql        = $sql;
                $this->returnRows = false;
        }

        public function beDeleteStatement(Model_Definition $oDef, Datastore_Storage $oMap)
        {
                $table          =  $oMap->getTable();
                $primaryKey     =  $oDef->getPrimaryKey();

                $this->sql          = "delete from $table where $primaryKey = ?";
                $this->returnRows   = false;
                $this->fieldsToBind = array($primaryKey);
        }

        public function beTruncateStatement(Datastore_Storage $oMap)
        {
                $table              = $oMap->getTable();

                $this->sql          = "truncate table $table";
                $this->returnRows   = false;
                $this->fieldsToBind = array();
        }

        public function beQueryStatement($query, $primaryKey)
        {
                constraint_mustBestring($query);

        	$this->sql          = $query;
                $this->primaryKey   = $primaryKey;
                $this->returnRows   = true;
                $this->fieldsToBind = array();
        }

        // ================================================================
        // Turn the statement into something we can execute

        public function bindValues($oRecord)
        {
                if ($oRecord instanceof Datastore_Record)
                {
                        $aFields =& $oRecord->getData();
                }
                else if (is_array($oRecord))
                {
                        $aFields =& $oRecord;
                }
                else
                {
                        throw new Exception();
                }

                $this->prepareToBind();
                $i = 1;
                foreach ($this->fieldsToBind as $fieldToBind)
                {
                        $this->bindField($i, $aFields[$fieldToBind]);
                        $i++;
                }
        }

        public function bindAnonymousValues($values)
        {
        	constraint_mustBeArray($values);

                $this->prepareToBind();
                $i = 1;
                foreach ($values as $value)
                {
                	$this->bindField($i, $value);
                        $i++;
                }
        }

        protected function prepareToBind()
        {
                $this->sqlToExecute = $this->sql;
        }

        protected function bindField($fieldNo, $value)
        {
                // we deliberately ignore $fieldNo, and just set
                // the next question mark that we find

                $pos = strpos($this->sqlToExecute, '?');
                if ($pos === false)
                {
                        // uh oh, we have more fields than question marks
                        throw new Datastore_E_QueryFailed($this->sqlToExecute, 'Too many fields to bind');
                }

                $this->sqlToExecute = substr($this->sqlToExecute, 0, $pos)
                                    . PHP_StringUtils::quote($this->oConnector->escapeString($value))
                                    . substr($this->sqlToExecute, $pos+1);
        }

        // ================================================================
        // Execute the statement

        public function execute()
        {
                $result = $this->oConnector->query($this->sqlToExecute);
                if (!$result)
                {
                        throw new Datastore_E_QueryFailed($this->sqlToExecute, $this->oConnector->errorString());
                }

                if (!$this->returnRows)
                {
                        return;
                }

                $aReturn = array();
                while ($aRec = $this->oConnector->fetchAssoc($result))
                {
                        $aReturn[$aRec[$this->primaryKey]] = $aRec;
                }

                if (count($aReturn) == 0)
                {
                        throw new Datastore_E_QueryFailed($this->sqlToExecute, 'No matching rows found');
                }

                return $aReturn;
        }
}

class DatastoreRdbms_Connector extends Datastore_BaseConnector
{
        protected $oStore = null;

        public function __construct($statementClass = 'DatastoreRdbms_Statement', $queryClass = 'DatastoreRdbms_Query')
        {
        	parent::__construct($statementClass, $queryClass);
        }

        public function storeModel($model)
        {
        	$oStorageMap = new DatastoreRdbms_Storage($model);
                return $oStorageMap;
        }
}

class DatastoreRdbms_Storage extends Datastore_Storage
{
	public $table  = null;

        public function inTable($name)
        {
        	$this->table = $name;
        }

        // ----------------------------------------------------------------
        // methods used by Datastore et al

        public function getTable()
        {
        	return $this->table;
        }
}

// ========================================================================
// Class for making queries against SQL databases
// ------------------------------------------------------------------------

class DatastoreRdbms_Query extends Datastore_Query
{
        public function buildRawQuery()
        {
                // we have to take all the information that we have
                // been given, and turn it into a single SQL statement

                $queryBuilder = array
                (
                        'tokens' => array()
                );

                foreach ($this->searchTerms as $searchTerm)
                {
                        switch ($searchTerm['type'])
                        {
                                case Datastore_Query::TYPE_VIEW:
                                        $this->buildRawQuery_view($searchTerm, $queryBuilder);
                                        break;
                        	case Datastore_Query::TYPE_FIELD:
                                        $this->buildRawQuery_field($searchTerm, $queryBuilder);
                                        break;
                                case Datastore_Query::TYPE_JOIN:
                                        $this->buildRawQuery_join($searchTerm, $queryBuilder);
                                        break;
                                case Datastore_Query::TYPE_EXPRESSION:
                                        $this->buildRawQuery_expression($searchTerm, $queryBuilder);
                                        break;
                                default:
                                        throw new Exception();
                        }
                }

                // at this point, we have discovered the pieces
                // now we need to turn them into a single statement

                $sql = 'select ' . join(', ', $queryBuilder['fieldsToSelect'])
                     . ' from ' . $queryBuilder['tablesFrom'][0];

                if (isset($queryBuilder['joins']))
                {
                	$sql .= ' INNER JOIN ' . join(' INNER JOIN ', $queryBuilder['joins']);
                }

                if (isset($queryBuilder['where']))
                {
                        $sql .= ' where ' . join(' and ', $queryBuilder['where']);
                }

                if (isset($queryBuilder['orderBy']))
                {
                	$sql .= ' order by ' . $queryBuilder['orderBy'];
                }
                else
                {
                        // primary key may be an array
                        if ($this->currentView->oDef->getPrimaryKeyType() == Model_Definition::PRIMARY_KEY_SIMPLE)
                        {
                                $sql .= ' order by ' . $this->currentView->oDef->getPrimaryKey() . ' asc';
                        }
                        else
                        {
                                $primaryKeys = $this->currentView->oDef->getPrimaryKey();
                                $sql .= ' order by ' . implode(' asc,', $primaryKeys) . ' asc' ;

                        }
                }

                constraint_mustBeString($sql);
                constraint_mustBeArray($queryBuilder['tokens']);

                return array($sql, $queryBuilder['tokens']);
        }

        protected function buildRawQuery_view($searchTerm, &$queryBuilder)
        {
                $oMap   = $this->oDB->getStorageForModel($searchTerm['view']->oDef->getModelName());
                $table  = $oMap->getTable();
        	$fields = $searchTerm['view']->getFields();
                foreach ($fields as $field)
                {
                	$queryBuilder['fieldsToSelect'][] = $table . '.' . $field->getName();
                }

                $queryBuilder['tablesFrom'][] = $table;
        }

        protected function buildRawQuery_field($searchTerm, &$queryBuilder)
        {
                $queryBuilder['where'][]  = $searchTerm['table'] . '.' . $searchTerm['field'] . '=?';
                $queryBuilder['tokens'][] = $searchTerm['value'];
        }

        protected function buildRawQuery_join($searchTerm, &$queryBuilder)
        {
                $queryBuilder['joins'][] = $searchTerm['theirTable']
                                         . ' ON ' . $searchTerm['ourTable'] . '.' . $searchTerm['ourField']
                                         . ' = ' . $searchTerm['theirTable'] . '.' . $searchTerm['theirField'];
        }

        protected function buildRawQuery_expression($searchTerm, &$queryBuilder)
        {
                $queryBuilder['where'][] = $searchTerm['exp'];
                // debug_vardump(__FILE__, __LINE__, __FUNCTION__, 'tokens', $queryBuilder['tokens']);

                foreach ($searchTerm['tokens'] as $token)
                {
                	$queryBuilder['tokens'][] = $token;
                        // debug_vardump(__FILE__, __LINE__, __FUNCTION__, 'tokens', $queryBuilder['tokens']);
                }
        }
}

?>