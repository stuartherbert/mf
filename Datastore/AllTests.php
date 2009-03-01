<?php

// ========================================================================
//
// Datastore/AllTests.php
//              Unit test suite for the Datastore component
//
//              Part of the Modular Framework for PHP appliations
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
// 2008-08-19   SLH     Created
// ========================================================================

if (!defined('MF_CORE_TOPDIR'))
        define('MF_CORE_TOPDIR', realpath('../../../mf-core/') . '/');

require_once ('PHPUnit/Framework/TestSuite.php');
require_once ('PHPUnit/Framework/TestCase.php');
require_once ('PHPUnit/TextUI/TestRunner.php');

// include files required for these tests
require_once (MF_CORE_TOPDIR . 'libs/Core/Core.inc.php');
require_once (MF_CORE_TOPDIR . 'libs/Testsuite/Testsuite.inc.php');
require_once (MF_CORE_TOPDIR . 'libs/Model/Model.inc.php');

// load the tests
require_once ('./libs/Datastore/Datastore.tests.php');

class Datastore_AllTests extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('Datastore');

                $suite->addTestSuite('Users_Models_Tests');

		return $suite;
	}
}

AllTests::addTestSuite('Datastore_AllTests');

?>