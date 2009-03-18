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
// 2009-03-18   SLH     Fixed up to run using the new task-based approach
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

// define the tests

Testsuite_registerTests('DatastoreMySQL_Record_Tests');

class DatastoreMySQL_Record_Tests extends DatastoreXXX_Record_Tests
{
        public function setup ()
        {
                createTestSqlDatabase();
                defineDatastoreTestModels();

                $oConn         = new DatastoreMySQL_Connector(array('host' => 'localhost', 'db' => 'mfTest', 'user' => 'root', 'pass' => ''));
                $this->db      = new Datastore($oConn);
                $this->fixture = new Datastore_Record('Test_Customer');

                defineDatastoreTestStorage_RDBMS($this->db);
        }
}

Testsuite_registerTests('DatastoreMySQL_Query_Tests');
class DatastoreMySQL_Query_Tests extends DatastoreXXX_Query_Tests
{
        public function setup ()
        {
                createTestSqlDatabase();
                defineDatastoreTestModels();

                $oConn    = new DatastoreMySQL_Connector(array('host' => 'localhost', 'db' => 'mfTest', 'user' => 'root', 'pass' => ''));
                $this->db = new Datastore($oConn);

                defineDatastoreTestStorage_RDBMS($this->db);
        }
}

// helper function to reload the database to a known state
function createTestSqlDatabase()
{
        $db = mysql_connect('localhost', 'root', '');
        if (!$db)
        {
                echo "*** error: cannot connect to local mysql db\n";
                exit(1);
        }

        mysql_select_db("mfTest", $db);

        $file = file_get_contents(dirname(__FILE__) . '/../Datastore//datastoreTest.sql');
        $aSql = explode(';', $file);

        foreach ($aSql as $sql)
        {
                mysql_query($sql, $db);

                $error = mysql_error($db);
                if (strlen(trim($error)) > 0)
                {
                        echo $error . "\n";
                        exit;
                }
        }

        mysql_close($db);
}

?>