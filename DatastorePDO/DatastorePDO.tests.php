<?php

// ========================================================================
//
// DatastorePDO/DatastorePDO.tests.php
//              Unit tests for the DatastorePDO library
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
// 2008-07-19   SLH     Separated out from Datastore.tests.php
// 2008-07-28   SLH     We now define the test models during setup()
// 2008-08-07   SLH     We now define where models are stored during
//                      setup()
// ========================================================================

// ========================================================================
// Tests against a PDO database
// ------------------------------------------------------------------------

// registerTests('DatastorePDO_Record_Tests');
class DatastorePDO_Record_Tests extends DatastoreXXX_Record_Tests
{
        public function setup ()
        {
//                echo "\nDatastorePDO_Record_Tests::setup()\n";
                createTestSqlDatabase();
                defineDatastoreTestModels();

                $oConn         = new Datastore_PDO_Connector('mysql:host=localhost;dbname=mfTest', 'root', '');
                $this->db      = new Datastore($oConn);
                $this->fixture = new Datastore_Record('Test_Customer');

                defineDatastoreTestStorage_RDBMS($this->db);
        }
}

class DatastorePDO_Query_Tests extends DatastoreXXX_Query_Tests
{
        public function setup ()
        {
                createTestSqlDatabase();
                defineDatastoreTestModels();

                $oConn         = new Datastore_PDO_Connector('mysql:host=localhost;dbname=mfTest', 'root', '');
                $this->db      = new Datastore($oConn);
                $this->fixture = new Datastore_Record('Test_Customer');

                defineDatastoreTestStorage_RDBMS($this->db);
        }
}

/*
registerTests('DatastorePDO_Table_Tests');
class DatastorePDO_Table_Tests extends DatastoreXXX_Table_Tests
{
        public function setup ()
        {
                createTestSqlDatabase();

                $oConn         = new Datastore_PDO_Connector('mysql:host=localhost;dbname=datastoreTest', 'root', '');
                $this->db      = new Datastore($oConn);
        }
}

// registerTests('DatastorePDO_ListQuery_Tests');
class DatastorePDO_ListQuery_Tests extends DatastoreXXX_ListQuery_Tests
{
        public function setup ()
        {
                createTestSqlDatabase();

                $oConn         = new Datastore_PDO_Connector('mysql:host=localhost;dbname=datastoreTest', 'root', '');
                $this->db      = new Datastore($oConn);
        }
}

*/

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