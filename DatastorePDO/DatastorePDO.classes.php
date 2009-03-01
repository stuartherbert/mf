<?php

// ========================================================================
//
// DatastorePDO/DatastorePDO.classes.php
//              Classes for the DatastorePDO component
//
//              Part of the Modular Framework for PHP applications
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
// 2008-03-16   SLH     Separated out from Datastore component
// 2008-08-12   SLH     Added improved error checking to catch rejected
//                      SQL statements
// ========================================================================

// ========================================================================
//
// Support for PDO
//
// ------------------------------------------------------------------------

class DatastorePDO_Statement extends DatastoreRdbms_Statement
{
        protected $pdoDB = null;

        public function __construct($oConnector, $pdoDB)
        {
                $this->pdoDB = $pdoDB;
        }

        protected function prepareToBind()
        {
                // echo "\nBinding sql: " . $this->sql . "\n";
                $this->oStmt = $this->pdoDB->prepare($this->sql);

                if (!$this->oStmt instanceof PDOStatement)
                {
                	$errInfo = $this->pdoDB->errorInfo();
                        throw new Datastore_E_QueryFailed($this->sql, $errInfo[0] . '(' . $errInfo[1] . ') :: ' . $errInfo[2]);
                }
        }

        protected function bindField($fieldNo, $value)
        {
                // echo "Binding field $fieldNo w/ $value\n";
                $this->oStmt->bindParam($fieldNo, $value);
                $this->boundFields[] = $value;
        }

        // ================================================================
        // Execute the statement

        public function execute()
        {
                $result = $this->oStmt->execute();
                if (!$result)
                {
                        $errorInfo = $this->oStmt->errorInfo();
                        throw new Datastore_E_QueryFailed($this->sql, $this->oStmt->errorCode() . ': ' . $errorInfo[2]);
                }

                if (!$this->returnRows)
                {
                        return;
                }

                $aReturn = array();
                while ($aRec = $this->oStmt->fetch(PDO::FETCH_ASSOC))
                {
                        $aReturn[$aRec[$this->primaryKey]] = $aRec;
                }

                if (count($aReturn) == 0)
                {
                        throw new Datastore_E_NoRowsFound($this->sql);
                }

                if (@$GLOBALS['debugSql'] == 1)
                {
                        var_dump($this->sql);
                        var_dump($this->boundFields);
                        var_dump($aReturn);
                }

                return $aReturn;
        }
}

class DatastorePDO_Connector extends DatastoreRdbms_Connector
{
        protected $DB = null;

        public function __construct($uri, $user, $password)
        {
                parent::__construct('Datastore_PDO_Statement');

                $this->connect($uri, $user, $password);
        }

        public function getStatement()
        {
                return new DatastorePDO_Statement($this, $this->DB);
        }

        public function connect($uri, $user, $password)
        {
                try
                {
                        $DB = new PDO($uri, $user, $password, array (PDO::MYSQL_ATTR_DIRECT_QUERY => true));
                }
                catch (PDOException $e)
                {
                        throw new Datastore_E_ConnectFailed($e->getMessage(), $e);
                }

                $this->DB = $DB;

                $this->aDetails = array
                (
                        'uri'           => $uri,
                        'user'          => $user,
                        'password'      => $password
                );
        }

        public function disconnect()
        {
                if (!$this->isConnected())
                        return;

                $this->DB = null;
        }

        public function isConnected()
        {
                return (isset($this->DB));
        }

        public function requireConnected()
        {
                if (!$this->isConnected())
                {
                        throw new Datastore_E_NotConnected(get_class($this));
                }
        }

        public function query($oStmt)
        {
                $this->requireConnected();
                return $oStmt->execute();
        }

        public function fetchAssoc($oStmt)
        {
                $this->requireConnected();
                return $oStmt->fetch();
        }

        public function escapeString($string)
        {
                // do nothing to the string
                return $string;
        }
}

?>