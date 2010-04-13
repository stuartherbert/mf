<?php

__mf_init_tests("Exception");

class MF_Exception_EnterpriseTest extends PHPUnit_Framework_TestCase
{
        /**
         *
         * @var MF_Exception_Enterprise
         */
        public $fixture;

        /**
         *
         * @var MF_Exception_Enterprise
         */
        public $fixtureWithRootCause;

        /**
         *
         * @var MF_Exception_Enterprise
         */
        public $fixtureWithSymptom;

        public function setup ()
        {
                $this->fixture = new MF_Exception_Enterprise(500, 1, 'param 1: %s, param 2: %s', array ('array 1', 'array 2'));
                $this->type    = 'MF_Exception_Enterprise';
                $this->file    = basename(__FILE__);
                $this->line    = __LINE__ - 3;

                $rootCause = new Test_Exception_RootCause();
                $this->fixtureWithRootCause = new MF_Exception_Enterprise(500, 0, 'param 1: %s, param 2: %s', array ('array 1', 'array 2'), $rootCause);

                $symptom = new Test_Exception_Symptom();
                $this->fixtureWithSymptom = new MF_Exception_Enterprise(500, 0, 'param 1: %s, param 2: %s', array ('array 1', 'array 2'), $symptom);
        }

        public function testCanGetErrorMessage()
        {
                $this->assertEquals('param 1: array 1, param 2: array 2', $this->fixture->getMessage());
        }

        public function testCanGetErrorCode()
        {
                $this->assertEquals(1, $this->fixture->getCode());
        }

        public function testCanGetHttpReturnCode()
        {
                $this->assertEquals(500, $this->fixture->getHttpReturnCode());
        }

        public function testCanGetFileWhereErrorHappened()
        {
                $this->assertEquals($this->file, basename($this->fixture->getFile()));
        }

        public function testCanGetLineWhereErrorHappened()
        {
                $this->assertEquals($this->line, $this->fixture->getLine());
        }

        public function testCanGetParamsForTheErrorMessage()
        {
                $params = $this->fixture->getParams();

                $this->assertTrue(is_array($params));
                $this->assertEquals('array 1', $params[0]);
                $this->assertEquals('array 2', $params[1]);
        }

        public function testCanGetCauseOfError()
        {
                // get the cause
                $cause = $this->fixtureWithRootCause->getCause();

                // test the results
                $this->assertTrue($cause instanceof Test_Exception_RootCause);
                $this->assertTrue($cause instanceof Exception);
        }

        public function testCanTellIfExceptionASymptom()
        {
                // fixture doesn't have a cause
                $this->assertFalse($this->fixture->hasCause());
                $this->assertNull($this->fixture->getCause());
                $this->assertFalse($this->fixture->wasCausedBy("Exception"));

                // these fixture do have a root cause
                $this->assertTrue($this->fixtureWithRootCause->hasCause());
                $this->assertTrue($this->fixtureWithSymptom->hasCause());
        }

        public function testCanTellWhatExceptionIsSymptomOf()
        {
                // all of the following are valid causes
                $this->assertTrue($this->fixtureWithSymptom->wasCausedBy('Test_Exception_Symptom'));
                $this->assertTrue($this->fixtureWithSymptom->wasCausedBy('Test_Exception_RootCause'));
                $this->assertTrue($this->fixtureWithSymptom->wasCausedBy("Exception"));

                // this is not a valid cause
                $this->assertFalse($this->fixtureWithRootCause->wasCausedBy('Test_Exception_Symptom'));
        }
}

?>