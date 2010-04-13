<?php

__mf_init_tests('Obj');

class MF_Obj_MixinTest extends PHPUnit_Framework_TestCase
{
        public function testCannotCreateMixinObjectUnlessExtending()
        {
                $caughtException = false;
                try
                {
                        $testObj = new Test_Obj_ExtMixin('trout');
                }
                catch (Exception $e)
                {
                        if ($e instanceof MF_PHP_E_ConstraintFailed)
                        {
                                $caughtException = true;
                        }
                }
                $this->assertTrue($caughtException);
        }
}

?>