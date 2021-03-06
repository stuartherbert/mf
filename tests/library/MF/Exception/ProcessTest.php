<?php

require_once(dirname(__FILE__) . '/EnterpriseTest.php');

class MF_Exception_ProcessTest extends MF_Exception_EnterpriseTest
{
        /**
         *
         * @var MF_Exception_Process
         */
        public $fixture;
        
        public function setup ()
        {
                $this->fixture = new MF_Exception_Process(404, 1, 'param 1: %s, param 2: %s', array ('array 1', 'array 2'));
                $this->type    = 'MF_Exception_Process';
                $this->file    = basename(__FILE__);
                $this->line    = __LINE__ - 3;

                $rootCause = new Test_Exception_RootCause();
                $this->fixtureWithRootCause = new MF_Exception_Process(500, 0, 'param 1: %s, param 2: %s', array ('array 1', 'array 2'), $rootCause);

                $symptom = new Test_Exception_Symptom();
                $this->fixtureWithSymptom = new MF_Exception_Process(500, 0, 'param 1: %s, param 2: %s', array ('array 1', 'array 2'), $symptom);
        }

        public function testParent()
        {
                $this->assertTrue($this->fixture instanceof MF_Exception_Process);
        }

        public function testCode()
        {
                $this->assertEquals(1, $this->fixture->getCode());
        }

        public function testCanGetHttpReturnCode()
        {
                $this->assertEquals(404, $this->fixture->getHttpReturnCode());
        }
}

?>
