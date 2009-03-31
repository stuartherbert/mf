<?php

// ========================================================================
//
// Theme/Theme.exceptions.php
//              The different exceptions thrown by the Theme component
//
//              Part of the Methodosity Framework for PHP applications
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2007-2009 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2009-03-31   SLH     Created
// ========================================================================

class Theme_E_NoLayoutSet extends Exception_Technical
{
        public function __construct(Exception $oCause = null)
        {
                parent::__construct
                (
                        l('Theme', 'E_NO_LAYOUT_SET'),
                        array(),
                        $oCause
                );
        }
}
class Theme_E_NoSuchLayout extends Exception_Technical
{
        public function __construct($layout, Exception $oCause = null)
        {
                parent::__construct
                (
                        l('Theme', 'E_NO_SUCH_LAYOUT'),
                        array($layout),
                        $oCause
                );
        }
}

?>