<?php

// ========================================================================
//
// Language/Language.classes.php
//              Defines the classes provided by the Language component
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
// 2009-07-08   SLH     Broken out from App component
// 2009-07-08	SLH	Added Language_Translations
// 2009-07-15	SLH	Fixes for Language_Translations
// 2009-07-26   SLH     Added Language_Manager::getCurrentLanguageName()
// 2009-08-19   SLH     Added ability to load language files without
//                      having first loaded the module's inc file
// ========================================================================

class Language_Manager extends Obj
{
        /**
	 * The current language for this app ... this will be the same as
	 * the default language unless the user has chosen a different
	 * preference
	 *
         * @var Language_Translations
         */

        public $currentLanguage = null;

        /**
         * The default language for this app ... the one we expect to have
	 * a complete set of translations available
	 *
         * @var Language_Translations
         */
        public $defaultLanguage = null;

        /**
         * A list of all the languages we have translations for
	 *
         * @var array
         */
        protected $languages = array();

	/**
	 * A list of which modules have translations for which languages
	 *
	 * @var array
	 */
	protected $modulesWithTranslations = array();

	public function __construct($defaultLanguage = 'en-us')
	{
		// create a translations object for the default language
		$this->languages[$defaultLanguage] = new Language_Translations($defaultLanguage);

		// setup the default and current languages
		$this->defaultLanguage = $this->languages[$defaultLanguage];
		$this->currentLanguage = $this->languages[$defaultLanguage];
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
                if (!isset($this->languages[$language]))
                {
			// we dare not throw an Exception here, because
			// for all we know we'll run into an exception
			// handler that relies on translations to display
			// an error!
			trigger_error("Language $language is not supported");
                }

                $this->currentLanguage = $this->languages[$language];
        }

        /**
         * Ask the user's browser which language they would like us to use
         */
        public function changeLanguageBasedOnBrowserHint()
        {
                if (function_exists('http_negotiate_language'))
                {
                        $this->changeLanguage(http_negotiate_language(array_keys($this->languages)));
                }
        }

	public function moduleSpeaks ($module, $pathToDir, $language)
	{
		if (!isset($this->languages[$language]))
		{
			$this->languages[$language] = new Language_Translations($language);
		}

                // work out the file we need to include when the time comes
                $includeFile = $pathToDir . '/' . $module . '.lang.' . $language . '.php';
		if (!file_exists($includeFile))
		{
			trigger_error("File $includeFile not found; required for multi-lingual support");
		}

		// tell the language translations object where it can get
		// the translations from when the time comes
		$this->languages[$language]->setPathToTranslations($module, $includeFile);
	}

        protected function autoloadTranslationsForModule($module)
        {
                // we try to load translations from the app first
                // and then look in mf/ if we've had no luck
                if (!$this->findTranslationsForModule($module, APP_LIBDIR . '/' . $module))
                {
                        $this->findTranslationsForModule($module, MF_LIBDIR . '/' . $module);
                }
        }

        protected function findTranslationsForModule($module, $pathToDir)
        {
                App::$debug->info(__METHOD__ . ':: looking in ' . $pathToDir . ' for translations');

                // how many language files can we find?
                $dh = @dir($pathToDir);
                if (!$dh)
                {
                        return false;
                }

                // have we found any translations?
                $found = false;

                while ($file = $dh->read())
                {
                        // App::$debug->info('Looking at file ' . $file);

                        $fileParts = explode('.', $file);
                        if ($fileParts[0] != $module)
                        {
                                // App::$debug->info('Rejected file ' . $file . '; first part not module name');
                                continue;
                        }
                        if ($fileParts[1] != 'lang')
                        {
                                // App::$debug->info('Reject file ' . $file . '; second part not "lang"');
                                continue;
                        }
                        if ($fileParts[3] != 'php')
                        {
                                // App::$debug->info('Reject file ' . $file . '; fourth part not "php"');
                                continue;
                        }
                        if (isset($fileParts[4]))
                        {
                                // App::$debug->info('Reject file ' . $file . '; filename does not end in .php');
                                continue;
                        }

                        // make sure we are loading an actual file
                        if (!is_file($pathToDir . '/' . $file))
                        {
                                // App::$debug->info('Reject file ' . $file . '; not an actual file');
                                continue;
                        }

                        // we have found a language file ...
                        // make a note of it
                        $this->moduleSpeaks($module, $pathToDir, $fileParts[2]);

                        $found = true;
                }

                $dh->close();

                return $found;
        }

        public function addTranslationsForModule($module, $language, $translations)
        {
                constraint_mustBeArray($translations);
		if (!isset($this->languages[$language]))
		{
			$this->languages[$language] = new Language_Translations($language);
		}

                $this->languages[$language]->addTranslations($module, $translations);
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
                // step 1 - do we have a translation for this string?
                if (!$this->currentLanguage->hasTranslationsForModule($module))
                {
                        // we need to autoload the translations
                        $this->autoloadTranslationsForModule($module);
                }

                // step 2 - get the translation from the app's current
                //          language
                $return = $this->currentLanguage->getTranslation($module, $stringName);
                if ($return)
                {
                        return $return;
                }

                // step 3 - check the default language strings if the string
                //          we seek hasn't been translated for the app's
                //          current language
                $return = $this->defaultLanguage->getTranslation($module, $stringName);
                if ($return)
                {
                        return $return;
                }
                
                // step 3 - still no translation? return the token then
                $return = $module . '::' . $stringName;
                return $return;
        }

        public function getCurrentLanguageName()
        {
                return $this->currentLanguage->lang;
        }
}

class Language_Translations extends Obj
{
	/**
	 * Which language are these translations for?
	 * @var string
	 */
	public $lang = null;

	/**
	 * What translations have the modules defined?
	 * @var array
	 */
	public $translations = array();

	public function __construct($lang)
	{
		$this->lang   = $lang;
	}

	public function setPathToTranslations($module, $filename)
	{
		$this->translations[$module] = $filename;
	}

	public function addTranslations($module, $translations)
	{
                constraint_mustBeArray($translations);
                
		if (!is_array($this->translations[$module]))
		{
			$this->translations[$module] = array();
		}
		$this->translations[$module] = array_merge($translations, $this->translations[$module]);
	}

        public function hasTranslationsForModule($module)
        {
                if (!isset($this->translations[$module]))
                {
                        return false;
                }

                return true;
        }

	public function getTranslation($module, $name)
	{
                App::$debug->info("Looking for translation for $module::$name");
                
		// do we know anything about this module?
		if (!$this->hasTranslationsForModule($module))
		{
                        App::$debug->warn("Unknown translation $module");
			// no we do not
                        // bail out

			return false;
		}

		// have we already loaded this module's translation for
		// this language?
		if (is_string($this->translations[$module]))
		{
                        App::$debug->info("Loading translations for $module");
			// no we have not ... time to do so
			@require_once($this->translations[$module]);
		}

		// do we have a translation?
		if (isset($this->translations[$module][$name]))
		{
                        App::$debug->info("Found translation for $module::$name");
			// yes we do :)
			return $this->translations[$module][$name];
		}

                App::$debug->error("Exhausted possibilities for $module::$name");

		// no, we have no translation
		return false;
	}
}

?>
