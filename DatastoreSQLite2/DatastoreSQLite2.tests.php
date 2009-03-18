<?php

// ========================================================================
//
// DatastoreSQL/DatastoreSQL.tests.php
//              Unit tests for the DatastoreSQL library
//
//              Part of the Methodosity Framework for PHP applications
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
// 2008-07-26   SLH     Separated out from Datastore.tests.php
// 2008-07-28   SLH     We now define the test models during setup()
// 2008-08-07   SLH     We now define where models are stored during
//                      setup()
// ========================================================================

// ========================================================================
// Tests against a SQL database
// ------------------------------------------------------------------------

// bootstrap the framework
define('UNIT_TEST', true);
define('APP_TOPDIR', realpath(dirname(__FILE__) . '/../../'));
require_once(APP_TOPDIR . '/mf/mf.inc.php');

// load additional files we explicitly require
__mf_require_once('Testsuite');
require_once(APP_TOPDIR . '/mf/Datastore/Datastore.tests.inc.php');

/*
class DatastoreSQL_MySQL_Record_Tests extends DatastoreXXX_Record_Tests
{
        public function setup ()
        {
                createTestSqlDatabase();
                defineDatastoreTestModels();

                $oConn         = new Datastore_MySql_Connector(array('host' => 'localhost', 'db' => 'mfTest', 'user' => 'root', 'pass' => ''));
                $this->db      = new Datastore($oConn);
                $this->fixture = new Datastore_Record('Test_Customer');

                defineDatastoreTestStorage_RDBMS($this->db);
        }
}

class DatastoreSQL_MySQL_Query_Tests extends DatastoreXXX_Query_Tests
{
        public function setup ()
        {
                createTestSqlDatabase();
                defineDatastoreTestModels();

                $oConn    = new Datastore_MySql_Connector(array('host' => 'localhost', 'db' => 'mfTest', 'user' => 'root', 'pass' => ''));
                $this->db = new Datastore($oConn);

                defineDatastoreTestStorage_RDBMS($this->db);
        }
}

*/
?>