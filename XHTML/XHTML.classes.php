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
// 2009-07-26   SLH     Added XHTML::tag_p()
// 2009-07-26   SLH     Added XHTML::expandAttributes()
// 2009-07-26   SLH     Added XHTML::tag_routeLinkWithText()
// 2009-07-26   SLH     Added XHTML::tag_open() and XHTML::tag_close()
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

        /**
         * Convert a list of XHTML attributes into something that can be
         * easily published in an XHTML tag
         * 
         * @param array $attr a list of the attributes to expand
         * @return string the expanded attributes ready to publish in a tag
         */
        static public function expandAttributes($attr)
        {
                $attrs  = '';

                foreach ($attr as $attrName => $value)
                {
                        $attrs .= '  ' . $attrName . '="' . $value . '"';
                }

                return $attrs;
        }

        /**
         * Create the DOCTYPE instruction to tell the browser what type
         * of content this XHTML page contains
         *
         * @see XHTML::DOCTYPE_STRICT
         *
         * @param string $doctype the doctype to use
         * @return string the DOCTYPE instruction to publish
         */
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

        static public function tag_p($module, $name, $params = array(), $attr = array())
        {
                $attrs = self::expandAttributes($attr);
                return '<p' . $attrs . '>' . XHTML::translation($module, $name, $params) . '</p>';
        }

        static public function tag_open($tag, $attr = array())
        {
                $attrs = self::expandAttributes($attr);
                return '<' . $tag . $attrs . '>';
        }

        static public function tag_close($tag)
        {
                return '</' . $tag . '>';
        }
        
        /**
         * Create a xhtml a tag using our table of routes
         *
         * @param string $name the name of the route to link to
         * @param array $params any parameters required by the route
         * @param array $attr any additional attributes to add to the tag
         * @return string
         */

        static public function tag_routeLink ($name, $params = array(), $attr = array())
        {
                $attrs = self::expandAttributes($attr);
                $route = App::$routes->findByName($name);

                $return = '<a href="'
                        . $route->toUrl($params)
                        . '"'
                        . $attrs
                        . ">" . XHTML::escapeOutput($route->expandLinkText()) . "</a>";

                return $return;
        }

        static public function tag_routeLinkWithText ($module, $message, $messageParams, $routeName, $params = array(), $attr = array())
        {
                $attrs = self::expandAttributes($attr);
                $route = App::$routes->findByName($routeName);
                $text  = XHTML::escapeOutput(XHTML::translation($module, $message, $messageParams));

                $return = '<a href="'
                        . $route->toUrl($params)
                        . '"'
                        . $attrs
                        . ">" . $text . "</a>";

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
