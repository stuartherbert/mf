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
                // make sure we have a layout to use
                if (App::$response->page->getLayout() == null)
                {
                        throw new Theme_E_NoLayoutSet();
                }

                // work out where the layout is on disk
                $layoutFile = $this->layoutFile(App::$response->page->getLayout());

                // now, execute the layout, and buffer its output
                // to help with caching in future
                ob_start();
                require_once($layoutFile);
        }

        public function render()
        {
                ob_end_flush();
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
                        // check the list of possible files
                        //
                        // we check the following:
                        //
                        // a) a layout specific to this version of the browser
                        //    (e.g. ie7)
                        // b) a layout specific to this type of browser
                        //    (e.g. ie)
                        // c) a default layout to fall back on

                        $possibles = array (
                                $this->themeDir . '/' . App::$browser->platform . '/' . $layout . '.' . App::$browser->name . App::$browser->version . '.layout.php',
                                $this->themeDir . '/' . App::$browser->platform . '/' . $layout . '.' . App::$browser->name . '.layout.php',
                                $this->themeDir . '/' . App::$browser->platform . '/' . $layout . '.layout.php'
                        );

                        foreach ($possibles as $layout)
                        {
                                if (file_exists($layout))
                                {
                                        $layouts[$layout] = $layout;
                                }
                        }
                }

                if (!isset($layouts[$layout]))
                {
                        throw new Theme_E_NoSuchLayout($layout);
                }

                return $layouts[$layout];
        }
}

?>
