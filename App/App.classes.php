<?php

// ========================================================================
//
// App/App.classes.php
//              Defines the classes used to support the pipeline approach
//
//              Part of the Modular Framework for PHP Applications
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
// ========================================================================

class App
{
        public static $request    = null;
        public static $response   = null;
        public static $user       = null;
        public static $theme      = null;
        public static $languages  = null;
        public static $routes     = null;
        public static $config     = array();

        // cannot be instantiated
	private function __construct()
        {

        }

        /**
         * setup the things that every app has
         *
         * it is up to the mainLoop() of the different types of app
         * to setup the rest (user, controller, and theme)
         */
        
        public static function init()
        {
                self::$request    = new App_Request();
                self::$response   = new App_Response();
                self::$languages  = new App_Languages();
                self::$routes     = new Routing_Routes();
        }

        // ----------------------------------------------------------------
        // Handle creating controllers

        public static function newController()
        {
                // setup any required conditions
                if (self::$user->authenticated)
                {
                        Routing_Routes::setCondition('loggedIn', true);
                }
                else
                {
                        Routing_Routes::setCondition('anonymousUser', true);
                }

                // convert the queryString into its individual components
                $params = Routing_Routes::matchUrl(self::$request->pathInfo);

                var_dump($params);

                // load the controller
        }

        public static function newTheme()
        {
                constraint_mustBeUser(self::$user);

                $theme = self::$user->theme;
                if ($theme === null)
                {
                        $theme = DEFAULT_THEME;
                }

                return $theme;
        }
}

// ========================================================================

class App_Request
{
        // holds the path the user has requested, which we later
        // decode to determine which class to route the request to
        public $pathInfo     = null;

        public function __construct($pathInfo = null)
        {
                if ($pathInfo === null)
                {
                	$pathInfo = $this->determinePathInfo();
                }

                $this->pathInfo = $pathInfo;
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
}

// ========================================================================

class App_Response
{
	public $responseCode = 200;
        public $messages     = null;
        public $page         = null;

        public function __construct()
        {
        	$this->messages = new App_Messages();
        	$this->page     = new App_Page();
        }
}

// ========================================================================

class App_Page
{
        public $title = null;
        public $h1    = null;

        protected $layout        = null;
        protected $aLinks        = array();
        protected $aBlocks       = array();

        public function getLayout()
        {
        	return $this->layout;
        }

        public function setLayout($layoutName)
        {
                constraint_mustBeLayout($layoutName);
                $this->layout = $layoutName;
        }

        public function addBlock($blockName, $oBlock)
        {
                // constraint_mustBeValidBlock($blockName);
                $this->aBlocks[$blockName] = $oBlock;
        }

        public function getBlock($blockName)
        {
        	$this->requireValidBlock($blockName);
                return $this->aBlocks[$blockName];
        }

        public function requireValidBlock($name)
        {
        	if (!isset($this->aBlocks[$name]))
                {
                	throw new Exception();
                }
        }

        public function addLink($name, $url)
        {
        	$this->aLinks[$name] = $url;
        }

        public function getLink($name)
        {
        	$this->requireValidLink($name);
                return $this->aLinks[$name];
        }

        public function requireValidLink($name)
        {
        	if (!isset($this->aLinks[$name]))
                {
                        // FIXME: replace this with a proper exception
                	throw new Exception();
                }
        }
}

// ========================================================================

class App_Messages
{
        protected static $messages     = array();
        protected static $errorCount   = 0;

        public static function addMessage($message)
        {
                // special case - do not add empty messages
                if ($message === null || strlen($message) == 0)
                        return;

                self::$messages[] = array
                (
                        'class' => 'message',
                        'msg'   => $message,
                );
        }

        public static function addError($message)
        {
                // special case - do not add empty messages
                if ($message === null || strlen($message) == 0)
                {
                        return;
                }

                self::$messages[] = array
                (
                        'class' => 'error',
                        'msg'   => $message,
                );

                self::$errorCount++;
        }

        public static function toXhtml()
        {
                $return = '';

                if (count(self::$messages) == 0)
                {
                        return $return;
                }

                if (self::getErrorCount() > 0)
                {
                	$return .= '<p class="formInstructions">'
                                .  l('Pipeline', 'LANG_RENDER_MESSAGES_ERROR_INSTRUCTIONS')
                                . '</p>';
                }

                $return .= '<ul class="formMessages">';

                foreach (self::$messages as $message)
                {
                        $return .= '<li class="' . $message['class'] . '">'
                                . $message['msg']
                                . "</li>\n";
                }

                $return .= "</ul>\n";

                return $return;
        }

        public static function getCount()
        {
                return count(self::$messages);
        }

        public static function getErrorCount()
        {
                return self::$errorCount;
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
                $this->translations[$module][$language] = true;
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

?>