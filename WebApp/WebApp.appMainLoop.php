<?php

// ========================================================================
//
// WebApp/WebApp.appMainLoop.php
//              Main app loop for browser-based web applications
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
// 2008-10-01   SLH     Created
// 2008-10-26   SLH     Moved from snippets/appMainLoop.php
// ========================================================================

if (!defined('APP_TOPDIR'))
{
        throw new Exception('APP_TOPDIR not defined');
}

// ========================================================================
//
// Supported services
//
// ------------------------------------------------------------------------

// work out what page was requested
$oRequest = new App_Request();

// create an object to track our response
$oResponse = new App_Response();

// work out who has requested the page
$oUser = App_User::newUser($oRequest, App_Request::$userDB);

// work out what the controller class is
$oController = App_Controller::newController
(
        $oRequest,
        $oResponse,
        $oUser
);

// work out how the page should be displayed
// unlike website apps, this app uses themes to determine what
// format the result should be returned as
$oTheme = App_Theme::newTheme($oRequest, $oUser);

// at this point ...
//
// $oRequest
//      contains all the information about what the user wants to do
//
// $oResponse
//      contains all the object to hold the data we will return
//
// $oController
//      contains the controller object
//
// $oUser
//      represents the user at the other end of the web browser,
//      spider, API, or a.n.other way of accessing this app
//
// $oTheme
//      contains the theme object

// tell the controller to do its thing
$oController->process($oRequest, $oResponse, $oUser);

// render the page
$oTheme->process($oRequest, $oResponse, $oUser);

?>