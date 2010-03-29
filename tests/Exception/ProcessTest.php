<?php

require_once(dirname(__FILE__) . '/EnterpriseTest.php');

class MF_Exception_Process_Tests extends MF_Exception_Enterprise_Tests
{
        public function setup ()
        {
                $e = new MF_Exception_Technical('oh my diety: %s', array ('god'));
                $this->fixture = new MF_Exception_Process(500, 1, 'param 1: %s, param 2: %s', array ('array 1', 'array 2'), $e);
                $this->type    = 'MF_Exception_Process';
                $this->file    = basename(__FILE__);
                $this->line    = __LINE__ - 3;
        }

        public function testParent()
        {
                $this->assertTrue($this->fixture instanceof MF_Exception_Process);
        }

        public function testCode()
        {
                $this->assertEquals(1, $this->fixture->getCode());
        }
}

?>
