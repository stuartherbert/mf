<?php

class MF_Exception_Iterator_Tests extends PHPUnit_Framework_TestCase
{
        public function setup ()
        {
                $e1 = new Exception('root cause', 1);
                $e2 = new MF_Exception_Technical('cause #1', array(), $e1);
                $e3 = new MF_Exception_Technical('cause #2', array(), $e2);
                $e4 = new MF_Exception_Technical('cause #3', array(), $e3);
                $this->fixture = new MF_Exception_Process(500, 1, 'it all went horribly wrong', array(), $e4);
                $this->line    = 83;
        }

        public function testGetIterator()
        {
                $iter = $this->fixture->getIterator();
                $this->assertTrue($iter instanceof MF_Exception_Iterator);
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
                $this->assertTrue($e instanceof MF_Exception_Process);
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

?>