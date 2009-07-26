<?php

// ========================================================================
//
// Debug/Debug.tests.php
//              PHPUnit tests for the Debug component
//
//              Part of the Methodosity Framework for PHP applications
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2007-2009 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2009-07-26   SLH     Created
// ========================================================================
//
// bootstrap the framework
define('UNIT_TEST', true);
define('APP_TOPDIR', realpath(dirname(__FILE__) . '/../../'));
require_once(APP_TOPDIR . '/mf/mf.inc.php');

// load additional files we explicitly require
__mf_require_once('Testsuite');

Testsuite_registerTests('Debug_Timer_Tests');
class Debug_Timer_Tests extends PHPUnit_Framework_TestCase
{
        public function setup()
        {
                $this->fixture = new Debug_Timer();
        }
        
        public function testCanFormatDuration()
        {
                // perform the basic test
                $this->assertEquals('00.100', $this->fixture->formatDuration(0.1, 2, 3));

                // now try something more challenging
                $this->assertEquals('01.234', $this->fixture->formatDuration(1.2345, 2, 3));

                // make sure we're formatting millisecs correctly
                $this->assertEquals('01.002', $this->fixture->formatDuration(1.002, 2, 3));

                // this is more of a note for future users of the class ...
                //
                // floating point numbers are not precise, therefore some
                // numbers will not be formatted correctly.
                //
                // here is an example
                $this->assertEquals('01.000', $this->fixture->formatDuration(1.001, 2, 3));
        }
}

?>