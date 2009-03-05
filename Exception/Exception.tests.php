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

registerTests('EnterpriseException_Tests');
class EnterpriseException_Tests extends PHPUnit_Framework_TestCase
{
        public function setup ()
        {
                $e = new Exception('root cause', 1);
                $this->fixture = new EnterpriseException('param 1: %s, param 2: %s', array ('array 1', 'array 2'), $e);
                $this->type    = 'EnterpriseException';
                $this->file    = basename(__FILE__);
                $this->line    = 24;
        }

        public function testMessage()
        {
                $this->assertEquals($this->type . ' : param 1: array 1, param 2: array 2', $this->fixture->getMessage());
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

registerTests('ExceptionIterator_Tests');
class ExceptionIterator_Tests extends PHPUnit_Framework_TestCase
{
        public function setup ()
        {
                $e1 = new Exception('root cause', 1);
                $e2 = new Exception_Technical('cause #1', array(), $e1);
                $e3 = new Exception_Technical('cause #2', array(), $e2);
                $e4 = new Exception_Technical('cause #3', array(), $e3);
                $this->fixture = new ProcessException('it all went horribly wrong', array(), $e4);
                $this->line    = 66;
        }

        public function testGetIterator()
        {
                $iter = $this->fixture->getIterator();
                $this->assertTrue($iter instanceof ExceptionIterator);
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
                        $this->assertEquals('ProcessException : it all went horribly wrong', $e->getMessage());
                }
        }

        public function testCurrent()
        {
                $iter = $this->fixture->getIterator();

                $e = $iter->current();
                $this->assertTrue($e instanceof ProcessException);
        }

        public function testNext1()
        {
                $iter = $this->fixture->getIterator();

                $iter->next();
                $e = $iter->current();
                $this->assertEquals('Exception_Technical : cause #3', $e->getMessage());
        }

        public function testNext2()
        {
                $iter = $this->fixture->getIterator();

                $iter->next();
                $iter->next();
                $e = $iter->current();
                $this->assertEquals('Exception_Technical : cause #2', $e->getMessage());
        }

        public function testNext3()
        {
                $iter = $this->fixture->getIterator();

                $iter->next();
                $iter->next();
                $iter->next();
                $e = $iter->current();
                $this->assertEquals('Exception_Technical : cause #1', $e->getMessage());
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

registerTests('ProcessException_Tests');
class ProcessException_Tests extends EnterpriseException_Tests
{
        public function setup ()
        {
                $e = new Exception_Technical('oh my diety: %s', array ('god'));
                $this->fixture = new ProcessException('param 1: %s, param 2: %s', array ('array 1', 'array 2'), $e);
                $this->type    = 'ProcessException';
                $this->file    = basename(__FILE__);
                $this->line    = 169;
        }

        public function testParent()
        {
                $this->assertTrue($this->fixture instanceof EnterpriseException);
        }

}

registerTests('Exception_Technical_Tests');
class Exception_Technical_Tests extends EnterpriseException_Tests
{
        public function setup ()
        {
                $e = new Exception_Technical('oh my diety: %s', array ('god'));
                $this->fixture = new Exception_Technical('param 1: %s, param 2: %s', array ('array 1', 'array 2'), $e);
                $this->type    = 'Exception_Technical';
                $this->file    = basename(__FILE__);
                $this->line    = 188;
        }

        public function testParent()
        {
                $this->assertTrue($this->fixture instanceof EnterpriseException);
        }
}

?>