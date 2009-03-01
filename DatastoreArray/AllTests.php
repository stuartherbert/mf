<?php

// ========================================================================
//
// DatastoreArray/AllTests.php
//              Unit test suite for the DatastoreArray library
//
//              Part of the Modular Framework for PHP Applications
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2007-2009 Stuart Herbert
//              Released under v3 of the GNU Affreo Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2007-07-24   SLH     Created
// ========================================================================

if (!defined('PROJECT36_TOPDIR'))
        define('PROJECT36_TOPDIR', realpath('../../../') . DIRECTORY_SEPARATOR);
if (!defined('TESTSUITE'))
        define('TESTSUITE', true);

require_once (PROJECT36_TOPDIR . 'conf' . DIRECTORY_SEPARATOR . 'include.php');
require_once ('PHPUnit/Framework/TestSuite.php');
require_once ('PHPUnit/Framework/TestCase.php');
require_once ('PHPUnit/TextUI/TestRunner.php');

// load the tests
require_once (PROJECT36_TOPDIR . '/components/Users/Users.models.tests.php');

if (!defined('PHPUnit_MAIN_METHOD'))
{
	define('PHPUnit_MAIN_METHOD', 'Users::main');
}

class Users_AllTests extends PHPUnit_Framework_TestSuite
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('Project 36');

                $suite->addTestSuite('Users_Models_Tests');

		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'Users::main')
{
	Users_AllTests::main();
}

?>
