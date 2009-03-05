<?php

// ========================================================================
//
// Model/AllTests.php
//              Unit test suite for the Model component
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
// 2008-07-28   SLH     Created
// ========================================================================

if (!defined('MF_CORE_LIBDIR'))
        define('MF_CORE_LIBDIR', realpath(dirname(__FILE__) . '/../../../mf-core/') . '/libs/');

if (!isset($GLOBALS['APP_LANG']))
        $GLOBALS['APP_LANG'] = 'en-us';

require_once ('PHPUnit/Framework/TestSuite.php');
require_once ('PHPUnit/Framework/TestCase.php');
require_once ('PHPUnit/TextUI/TestRunner.php');

// include files required for these tests
require_once (MF_CORE_LIBDIR . '../mf-core.inc.php');
require_once (MF_CORE_LIBDIR . 'Testsuite/Testsuite.inc.php');
require_once (MF_CORE_LIBDIR . 'Model/Model.inc.php');
//require_once (MF_DATASTORE_LIBDIR . 'Datastore/Datastore.inc.php');

// load the tests
require_once (MF_CORE_LIBDIR . 'Model/Model.tests.php');

AllTests::addTestSuite('Model_Definitions_Tests');
AllTests::addTestSuite('Model_Tests');

?>