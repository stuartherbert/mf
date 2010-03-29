<?php

class MF_PHP_Network_Tests extends PHPUnit_Framework_TestCase
{
        public $aIpAddresses = array
        (
                '127.0.0.1'     => 2130706433,
                '10.227.136.20' => 182683668,
        );

        public function testCanConvertIpAddressToInt()
        {
                foreach ($this->aIpAddresses as $ipAddress => $ipInt)
                {
                        $this->assertEquals($ipInt, ipAddress_to_int($ipAddress));
                }
        }

        public function testCanConvertIntsToIpAddress()
        {
                foreach ($this->aIpAddresses as $ipAddress => $ipInt)
                {
                        $this->assertEquals($ipAddress, int_to_ipAddress($ipInt));
                }
        }
}

?>
