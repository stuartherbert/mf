<?php

// ========================================================================
//
// Themes/Themes.classes.php
//              Classes to support theme engines
//
//              Part of the Modular Framework for PHP applications
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2008 Stuart Herbert
//              All rights reserved
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2008-08-13   SLH     Created
// ========================================================================


class AppTheme
{
	static public function newTheme(AppRequest $oRequest, AppUser $oUser)
        {
        	// step 1: work out which theme to load

                // now that we have our theme, define a global constant
                // for use in snippets
                define('PATH_TO_THEME', APP_TOPDIR . 'themes/' . $oRequest->theme);
                define('URL_TO_THEME',  'themes/' . $oRequest->theme);

        }

        static public function loadTheme($themeName)
        {
        	$themeDir = APP_TOPDIR . 'themes/' . $themeName . '/';

                include($themeDir . $themeName . 'inc.php');
        }
}

class Theme
{

}

?>
