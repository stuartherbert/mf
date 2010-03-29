<?php

class MF_Exception_Enterprise_Tests extends PHPUnit_Framework_TestCase
{
        public function setup ()
        {
                $e = new Exception('root cause', 1);
                $this->fixture = new MF_Exception_Enterprise(0, 'param 1: %s, param 2: %s', array ('array 1', 'array 2'), $e);
                $this->type    = 'MF_Exception_Enterprise';
                $this->file    = basename(__FILE__);
                $this->line    = __LINE__ - 3;
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

?>