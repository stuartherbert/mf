<?php

// ========================================================================
//
// Obj/Obj.exceptions.php
//              The different exceptions thrown by the Obj component
//
//              Part of the Methodosity Framework for PHP applications
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2007-2010 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2009-05-22   SLH     Created
// ========================================================================

// ========================================================================
//
// NOTE:
//
//      Obj cannot use translations in exceptions, to avoid a circular
//      dependency between Obj, Language and App modules
//
// ========================================================================

class Obj_E_NoSuchMethod extends Exception_Technical
{
        public function __construct($methodName, $obj, Exception $oCause = null)
        {
                parent::__construct(
                        "No such method '%s' for object of type '%s'",
                        array ($methodName, get_class($obj)),
                        $oCause
                );
        }
}

class Obj_E_NoSuchProperty extends Exception_Technical
{
        public function __construct($propertyName, $obj, Exception $oCause = null)
        {
                parent::__construct(
                        "No such property '%s' for object of type '%s'",
                        array ($propertyName, get_class($obj)),
                        $oCause
                );
        }
}

?>