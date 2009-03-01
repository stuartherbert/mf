<?php

// ========================================================================
//
// Core/Core.classes.php
//              Base class for use elsewhere in the framework
//
//              Part of the Modular Framework for PHP applications
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2008-2009 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2008-07-19   SLH     Created
// ========================================================================

class Core
{
	function __call_0($object, $funcName, $args)
        {
                return $object->$funcName();
        }

        function __call_1($object, $funcName, $args)
        {
                return $object->$funcName($args[0]);
        }

        function __call_2($object, $funcName, $args)
        {
                return $object->$funcName($args[0], $args[1]);
        }

        function __call_3($object, $funcName, $args)
        {
                return $object->$funcName($args[0], $args[1], $args[2]);
        }

        function __call_4($object, $funcName, $args)
        {
                return $object->$funcName($args[0], $args[1], $args[2], $args[3]);
        }

        function __call_5($object, $funcName, $args)
        {
                return $object->$funcName($args[0], $args[1], $args[2], $args[3], $args[4]);
        }

        function __call_6($object, $funcName, $args)
        {
                return $object->$funcName($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);
        }

        function __call_7($object, $funcName, $args)
        {
                return $object->$funcName($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6]);
        }

        function __call_8($object, $funcName, $args)
        {
                return $object->$funcName($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7]);
        }

        function __call_9($object, $funcName, $args)
        {
                return $object->$funcName($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8]);
        }

        public function requireValidMethod($method)
        {
                if (!method_exists($this, $method))
                {
                        throw new PHP_E_NoSuchMethod($method, $this);
                }
        }


}

?>