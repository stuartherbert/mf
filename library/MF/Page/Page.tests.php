<?php

// ========================================================================
//
// Page/Page.tests.php
//              Unit tests for the Page module
//
//              Part of the Modular Framework for PHP
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2009 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2009-04-15   SLH     Created
// ========================================================================

// bootstrap the framework
define('UNIT_TEST', true);
define('APP_TOPDIR', realpath(dirname(__FILE__) . '/../../'));
require_once(APP_TOPDIR . '/mf/mf.inc.php');

// load additional files we explicitly require
__mf_require_once('Testsuite');

class Example_Layout1 extends Page_Layout
{
}

class Example_DefaultLayout1 extends Page_Layout
{
}

class Example_Default_Layout2 extends Page_Layout
{
}

class Example_Default_Layout3 extends Page_Layout
{
        public function __construct()
        {
                $this->layoutFile = 'defaultLayout';

                parent::__construct();
        }
}

Testsuite_registerTests('Page_Layout_Tests');

class Page_Layout_Tests extends PHPUnit_Framework_TestCase
{
        function testDefaultLayoutNamesAreSet()
        {
                // make sure that the first word is decapitalised
                // (small camelcase)
                $test1 = new Example_Layout1();
                $this->assertEquals('layout1', $test1->layoutFile);

                // make sure that the first word is decapitalised, but
                // other letters are left alone
                $test2 = new Example_DefaultLayout1();
                $this->assertEquals('defaultLayout1', $test2->layoutFile);

                // make sure separate words are concatenated together
                $test3 = new Example_Default_Layout2();
                $this->assertEquals('defaultLayout2', $test3->layoutFile);

                // make sure the constructor does not replace any layout
                // file that we have already specified
                $test4 = new Example_Default_Layout3();
                $this->assertEquals('defaultLayout', $test4->layoutFile);
        }
}

?>