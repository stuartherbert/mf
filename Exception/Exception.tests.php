<?php

// ========================================================================
//
// Exception/Exception.tests.php
//              PHPUnit tests for the Exceptions component
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
// 2009-03-18   SLH     Added this header
// 2009-03-18   SLH     Fixed up to work with the new task-based approach
// 2009-03-18   SLH     Updated to incorporate error code support
// ========================================================================
//
// bootstrap the framework
define('UNIT_TEST', true);
define('APP_TOPDIR', realpath(dirname(__FILE__) . '/../../'));
require_once(APP_TOPDIR . '/mf/mf.inc.php');

// load additional files we explicitly require
__mf_require_once('Testsuite');

Testsuite_registerTests('EnterpriseException_Tests');
class EnterpriseException_Tests extends PHPUnit_Framework_TestCase
{
        public function setup ()
        {
                $e = new Exception('root cause', 1);
                $this->fixture = new Exception_Enterprise(0, 'param 1: %s, param 2: %s', array ('array 1', 'array 2'), $e);
                $this->type    = 'EnterpriseException';
                $this->file    = basename(__FILE__);
                $this->line    = 41;
        }

        public function testMessage()
        {
                $this->assertEquals('param 1: array 1, param 2: array 2', $this->fixture->getMessage());
        }

        public function testCode()
        {
                $this->assertEquals(0, $this->fixture->getCode());
        }

        public function testFile ()
        {
                $this->assertEquals($this->file, basename($this->fixture->getFile()));
        }

        public function testLine ()
        {
                $this->assertEquals($this->line, $this->fixture->getLine());
        }

        public function testCause ()
        {
                $e = $this->fixture->getCause();
                $this->assertTrue($e instanceof Exception);
        }
}

Testsuite_registerTests('ExceptionIterator_Tests');
class ExceptionIterator_Tests extends PHPUnit_Framework_TestCase
{
        public function setup ()
        {
                $e1 = new Exception('root cause', 1);
                $e2 = new Exception_Technical('cause #1', array(), $e1);
                $e3 = new Exception_Technical('cause #2', array(), $e2);
                $e4 = new Exception_Technical('cause #3', array(), $e3);
                $this->fixture = new Exception_Process(500, 1, 'it all went horribly wrong', array(), $e4);
                $this->line    = 83;
        }

        public function testGetIterator()
        {
                $iter = $this->fixture->getIterator();
                $this->assertTrue($iter instanceof Exception_Iterator);
        }

        public function testIsIterator()
        {
                $iter = $this->fixture->getIterator();
                $this->assertTrue($iter instanceof Iterator);
        }

        public function testRewind()
        {
                $iter = $this->fixture->getIterator();

                $i = 0;

                foreach ($iter as $e)
                {
                        if ($i < 2)
                                continue;

                        $iter->rewind();

                        $e = $iter->current();
                        $this->assertEquals('it all went horribly wrong', $e->getMessage());
                }
        }

        public function testCurrent()
        {
                $iter = $this->fixture->getIterator();

                $e = $iter->current();
                $this->assertTrue($e instanceof Exception_Process);
        }

        public function testNext1()
        {
                $iter = $this->fixture->getIterator();

                $iter->next();
                $e = $iter->current();
                $this->assertEquals('cause #3', $e->getMessage());
        }

        public function testNext2()
        {
                $iter = $this->fixture->getIterator();

                $iter->next();
                $iter->next();
                $e = $iter->current();
                $this->assertEquals('cause #2', $e->getMessage());
        }

        public function testNext3()
        {
                $iter = $this->fixture->getIterator();

                $iter->next();
                $iter->next();
                $iter->next();
                $e = $iter->current();
                $this->assertEquals('cause #1', $e->getMessage());
        }

        public function testNext4()
        {
                $iter = $this->fixture->getIterator();

                $iter->next();
                $iter->next();
                $iter->next();
                $iter->next();
                $e = $iter->current();
                $this->assertEquals('root cause', $e->getMessage());
        }

        public function testNext5()
        {
                $iter = $this->fixture->getIterator();

                $iter->next();
                $iter->next();
                $iter->next();
                $iter->next();
                $this->assertFalse($iter->next());
        }

}

Testsuite_registerTests('ProcessException_Tests');
class ProcessException_Tests extends EnterpriseException_Tests
{
        public function setup ()
        {
                $e = new Exception_Technical('oh my diety: %s', array ('god'));
                $this->fixture = new Exception_Process(500, 1, 'param 1: %s, param 2: %s', array ('array 1', 'array 2'), $e);
                $this->type    = 'ProcessException';
                $this->file    = basename(__FILE__);
                $this->line    = 186;
        }

        public function testParent()
        {
                $this->assertTrue($this->fixture instanceof Exception_Process);
        }

        public function testCode()
        {
                $this->assertEquals(1, $this->fixture->getCode());
        }
}

Testsuite_registerTests('Exception_Technical_Tests');
class Exception_Technical_Tests extends EnterpriseException_Tests
{
        public function setup ()
        {
                $e = new Exception_Technical('oh my diety: %s', array ('god'));
                $this->fixture = new Exception_Technical('param 1: %s, param 2: %s', array ('array 1', 'array 2'), $e);
                $this->type    = 'Exception_Technical';
                $this->file    = basename(__FILE__);
                $this->line    = 209;
        }

        public function testParent()
        {
                $this->assertTrue($this->fixture instanceof Exception_Technical);
        }
}

?>