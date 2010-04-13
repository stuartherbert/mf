<?php

require_once(dirname(__FILE__) . '/EnterpriseTest.php');

class MF_Exception_TechnicalTest extends MF_Exception_EnterpriseTest
{
        public function setup ()
        {
                $this->fixture = new MF_Exception_Technical('param 1: %s, param 2: %s', array ('array 1', 'array 2'));
                $this->type    = 'MF_Exception_Technical';
                $this->file    = basename(__FILE__);
                $this->line    = __LINE__ - 3;

                $rootCause = new Test_Exception_RootCause();
                $this->fixtureWithRootCause = new MF_Exception_Technical('param 1: %s, param 2: %s', array ('array 1', 'array 2'), $rootCause);

                $symptom = new Test_Exception_Symptom();
                $this->fixtureWithSymptom = new MF_Exception_Technical('param 1: %s, param 2: %s', array ('array 1', 'array 2'), $symptom);
        }

        public function testParent()
        {
                $this->assertTrue($this->fixture instanceof MF_Exception_Technical);
        }
}

?>
