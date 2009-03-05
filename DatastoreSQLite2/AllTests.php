<?php

// ========================================================================
//
// DatastoreSQL/AllTests.php
//              Unit test suite for the DatastoreSQL component
//
//              Part of the Methodosity Framework for PHP appliations
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
// 2008-07-26   SLH     Created
// 2008-08-12   SLH     Changed name of MySQL tests to make some room for
//                      additional SQL connectors in the future
// ========================================================================

if (!defined('MF_CORE_LIBDIR'))
        define('MF_CORE_LIBDIR', realpath(dirname(__FILE__) . '/../../../mf-core/') . '/libs/');

if (!defined('MF_DATASTORE_LIBDIR'))
        define('MF_DATASTORE_LIBDIR', realpath(dirname(__FILE__) . '/../') . '/');

if (!isset($GLOBALS['APP_LANG']))
        $GLOBALS['APP_LANG'] = 'en-us';

require_once ('PHPUnit/Framework/TestSuite.php');
require_once ('PHPUnit/Framework/TestCase.php');
require_once ('PHPUnit/TextUI/TestRunner.php');

// include files required for these tests
require_once (MF_CORE_LIBDIR . '../mf-core.inc.php');
require_once (MF_CORE_LIBDIR . 'Testsuite/Testsuite.inc.php');
require_once (MF_CORE_LIBDIR . 'Model/Model.inc.php');
require_once (MF_DATASTORE_LIBDIR . 'Datastore/Datastore.inc.php');
require_once (MF_DATASTORE_LIBDIR . 'DatastoreSQL/DatastoreSQL.inc.php');

// load the tests
require_once (MF_DATASTORE_LIBDIR . 'Datastore/Datastore.tests.php');
require_once (MF_DATASTORE_LIBDIR . 'DatastoreSQL/DatastoreSQL.tests.php');

AllTests::addTestSuite('DatastoreSQL_MySQL_Record_Tests');
AllTests::addTestSuite('DatastoreSQL_MySQL_Query_Tests');

?>