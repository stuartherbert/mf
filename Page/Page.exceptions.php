<?php

// ========================================================================
//
// Page/Page.exceptions.php
//              Exceptions defined by the Page module
//
//              Part of the Methodosity Framework for PHP
//              http://blog.stuartherbert.com/php/mf/
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
// 2009-04-15   SLH     Created
// ========================================================================

class Page_E_NoLayout extends Exception_Technical
{
        public function __construct (Exception $oCause = null)
        {
                parent::__construct(
                        app_l('Page', 'E_NoLayout'),
                        array(),
                        $oCause
                );
        }
}

class Page_E_OutputFormatNotSupported extends Exception_Technical
{
        public function __construct (Page_Component $origin, $outputFormat, Exception $oCause = null)
        {
                parent::__construct(
                        app_l('Page', 'E_OutputFormatNotSupported'),
                        array (get_class($origin), $outputFormat),
                        $oCause
                );
        }
}

?>
