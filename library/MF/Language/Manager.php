<?php

/**
 * Methodosity Framework
 *
 * LICENSE
 *
 * Copyright (c) 2010 Stuart Herbert
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *  * Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *  * Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in
 *    the documentation and/or other materials provided with the
 *    distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   MF
 * @package    MF_Language
 * @copyright  Copyright (c) 2008-2010 Stuart Herbert.
 * @license    http://www.opensource.org/licenses/bsd-license.php Simplified BSD License
 * @version    0.1
 * @link       http://framework.methodosity.com
 */

__mf_init_module('Language');

class MF_Language_Manager extends MF_Obj
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
		$this->languages[$defaultLanguage] = new MF_Language_Translations($defaultLanguage);

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
			$this->languages[$language] = new MF_Language_Translations($language);
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
                //MF_App::$debug->info(__METHOD__ . ':: looking in ' . $pathToDir . ' for translations');

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
			$this->languages[$language] = new MF_Language_Translations($language);
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

?>