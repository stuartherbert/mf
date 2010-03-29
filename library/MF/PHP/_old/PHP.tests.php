<?php

// ========================================================================
//
// PHP/PHP.tests.php
//              PHPUnit tests for the PHP component
//
//              Part of the Methodosity Framework for PHP applications
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2007-2010 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2007-08-11   SLH     Consolidated from individual files
// 2009-03-18   SLH     Fixed up to use the new task-based approach
// ========================================================================

// bootstrap the framework
define('UNIT_TEST', true);
define('APP_TOPDIR', realpath(dirname(__FILE__) . '/../../'));
require_once(APP_TOPDIR . '/mf/mf.inc.php');

// load additional files we explicitly require
__mf_require_once('Testsuite');

Testsuite_registerTests('PHP_Array_Tests');


Testsuite_registerTests('PHP_Network_Tests');
class PHP_Network_Tests extends PHPUnit_Framework_TestCase
{
        public $aIpAddresses = array
        (
                '127.0.0.1'     => 2130706433,
                '10.227.136.20' => 182683668,
        );

        public function testCanConvertIpAddressToInt()
        {
                foreach ($this->aIpAddresses as $ipAddress => $ipInt)
                {
                        $this->assertEquals($ipInt, ipAddress_to_int($ipAddress));
                }
        }

        public function testCanConvertIntsToIpAddress()
        {
                foreach ($this->aIpAddresses as $ipAddress => $ipInt)
                {
                        $this->assertEquals($ipAddress, int_to_ipAddress($ipInt));
                }
        }
}

?>