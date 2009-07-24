<?php

// ========================================================================
//
// App/App.classes.php
//              Defines the classes required for the mainLoop to function
//
//              Part of the Methodosity Framework for PHP Applications
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
// 2007-12-02   SLH     Created from separate components
// 2008-01-03   SLH     Added AppPage class
// 2008-01-03   SLH     Moved Render_Messages class to AppMessages
// 2008-01-06   SLH     Stopped using constants for language strings
// 2008-10-12   SLH     Moved responsibility for loading the controller
//                      out of AppRequest
// 2008-10-26   SLH     Added underscore to class names
// 2008-11-03   SLH     Added new class App to be responsible for dishing
//                      out all object instances
//                      Removed App_Controller class (replaced by App class)
//                      Removed App_User class (replaced by App class)
//                      Removed App_Theme class (replaced by App class)
// 2009-02-29   SLH     Simplified App class, to be the global holder for
//                      all things to do with an MF app
// 2009-03-24   SLH     Added support for a user authenticator object
// 2009-03-25   SLH     Revamped theme support
// 2009-03-30   SLH     Moved user authentication out to the User_Engine
// 2009-03-31   SLH     Moved the bulk of the mainLoop into mf.mainLoop
//                      and removed App_Engine
// 2009-03-31   SLH     Added support for browser detection, to enable
//                      more fine-grained theme support
// 2009-04-01   SLH     Added support for determining the baseUrl of the
//                      app (needed by Routing_Manager)
// 2009-04-15   SLH     Moved App_Page out into separate module
// 2009-04-15   SLH     Language support is now loaded first; required
//                      because creating App_Response loads language strings
//                      now (via new Page_Manager class)
// 2009-04-16   SLH     Promoted conditions out of Routing to be a top-level
//                      piece of data
// 2009-05-01   SLH     App_Conditions now does a convincing job of
//                      pretending to be an array
// 2009-05-20   SLH     Added currentRoute to App_Request
// 2009-06-10   SLH     Removed messages from App_Response; they are now
//                      provided on a per-block basis
// 2009-07-07   SLH     App_Conditions now uses the underlying PHP_Array
//                      instead of implementing everything itself
// 2009-07-08	SLH	App_Languages has been moved out into Language
//			module
// 2009-07-14   SLH     Added a general debugging capability
// 2009-07-24   SLH     Disable debugging during unit testing to avoid
//                      errors about FirePHP
// ========================================================================

class App
{
        /**
         *
         * @var App_Request
         */
        public static $request           = null;

        /**
         *
         * @var App_Response
         */
        public static $response          = null;

        /**
         * @var User_Manager
         */
        public static $users             = null;

        /**
         *
         * @var User
         */
        public static $user              = null;

        /**
         *
         * @var Browser_Manager
         */
        public static $browsers          = null;

        /**
         *
         * @var Browser
         */
        public static $browser           = null;
        /**
         *
         * @var Theme_Manager
         */
        public static $themes            = null;

        /**
         * @var Theme_BaseTheme
         */
        public static $theme             = null;
        
        /**
         *
         * @var Language_Manager
         */
        public static $languages         = null;

        /**
         *
         * @var Routing_Manager
         */
        public static $routes            = null;

        /**
         *
         * @var Page_Manager
         */
        public static $pages             = null;

        /**
         *
         * @var array
         */
        public static $config            = array();

        /**
         *
         * @var App_Conditions
         */
        public static $conditions        = array();

        /**
         * @var FirePHP
         */

        public static $debug             = array();

        // cannot be instantiated
	private function __construct()
        {

        }

        /**
         * this is where we put the things absolutely required to allow
         * other framework modules to load successfully
         */
        public static function initInitial()
        {
		// load the general environment first
                // the order matters!
                self::$languages  = new Language_Manager();
        }

        /**
         * setup the things that every app has
         *
         * it is up to the mainLoop() of the different types of app
         * to setup the rest (user, page, and theme)
         */

