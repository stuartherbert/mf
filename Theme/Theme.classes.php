<?php

// ========================================================================
//
// Theme/Theme.classes.php
//              The core theme engine support
//
//              Part of the Methodosity Framework for PHP applications
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2008-2009 Stuart Herbert
//              All rights reserved
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2008-08-13   SLH     Created
// 2009-03-30   SLH     Substantial improvements
// 2009-04-15   SLH     Added support for Page_Layouts
// ========================================================================

class Theme_Manager
{
        /**
         * A list of the themes that have been successfully loaded
         *
         * In reality, this list will probably only ever contain one
         * theme at a time, because we do not try to load the theme until
         * we know what theme we want to load!
         * 
         * @var array
         */
        public $registeredThemes = array();

        public function setTheme($theme)
        {
                constraint_mustBeTheme($theme);
                App::$theme = new $theme;
        }

        public function registerTheme($theme, $themeDir)
        {
                $this->registeredThemes[$theme] = $themeDir;
        }

        public function isRegisteredTheme($theme)
        {
                if (isset($this->registeredThemes[$theme]))
                {
                        return true;
                }

                return false;
        }
}

class Theme_BaseTheme
{
        /**
         * The directory where the theme is stored
         *
         * @var string
         */
        protected $themeDir = null;

        /**
         * The layout we want to use
         *
         * @var string
         */
        protected $layout   = null;

        public function __construct($themeDir)
        {
                $this->themeDir = $themeDir;
        }
        
        /**
         * The name of the page template to display
         *
         * @param string $pageType
         */
        public function setPageType($pageType)
        {
                constraint_mustBeString($pageType);
                $this->pageType = $pageType;
        }

        /**
         * work through 
         */
        public function processResponse()
        {
                // work out where the layout is on disk
                $layout = App::$response->page->getLayout();
                $layoutFile = $this->layoutFile($layout->layoutFile);

                // now, execute the layout, and buffer its output
                // to help with caching in future
                // ob_start();
                require_once($layoutFile);
        }

        public function render()
        {
                // ob_end_flush();
        }

        public function possiblePartialFilenames($name, $suffix)
        {
                // check the list of possible files
                //
                // we check the following:
                //
                // a) a file specific to this version of the browser
                //    (e.g. ie7)
                // b) a file specific to this type of browser
                //    (e.g. ie)
                // c) a default file to fall back on

                $possibles = array (
                        $this->themeDir . '/' . App::$browser->platform . '/' . $name . '.' . App::$browser->name . '.' . App::$browser->version . '.' . $suffix . '.php',
                        $this->themeDir . '/' . App::$browser->platform . '/' . $name . '.' . App::$browser->name . '.' . $suffix . '.php',
                        $this->themeDir . '/' . App::$browser->platform . '/' . $name . '.' . $suffix . '.php'
                );

                return $possibles;
        }

        public function partialFilename($name, $suffix)
        {
                $possibles = $this->possiblePartialFilenames($name, $suffix);

                foreach ($possibles as $filename)
                {
                        if (file_exists($filename))
                        {
                                return $filename;
                        }
                }

                return null;
        }

        public function requireValidLayout($layout)
        {
                // this will do the job
                $this->layoutFile($layout);
        }
        
        public function layoutFile($layout)
        {
                // this will get called at least twice, so well worth
                // caching the results?
                static $layouts = array();

                if (!isset($layouts[$layout]))
                {
                        $layouts[$layout] = $this->partialFilename($layout, 'layout');
                }

                if (!isset($layouts[$layout]))
                {
                        throw new Theme_E_NoSuchLayout($layout);
                }

                return $layouts[$layout];
        }

        public function snippetFile($snippet)
        {
                $filename = $this->partialFilename($snippet, 'snippet');
                if ($filename === null)
                {
                        throw new Theme_E_NoSuchSnippet($snippet);
                }

                return $filename;
        }
}

?>
