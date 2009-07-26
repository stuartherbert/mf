<?php

// ========================================================================
//
// XHTML/XHTML.funcs.php
//              Helper functions defined by the XHTML component
//
//              Part of the Methodosity Framework for PHP applications
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
// 2009-05-20   SLH     Created
// 2009-06-10   SLH     Added xhtml_errorMessagesForField()
// 2009-07-24   SLH     All functions now require language strings to
//                      ensure multilingual support
// 2009-07-26   SLH     Added xhtml_doctype() and XHTML_STRICT constant
// ========================================================================

define('XHTML_STRICT', 1);
function xhtml_doctype($doctype = XHTML_STRICT)
{
        return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3c.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
}

function xhtml_errorMessagesForField(Messages $messages, $field)
{
        // do we have any errors to display?
        $errors = $messages->getErrorsForField($field);
        if (count($errors) === 0)
        {
                // no messages for this field
                return;
        }

        // if we get here, then we have errors to display

        $return = '<ul class="mfErrors">';
        foreach ($errors as $error)
        {
                $return .= '<li>' . xhtml_translation($error['module'], $error['message'], $error['params']) . '</li>';
        }

        $return .= '</ul>';

        return $return;
}

function xhtml_inputPassword($labelModule, $labelMessage, $name, $value, $width='30')
{
        return '<label for="' . $name .'">' . xhtml_translation($labelModule, $labelMessage) . '</label>'
               . '<input type="password" name="' . $name . '" value="' . $value . '" width="' . $width . '"/>';
}

function xhtml_inputSubmit($labelModule, $labelMessage)
{
        return '<input type="submit" name="submit" value="' . xhtml_translation($labelModule, $labelMessage) . '"/>';
}

function xhtml_inputText($labelModule, $labelMessage, $name, $value, $width='30')
{
        return '<label for="'.$name.'">' . xhtml_translation($labelModule, $labelMessage) . '</label>'
               . '<input type="text" name="' . $name . '" value="' . $value . '" width="' . $width . '"/>';
}

function xhtml_translation($module, $message, $params = array())
{
        return htmlentities(app_translation($module, $message, $params), ENT_QUOTES, 'UTF-8', false);
}

/**
 * Create a xhtml hyperlink using our table of routes
 *
 * @param string $name
 * @param array $params
 * @param string $cssClass
 * @return string
 */

function xhtml_routeLink ($name, $params = array(), $cssClass=null)
{
        $route = App::$routes->findByName($name);

        $return = '<a href="'
                . $route->toUrl($params)
                . '"';

        if ($cssClass != null)
        {
                $return .= ' class="' . $cssClass . '"';
        }

        $return .= ">" . $route->expandLinkText() . "</a>";

        return $return;
}

function xhtml_routeUrl($name, $params = array())
{
        return App::$routes->findByName($name)->toUrl($params);
}

function xhtml_title($module, $message, $params = array())
{
        return '<title>' . xhtml_translation($module, $message, $params) . '</title>';
}

?>