        public static function init()
        {
                self::$browsers   = new Browser_Manager();
                self::$users      = new User_Manager();
                self::$routes     = new Routing_Manager();
                self::$pages      = new Page_Manager();
                self::$themes     = new Theme_Manager();
                self::$debug      = FirePHP::getInstance(true);

                // disable debugging if we are unit testing
                if (defined('UNIT_TEST') && UNIT_TEST)
                {
                        self::$debug->setEnabled(false);
                }

		// with the general environment loaded, we can now load
		// the modules that are app-specific
                self::$request    = new App_Request();
                self::$response   = new App_Response();
                self::$conditions = new App_Conditions();
        }
}

App::initInitial();

// ========================================================================

class App_Request
{
        /**
         * The URL of the homepage of our app.  All other URLs for our
         * app sit beneath this one
         * 
         * @var string
         */
        public $baseUrl      = null;

        // holds the path the user has requested, which we later
        // decode to determine which class to route the request to
        public $pathInfo     = null;

        /**
         * What route are we being asked to process?
         *
         * @var Routing_Route
         */
        public $currentRoute = null;

        /**
         * What type of content is the user asking for?
         *
         * this is set automatically by the constructor, but the AnonApi
         * and Api classes will override this based on any format parameter
         * included in the URL
         * 
         * @var string
         */
        public $requestedContentType = null;

        // the different types of content that can be requested
        const CT_XHTML   = 1;
        const CT_XML     = 2;
        const CT_JSON    = 3;
        const CT_PHP     = 4;
        const CT_CONSOLE = 5;

        protected $contentTypeNames = array
        (
                CT_XHTML   => 'xhtml',
                CT_XML     => 'xml',
                CT_JSON    => 'json',
                CT_PHP     => 'php',
                CT_CONSOLE => 'term',
        );

        public function __construct()
        {
                $this->baseUrl              = $this->determineBaseUrl();
                $this->pathInfo             = $this->determinePathInfo();
                $this->requestedContentType = $this->determineContentType();
        }

        public function determineBaseUrl()
        {
                // the SCRIPT_NAME is always <url>/index.php, which is
                // why this works at all :)
                return dirname($_SERVER['SCRIPT_NAME']);
        }

        public function determinePathInfo()
        {
                $publicDir = dirname($_SERVER['SCRIPT_NAME']) . '/';
                $strippedPath = str_replace($publicDir, '', $_SERVER['REDIRECT_URL']);
                if ($strippedPath[0] != '/')
                {
                        $strippedPath = '/' . $strippedPath;
                }

                return $strippedPath;
        }

        /**
         * work out what type of content the user is asking for
         */
        public function determineContentType()
        {
                // step 1: are we in a browser at all?
                if (!isset($_SERVER))
                {
                        // no, so we must be a console app
                        return App_Request::CT_CONSOLE;
                }

                // TODO: detect content negotiation properly
                return App_Request::CT_XHTML;
        }
}

// ========================================================================

class App_Response
{
	public $responseCode = 200;

        /**
         *
         * @var Page
         */
        public $page         = null;

        public function __construct()
        {
        	$this->page     = new Page();
        }
}

// ========================================================================

class App_Conditions extends PHP_ObjectArray
{
        public function __construct()
        {
                parent::__construct();

                // by default, users start off logged out
                $this->loggedIn = false;
        }
        
        public function resetConditions()
        {
                $this->clear();
        }

        // ================================================================
        // Intercept attempts to set loggedIn & loggedOut, to ensure
        // that the underlying conditions are always consistent

        public function setLoggedIn($value = true)
        {
                if ($value)
                {
                        $this->__data['loggedIn']  = true;
                        $this->__data['loggedOut'] = false;
                }
                else
                {
                        $this->__data['loggedIn']  = false;
                        $this->__data['loggedOut'] = true;
                }
        }

        public function setLoggedOut($value = true)
        {
                if ($value)
                {
                        $this->__data['loggedIn']  = false;
                        $this->__data['loggedOut'] = true;
                }
                else
                {
                        $this->__data['loggedIn']  = true;
                        $this->__data['loggedOut'] = false;
                }
        }
}

?>