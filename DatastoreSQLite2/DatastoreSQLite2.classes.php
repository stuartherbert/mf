<?php

// ========================================================================
//
// DatastoreSQLite2/DatastoreSQLite2.classes.php
//              Classes for the DatastoreSQLite2 component
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
// 2008-03-16   SLH     Separated out from the Datastore component
// 2008-08-12   SLH     Added support for SQLite2
// 2009-02-28   SLH     Separated out from the DatastoreSQL component
// ========================================================================

// ========================================================================
//
// Support for SQLite 2
//
// ------------------------------------------------------------------------

class DatastoreSQLite2_Connector extends DatastoreRdbms_Connector
{
        protected $oDB       = null;
        protected $errString = null;

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
                $this->errString = null;

                $oDB = sqlite_open($aParams['file'], $aParams['mode'], $this->errString);
                if (!$oDB)
                {
                        throw new Datastore_E_ConnectFailed($this->errString);
                }

                $this->oDB = $oDB;
                $this->aDetails = $aParams;
        }

        public function disconnect()
        {
                $this->errString = null;

                if (!$this->isConnected())
                        return;

                sqlite_close($this->oDB);
                $this->oDB = null;
        }

        public function isConnected()
        {
                return (isset($this->oDB));
        }

        public function query($sql)
        {
                $this->errString = null;

                $this->requireConnected();
                return sqlite_unbuffered_query($this->oDB, $sql, SQLITE_ASSOC, $this->errString);
        }

        public function fetchAssoc($result)
        {
                $this->errString = null;
                $this->requireConnected();

                return sqlite_fetch_array($result, SQLITE_ASSOC);
        }

        public function escapeString($string)
        {
                return sqlite_escape_string($string);
        }

        public function errorString()
        {
                return $this->errString;
        }
}

?>