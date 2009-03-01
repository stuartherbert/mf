<?php

// ========================================================================
//
// DatastoreSQLite3/DatastoteSQLite3.tests.php
//              Unit tests for the SQLite3 support
//
//              Part of the Modular Framework for PHP applications
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
// 2008-08-12   SLH     Created
// ========================================================================

class DatastoreSQLite3_Record_Tests extends DatastoreXXX_Record_Tests
{
        public function setup ()
        {
                createTestSQLiteDatabase();
                defineDatastoreTestModels();

                $oConn         = new Datastore_SQLite3_Connector('mf.sqlite');
                $this->db      = new Datastore($oConn);
                $this->fixture = new Datastore_Record('Test_Customer');

                defineDatastoreTestStorage_RDBMS($this->db);
        }
}

class DatastoreSQLite3_Query_Tests extends DatastoreXXX_Query_Tests
{
        public function setup ()
        {
                createTestSQLiteDatabase();
                defineDatastoreTestModels();

                $oConn    = new Datastore_SQLite3_Connector('mf.sqlite');
                $this->db = new Datastore($oConn);

                defineDatastoreTestStorage_RDBMS($this->db);
        }
}

function createTestSQLiteDatabase()
{
        if (file_exists('mf.sqlite'))
        {
                unlink('mf.sqlite');
        }

        $db = new PDO('sqlite:mf.sqlite');
        $file = file_get_contents(dirname(__FILE__) . '/datastoreSQLiteTest.sql');
        $aSql = explode(';', $file);

        foreach ($aSql as $sql)
        {
                $sql = trim(str_replace("\n", ' ', $sql)) . ';';

                $db->query($sql);
                $errInfo = $db->errorInfo();
                if ($errInfo[1] != 0)
                {
                        $errInfo[3] = $sql;
                        var_dump($errInfo);
                        exit(0);
                }
        }
}

?>