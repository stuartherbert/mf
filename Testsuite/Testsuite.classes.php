<?php

// ========================================================================
//
// Testsuite/Testsuite.classes.php
//              Helper coded for running unit tests
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
// 2008-07-19   SLH     Created
// 2008-07-25   SLH     Renamed TestSuite to AllTests to silence
//                      PHPUnit warnings
// 2009-03-18   SLH     Fixed typo in message of which test suites have
//                      been registered
// ========================================================================

class AllTests
{
        static $testSuites = array();

	public static function addTestsuite($suiteName)
        {
                echo "Registering test suite $suiteName\n";
        	self::$testSuites[] = $suiteName;
        }

        public static function suite()
        {
        	$suite = new PHPUnit_Framework_TestSuite('MethodosityFramework');

                foreach (self::$testSuites as $testSuite)
                {
                	$suite->addTestSuite($testSuite);
                }

                return $suite;
        }

        public static function run()
        {
        	 PHPUnit_TextUI_TestRunner::run(self::suite());
        }
}

?>
