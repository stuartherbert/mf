<?php

// ========================================================================
//
// App/App.exceptions.php
//              The different exceptions thrown by the App component
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
// 2007-12-02   SLH     Created
// 2009-03-02   SLH     Added App_E_InternalServerError
// ========================================================================

class App_E_InternalServerError extends Exception_Process
{
        public function __construct(Exception $oCause)
        {
                parent::__construct
                (
                        500,
                        1,
                        app_l('App', 'E_INTERNAL_SERVER_ERROR'),
                        array(),
                        $oCause
                );
        }
}

?>