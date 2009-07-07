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
         * @var App_Languages
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

        // cannot be instantiated
	private function __construct()
        {

        }

        /**
         * setup the things that every app has
         *
         * it is up to the mainLoop() of the different types of app
         * to setup the rest (user, page, and theme)
         */
        
        public static function init()
        {
                // these are part of the App module
                // the order matters!
                self::$languages  = new App_Languages();
                self::$request    = new App_Request();
                self::$response   = new App_Response();
                self::$conditions = new App_Conditions();

                // these have their own modules
                //
                // at the moment, the order we create them does not
                // matter, but one day it might!
                self::$browsers   = new Browser_Manager();
                self::$users      = new User_Manager();
                self::$routes     = new Routing_Manager();
                self::$pages      = new Page_Manager();
                self::$themes     = new Theme_Manager();
        }
}

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

class App_Languages
{
        /**
         * @var string the current language being used
         */

        public $currentLanguage = null;

        /**
         *
         * @var string the default language for this app
         */
        public $defaultLanguage = 'en-us';

        /**
         *
         * @var array a list of all the languages we know about, ordered
         *            by the module that supports the language
         */
        protected $languagesByModule = array();

        /**
         *
         * @var array a list of all the languages we know about, ordered
         *            by language
         */
        protected $languagesByLanguage = array();

        /**
         *
         * @var array a list of all the languages we know about, for
         *            http_negotiate_language()
         */
        protected $languages = array('en-us');

        /**
         * Allows each module in the app to say what languages it supports
         *
         * We don't actually load the language files at this time; no point
         * going to all that expense of RAM, CPU and disk i/o until we
         * know which one of the languages will actually be used
         *
         * @param <type> $module
         * @param <type> $modulePath
         * @param <type> $language
         */
        public function moduleSpeaks($module, $modulePath, $language)
        {
                $this->languagesByModule[$module][$language]   = $modulePath;
                $this->languagesByLanguage[$language][$module] = $modulePath;
                $this->languages[$language] = $language;
        }

        /**
         * Switch the whole app to use a different language
         *
         * @param string $language
         * @return boolean was the language actually changed?
         */

        public function changeLanguage($language)
        {
                // is this language supported?
                if (!isset($this->languagesByLanguage[$language]))
                {
                        // we daren't throw an exception here, because
                        // we're not sure we have any languages loaded
                        // at all!
                        trigger_error($language . ' not supported by this app');
                }

                $this->currentLanguage = $language;
                return true;
        }

        /**
         * Ask the user's browser which language they would like us to use
         */
        public function changeLanguageBasedOnBrowserHint()
        {
                if (function_exists('http_negotiate_language'))
                {
                        $this->changeLanguage(http_negotiate_language(self::$languages));
                }
        }

        public function setDefaultLanguage($language)
        {
                if(!isset($this->languages))
                {
                        // TODO: handle this error better
                        return false;
                }

                $this->defaultLanguage = $language;
                if ($this->currentLanguage == null)
                {
                        $this->currentLanguage = $language;
                }
        }

        public function loadLanguageFile($module, $language)
        {
                // work out the file we need to include
                $modulePath  = $this->languagesByModule[$module][$language];
                $includeFile = $modulePath . '/' . $module . '.lang.' . $language . '.php';

                // load the file if it exists
                if (file_exists($includeFile))
                {
                        require_once($includeFile);
                }
                else
                {
                        // file does not exist, make a note in the translations
                        // so that we do not try and load it again
                        $this->translations[$module][$language] = false;
                }
        }

        public function addTranslationsForModule($module, $language, $translations)
        {
                constraint_mustBeArray($translations);
                $this->translations[$module][$language] = $translations;
        }

        /**
         * get a translated version of a string
         *
         * @param string $module The name of the module where this string
         *        is defined
         * @param string $stringName The name of the string we want a
         *        translation for
         * @return string The translated string, or the name of the token
         *                that needs translating
         */
        public function getTranslation($module, $stringName)
        {
                // step 1 - get the translation from the app's current
                //          language
                $return = $this->getTranslationForLanguage($module, $this->currentLanguage, $stringName);
                
                // step 2 - check the default language strings if the string
                //          we seek hasn't been translated for the app's
                //          current language
                if (!$return)
                {
                        $return = $this->getTranslationForLanguage($module, $this->defaultLanguage, $stringName);
                }
                
                // step 3 - still no translation? return the token then
                if (!$return)
                {
                        $return = $module . '::' . $stringName;
                }
                
                // all done - send back what we did / didn't find
                return $return;
        }
        
        public function getTranslationForLanguage($module, $language, $stringName)
        {
                // do we need to load the language file?
                if (!isset($this->translations[$module][$language]))
                {
                        $this->loadLanguageFile($module, $language);
                }
                else if ($this->translations[$module][$language] === false)
                {
                        return false;
                }
                
                // if we get here, the language file is loaded
                if (isset($this->translations[$module][$language][$stringName]))
                {
                        return $this->translations[$module][$language][$stringName];
                }
                
                // if we get here, then we do not have a suitable translation
                return false;
        }
}

// ========================================================================

class App_Conditions implements ArrayAccess, IteratorAggregate
{
        /**
         *
         * @var array
         */
        protected $conditions = array();

        // ================================================================
        // Voodoo stuff, to make App_Conditions easier to use
        // ----------------------------------------------------------------

        public function __get($name)
        {
                // step 1: do we have an override method?
                $method = 'get' . ucfirst($name);
                if (method_exists($this, $method))
                {
                        return $this->$method();
                }

                // step 2: return the data in the array
                if (!isset($this->conditions[$name]))
                {
                        return null;
                }

                return $this->conditions[$name];
        }

        public function __set($name, $value)
        {
                // step 1: do we have an override method?
                $method = 'set' . ucfirst($name);
                if (method_exists($this, $method))
                {
                        return $this->$method($value);
                }

                // step 2: just store the data directly in the array
                $this->conditions[$name] = $value;
        }

        public function __isset($name)
        {
                // step 1: do we have an override method?
                $method = 'isset' . ucfirst($name);
                if (method_exists($this, $method))
                {
                        return $this->$method();
                }

                // step 2: just check on the data in the array
                return isset($this->conditions[$name]);
        }

        public function resetConditions()
        {
                $this->conditions = array();
        }

        // ================================================================
        // More voodoo ... array iterator support

        public function getIterator()
        {
                return new PHP_Array($this->conditions);
        }

        // ================================================================
        // Just in case you've not had enough voodoo ...
        // array [] operator support
        
        public function offsetSet($name, $value)
        {
                return $this->__set($name, $value);
        }

        public function offsetExists($name)
        {
                return $this->__isset($name);
        }

        public function offsetUnset($name)
        {
                unset($this->conditions[$name]);
        }

        public function offsetGet($name)
        {
                return $this->__get($name);
        }

        // ================================================================
        // The reasons for the voodoo ...
        //
        //
        // ----------------------------------------------------------------

        public function setLoggedin($value)
        {
                if ($value)
                {
                        $this->conditions['loggedIn']  = true;
                        $this->conditions['loggedOut'] = false;
                }
                else
                {
                        $this->conditions['loggedIn']  = false;
                        $this->conditions['loggedOut'] = true;
                }
        }

        public function setLoggedout($value)
        {
                if ($value)
                {
                        $this->conditions['loggedIn']  = false;
                        $this->conditions['loggedOut'] = true;
                }
                else
                {
                        $this->conditions['loggedIn']  = true;
                        $this->conditions['loggedOut'] = false;
                }
        }
}

?>
