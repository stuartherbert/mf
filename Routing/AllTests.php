<?php

// ========================================================================
//
// Routing/AllTests.php
//              Unit test suite for the Routing component
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
// 2008-09-09   SLH     Created
// ========================================================================

if (!defined('MF_CORE_TOPDIR'))
        define('MF_CORE_TOPDIR', realpath(dirname(__FILE__) . '/../../../mf-core/') . '/');

if (!isset($GLOBALS['APP_LANG']))
	$GLOBALS['APP_LANG']='en-us';

require_once ('PHPUnit/Framework/TestSuite.php');
require_once ('PHPUnit/Framework/TestCase.php');
require_once ('PHPUnit/TextUI/TestRunner.php');

// include files required for these tests
require_once (MF_CORE_TOPDIR . 'mf-core.inc.php');
require_once (MF_CORE_TOPDIR . 'libs/Testsuite/Testsuite.inc.php');
require_once (MF_CORE_TOPDIR . 'libs/Routing/Routing.inc.php');

// load the tests
require_once (dirname(__FILE__) . '/Routing.tests.php');

class Routing_AllTests extends PHPUnit_Framework_TestSuite
{
	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('Routing');

                $suite->addTestSuite('Routing_Tests');

		return $suite;
	}
}

AllTests::addTestSuite('Routing_AllTests');

?>
