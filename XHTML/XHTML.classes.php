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
// 2009-07-26   SLH     Added XHTML::tag() and removed XHTML::tag_p()
// 2009-07-26   SLH     XHTML::tag_*() now re-use underlying tag();
//                      added $attr parameters for increased flexibility
// ========================================================================

class XHTML
{
        const DOCTYPE_STRICT = '"-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3c.org/TR/xhtml1/DTD/xhtml1-strict.dtd"';

        /**
         * Takes a string and converts the contents to ensure it is safe
         * to output to a browser
         *
         * @param string $string the string to be escaped
         * @return string the escaped string, suitable for output to a browser
         */
        static public function escapeOutput($string)
        {
                return htmlentities($string, ENT_QUOTES, 'UTF-8', false);
        }

        /**
         * Return a suitably-formated list of error messages for the
         * specified form field
         *
         * @param Messages $messages the messages generated for the form
         * @param string $field the name of the field we want error messages for
         * @return string the XHTML to output to the browser
         */
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
                        $attrs .= ' ' . $attrName . '="' . XHTML::escapeOutput($value) . '"';
                }

                return $attrs;
        }

        /**
         * Get the URL for a named route
         *
         * Although the output is generic (it isn't XHTML-specific at all)
         * this method exists for completeness, so that snippet files
         * get all of their data via XHTML::.  It is hoped that this will
         * make our theme approach easier to work with and maintain in
         * the long run.
         *
         * @param string $name the name of the route
         * @param array $params any parameters that the route needs
         * @return string the URL to use in an <a> tag or a form's action
         *         attribute
         */
        static public function routeUrl($name, $params = array())
        {
                return App::$routes->findByName($name)->toUrl($params);
        }

        /**
         * Returns the XHTML for a closed tag
         *
         * @param string $tag the tag to generate
         * @param array $attr the attributes to add to the tag
         * @param string $content the optional content to show in the tag (assumed to have been escaped already)
         * @return string the XHTML to output to the browser
         */
        static public function tag($tag, $attr = array(), $content = null)
        {
                $attrs = self::expandAttributes($attr);

                $return = '<' . $tag . $attrs;
                if ($content === null)
                {
                        $return .= '/>';
                }
                else
                {
                        $return .= '>' . $content . '</' . $tag . '>';
                }

                return $return;
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

        /**
         * Returns the <html> tag, with the correct language specified
         *
         * @return string the XHTML for the <HTML> tag
         */
        static public function tag_html()
        {
                return self::tag_open('html', array(
                        'xmlns'    => 'http://www.w3.org/1999/xhtml',
                        'xml:lang' => App::$languages->currentLanguageName
                ));
        }

        /**
         * Returns the XHTML for a password field for a form
         *
         * @param string $labelModule which module has the translation for the label?
         * @param string $labelMessage the name of the translation for the label
         * @param string $name the form field name
         * @param string $value the default value of the form field
         * @param array  $labelAttr any additional attributes for the label tag
         * @param array  $inputAttr any additional attributes for the input tag
         * @return string the XHTML to output to the browser
         */
        static function tag_inputPassword($labelModule, $labelMessage, $name, $value, $labelAttr = array(), $inputAttr = array())
        {
                $labelAttr['for']   = $name;

                $inputAttr['type']  = 'password';
                $inputAttr['name']  = $name;
                $inputAttr['value'] = $value;

                // set a default width
                if (!isset($inputAttr['width']))
                {
                        $inputAttr['width'] = 30;
                }

                return self::tag('label', $labelAttr, XHTML::translation($labelModule, $labelMessage))
                     . self::tag('input', $inputAttr);
        }

        /**
         * Returns the XHTML for a submit button for a form
         *
         * @param string $labelModule which module has the translation for the label?
         * @param string $labelMessage the name of the translation for the label
         * @return string the XHTML to output to the browser
         */
        static public function tag_inputSubmit($labelModule, $labelMessage, $attr = array())
        {
                $attr['type']  = 'submit';
                $attr['name']  = 'submit';
                $attr['value'] = XHTML::translation($labelModule, $labelMessage);

                return self::tag('input', $attr);
        }

        /**
         * Returns the XHTML for a text input field for a form
         *
         * @param string $labelModule which module has the translation for the label?
         * @param string $labelMessage the name of the translation for the label
         * @param string $name the name of the form field
         * @param string $value the default value of the field
         * @param int $width the width of the field
         * @return <type> the XHTML to output to the browser
         */
        static public function tag_inputText($labelModule, $labelMessage, $name, $value, $labelAttr = array(), $inputAttr = array())
        {
                $labelAttr['for'] = $name;

                $inputAttr['type'] = 'text';
                $inputAttr['name'] = $name;
                $inputAttr['value'] = $value;

                if (!isset($inputAttr['width']))
                {
                        $inputAttr['width'] = 30;
                }

                return self::tag('label', $labelAttr, XHTML::translation($labelModule, $labelMessage))
                     . self::tag('input', $inputAttr);
        }

        /**
         * Returns the XHTML to open an arbitrary tag
         *
         * @param string $tag the name of the tag
         * @param array $attr any attributes to be added to the tag
         * @return string the XHTML to output to the browser
         */
        static public function tag_open($tag, $attr = array())
        {
                $attrs = self::expandAttributes($attr);
                return '<' . $tag . $attrs . '>';
        }

        /**
         * Returns the XHTML to close an arbitrary tag
         *
         * @param string $tag the name of the tag
         * @return string the XHTML to output to the browser
         */
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
         * @return string the XHTML to send to the browser
         */
        static public function tag_routeLink ($name, $params = array(), $attr = array())
        {
                $route = App::$routes->findByName($name);
                $text  = XHTML::escapeOutput($route->expandLinkText());

                $attr['href'] = $route->toUrl($params);

                return self::tag('a', $attr, $text);
        }

        /**
         * Create the XHTML to link to a named route, but using text that
         * we choose instead of the route's default text
         *
         * @param string $module which module has the translation for this link?
         * @param string $message the name of the translation for this link
         * @param array  $messageParams any parameters for the translation of this link
         * @param string $routeName the name of the route
         * @param array  $params any parameters that the route needs
         * @param array  $attr any additional attributes for the XHTML <a> tag
         * @return string the XHTML to send to the browser
         */
        static public function tag_routeLinkWithText ($module, $message, $messageParams, $routeName, $params = array(), $attr = array())
        {
                $route = App::$routes->findByName($routeName);
                $text  = XHTML::escapeOutput(XHTML::translation($module, $message, $messageParams));

                $attr['href'] = $route->toUrl($params);

                return self::tag('a', $attr, $text);
        }

        /**
         * Get the XHTML <title> tag
         * 
         * @param string $module  where is the translation for this title?
         * @param string $message what is the name of this translation?
         * @param array  $params  any parameters required by the translation
         * @param array  $attr    any additional attribtes to include in the tag
         * @return string the XHTML to send to the browser
         */
        static public function tag_title($module, $message, $params = array(), $attr = array())
        {
                return self::tag('title', $attr, XHTML::translation($module, $message, $params));
        }

        /**
         * Expand a translated string, and escape the output to meet XHTML safety needs
         *
         * @param string $module  where is the translation for this string?
         * @param string $message what is the name of this translation?
         * @param array  $params  any parameters required by the translation
         * @return string the translated string, expanded and suitably escaped for XHTML
         */
        static public function translation($module, $message, $params = array())
        {
                return XHTML::escapeOutput(app_translation($module, $message, $params), ENT_QUOTES, 'UTF-8', false);
        }
}

?>
