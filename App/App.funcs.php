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
// ========================================================================

/**
 * Short-cut to save on typing when working with translated strings
 *
 * @param string $module The name of the module which defines the translation
 * @param string $stringName The name of the translation to retrieve
 * @return string the transation, or $stringName if no translation found
 */
function l($module, $stringName)
{
        return App::$languages->getTranslation($module, $stringName);
}

function m($module, $messages = array())
{
        if (count($messages) == 0)
        {
                return;
        }

        foreach ($messages as $message)
        {
                if (strstr($message, 0, 2) == 'M_')
                {
                        App::$response->messages->addMessage(l($module, $message));
                }
                else
                {
                        App::$response->messages->addError(l($module, $error));
                }
        }
}

?>