<?php

// ========================================================================
//
// App/App.funcs.php
//              Functions defined by the App component
//
//              Part of the Methodosity Framework for PHP applications
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2008-2009 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2008-01-06   SLH     Created
// 2008-02-11   SLH     Now looks in $APP_CONFIG for the language string
// 2009-03-01   SLH     Moved from Languages to App
//                      Now supports App_Language class
// 2009-05-22   SLH     Added m() to assist with handling data validation
//                      errors
// 2009-06-10   SLH     Removed m()
// 2009-06-10   SLH     Added ls() and lf()
// ========================================================================

/**
 * Short-cut to save on typing when retrieving translated format strings
 *
 * @param string $module The name of the module which defines the translation
 * @param string $stringName The name of the translation to retrieve
 * @return string the transation, or $stringName if no translation found
 */
function l($module, $stringName)
{
        return App::$languages->getTranslation($module, $stringName);
}

/**
 * Retrieve a fully-exploded translation
 *
 * @param string $module The name of the module which defines the translation
 * @param string $stringName The name of the translation to retrieve
 * @param array $params The parameters to plug into the translation
 * @return string the translation with parameters plugged in
 */
function ls($module, $stringName, $params = array())
{
        $formatString = App::$languages->getTranslation($module, $stringName);
        return vsprintf($formatString, $params);
}

/**
 * Expand a translated format string previously retrived from l()
 *
 * @param string $formatString
 * @param array $params
 * @return string the translation with the parameters plugged in
 */
function lf($formatString, $params = array())
{
        return vsprintf($formatString, $params);
}

?>