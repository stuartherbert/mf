<?php

// ========================================================================
//
// XHTML/XHTML.classes.php
//              Classes defined by the XHTML component
//
//              Part of the Methodosity Framework for PHP
//              http://blog.stuartherbert.com/php/mf/
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
// 2009-07-26   SLH     Created, replaces XHTML.funcs.php
// ========================================================================

class XHTML
{
        const DOCTYPE_STRICT = '"-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3c.org/TR/xhtml1/DTD/xhtml1-strict.dtd"';

        static public function escapeOutput($string)
        {
                return htmlentities($string, ENT_QUOTES, 'UTF-8', false);
        }

        static public function errorMessagesForField(Messages $messages, $field)
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
                        $return .= '<li>' . XHTML::translation($error['module'], $error['message'], $error['params']) . '</li>';
                }

                $return .= '</ul>';

                return $return;
        }

        static public function tag_doctype($doctype = XHTML::DOCTYPE_STRICT)
        {
                return '<!DOCTYPE html PUBLIC ' . $doctype . '>';
        }

        static public function tag_html()
        {
                return '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="' . App::$languages->currentLanguageName . '">';
        }

        static function tag_inputPassword($labelModule, $labelMessage, $name, $value, $width='30')
        {
                return '<label for="' . $name .'">' . XHTML::translation($labelModule, $labelMessage) . '</label>'
                       . '<input type="password" name="' . $name . '" value="' . $value . '" width="' . $width . '"/>';
        }

        static public function tag_inputSubmit($labelModule, $labelMessage)
        {
                return '<input type="submit" name="submit" value="' . XHTML::translation($labelModule, $labelMessage) . '"/>';
        }

        static public function tag_inputText($labelModule, $labelMessage, $name, $value, $width='30')
        {
                return '<label for="'.$name.'">' . XHTML::translation($labelModule, $labelMessage) . '</label>'
                       . '<input type="text" name="' . $name . '" value="' . $value . '" width="' . $width . '"/>';
        }

        /**
         * Create a xhtml hyperlink using our table of routes
         *
         * @param string $name
         * @param array $params
         * @param string $cssClass
         * @return string
         */

        static public function tag_routeLink ($name, $params = array(), $cssClass=null)
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

        static public function routeUrl($name, $params = array())
        {
                return App::$routes->findByName($name)->toUrl($params);
        }

        static public function tag_title($module, $message, $params = array())
        {
                return '<title>' . XHTML::translation($module, $message, $params) . '</title>';
        }

        static public function translation($module, $message, $params = array())
        {
                return XHTML::escapeOutput(app_translation($module, $message, $params), ENT_QUOTES, 'UTF-8', false);
        }
}

?>
