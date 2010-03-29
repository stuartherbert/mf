<?php

// ========================================================================
//
// DatastoreMySQL/DatastoreMySQL.classes.php
//              Classes for the DatastoreMySQL component
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
// 2008-03-16   SLH     Separated out from the Datastore component
// 2008-08-12   SLH     Added support for SQLite2
// 2009-02-29   SLH     Separated out from the DatastoreSQL module
// 2009-03-18   SLH     Fixes for supporting complex primary keys
//                      (more fixes required as we get better tests)
// ========================================================================

class DatastoreMySql_Connector extends DatastoreRdbms_Connector
{
        protected $oDB = null;

        public function __construct($aParams = array())
        {
                parent::__construct();

                if (count($aParams) > 0)
                {
                       $this->connect($aParams);
                }
        }

        public function connect($aParams)
        {
                $oDB = mysql_connect($aParams['host'], $aParams['user'], $aParams['pass']);
                if (!$oDB)
                {
                        throw new Datastore_E_ConnectFailed(mysql_error());
                }
                mysql_select_db($aParams['db'], $oDB);

                $this->oDB = $oDB;

                $this->aDetails = $aParams;
        }

        public function disconnect()
        {
                if (!$this->isConnected())
                        return;

                mysql_close($this->oDB);
                $this->oDB = null;
        }

        public function isConnected()
        {
                return (isset($this->oDB));
        }

        public function query($sql)
        {
                $this->requireConnected();
                return mysql_query($sql, $this->oDB);
        }

        public function fetchAssoc($result)
        {
                $this->requireConnected();
                return mysql_fetch_assoc($result);
        }

        public function escapeString($string)
        {
                $this->requireConnected();
                return mysql_real_escape_string($string, $this->oDB);
        }

        public function errorString()
        {
                return mysql_errno($this->oDB) . ': ' . mysql_error($this->oDB);
        }
}

?>