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

class Theme_Engine
{
        /**
         * The name of the theme to use
         * 
         * @var string
         */
        public $theme = null;

        /**
         * The particular page type to load
         * 
         * @var string
         */
        public $pageType = null;

        public function loadTheme($theme)
        {
                constraint_mustBeTheme($theme);
                $this->theme = $theme;
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
}

?>
