<?php

// ========================================================================
//
// Menu/Menu.exceptions.php
//              Exceptions thrown by the Menu component
//
//              Part of the Methodosity Framework for PHP applications
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2009 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2009-04-16   SLH     Created
// ========================================================================

class Menu_E_NoSuchOption extends Exception_Technical
{
        public function __construct($optionName, Exception $oCause = null)
        {
                parent::__construct
                (
                        l('Menu', 'E_NoSuchOption'),
                        array ($optionName),
                        $oCause
                );
        }
}

?>
