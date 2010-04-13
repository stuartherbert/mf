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

class MF_Language_Translations extends MF_Obj_Extensible
{
	/**
	 * Which language are these translations for?
	 * @var string
	 */
	protected $lang = null;

        /**
         * Which files hold the translations we need?
         */
        protected $translationFiles = array();

	/**
	 * What translations have the modules defined?
	 * @var array
	 */
	protected $translations = array();

        /**
         *
         * @param string $lang locale string for this translation set
         */
	public function __construct($lang)
	{
		$this->lang   = $lang;
	}

        /**
         *
         * @param string $module name of the module these translations are for
         * @param string $filename path to the file containing the translations
         */
	public function setPathToTranslation($module, $filename)
	{
		$this->translationFiles[$module] = $filename;
	}

        /**
         *
         * @param string $module name of the module these translations are for
         * @param array $translations set of translations to load
         */
	public function addTranslations($module, $translations)
	{
                constraint_mustBeString($module);
                constraint_mustBeArray($translations);

		if (!is_array($this->translations[$module]))
		{
			$this->translations[$module] = array();
		}
		$this->translations[$module] = array_merge($translations, $this->translations[$module]);
	}

        /**
         *
         * @param string $module name of the module these translations are for
         * @return boolean true if we have loaded translations for this module
         */
        protected function hasTranslationsForModule($module)
        {
                if (!isset($this->translations[$module]))
                {
                        return false;
                }

                return true;
        }

        /**
         *
         * @param string $module name of the module we want a translation for
         * @param string $name text that we want to translate
         * @return mixed a string if we have a translation; false otherwise
         */
	public function getTranslation($module, $name)
	{
		// do we know anything about this module?
		if (!$this->hasTranslationsForModule($module))
		{
                        // none loaded yet
                        $this->loadTranslationsForModule($module);
		}

		// do we have a translation?
		if (isset($this->translations[$module][$name]))
		{
                        //MF_App::$debug->info("Found translation for $module::$name");
			// yes we do :)
			return $this->translations[$module][$name];
		}

                //MF_App::$debug->error("Exhausted possibilities for $module::$name");

		// no, we have no translation
		return false;
	}

        /**
         * getter; can be used as a fake property e.g. $this->lang
         *
         * @return string locale string for this set of translations
         */
        public function getLang()
        {
                return $this->lang;
        }

        /**
         * getter; can be used as a fake property e.g. $this->translationsCount
         *
         * @return int how many translations we have actually loaded
         */
        public function getTranslationsCount()
        {
                return count($this->translations);
        }

        /**
         * getter; can be used as a fake property e.g. $this->translationPathsCount
         *
         * @return int how many translation files we know about
         */
        public function getTranslationPathsCount()
        {
                return count($this->translationFiles);
        }

        /**
         *
         * @param string $module name of the module we want to load translations for
         */
        protected function loadTranslationsForModule($module)
        {
                $this->requireValidPathForModule($module);
                require($this->translationFiles[$module]);
        }

        /**
         *
         * @param string $module name of the module we wanat to load translations for
         */
        protected function requireValidPathForModule($module)
        {
                if (!isset($this->translationFiles[$module]))
                {
                        throw new MF_Language_E_UnknownModule($module);
                }
                
                constraint_mustBeString($this->translationFiles[$module]);
                if (!file_exists($this->translationFiles[$module]))
                {
                        throw new MF_Language_E_NoSuchTranslation($module, $this->translationFiles[$module]);
                }
        }
}

?>