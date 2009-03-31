<?php

// ========================================================================
//
// Theme/Theme.funcs.php
//              Functions defined by the Theme module
//
//              Part of the Methodosity Framework for PHP
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2009 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2009-03-30   SLH     Created
// ========================================================================

function constraint_mustBeTheme($themeName)
{
        // step 1: load the theme if it is not already loaded
        constraint_mustBeString($themeName);
        $theme = new $themeName;

        // step 2: check to see if it has registered itself as a theme
        if (!App::$themes->isRegisteredTheme($themeName))
        {
                throw new PHP_E_ConstraintFailed(__FUNCTION__);
        }
}

?>
