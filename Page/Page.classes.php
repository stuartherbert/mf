<?php

// ========================================================================
//
// Page/Page.classes.php
//              Classes for the Page module
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
// 2009-04-15   SLH     Moved out from App module
// 2009-04-15   SLH     Added Page_Manager
// 2009-04-15   SLH     Added Page_Component
// 2009-04-15   SLH     Added Page_Layout
// 2009-04-15   SLH     Added Page_Block
// 2009-04-15   SLH     Added Page_Content
// 2009-04-15   SLH     Page and Page_Layout are no longer expected to be
//                      able to render themselves
// 2009-05-19   SLH     Fixes for Page_Layout to actually work
// ========================================================================

// ========================================================================

class Page_Manager
{
        /**
         * A list of specialist objects (normally supplied by themes)
         * used to override the default output for a given output format
         *
         * @var array
         */
        protected $formatRenderers = array();

        /**
         * Tell a class that we wish to replace its own renderer with
         * one of our own
         * 
         * @param string $target name of the class we wish to override
         * @param string $format output format we wish to override
         * @param object $renderer the object that will do the rendering
         */
        public function registerFormatRenderer($target, $format, $renderer)
        {
                $this->formatRenderers[$target][$format] = $renderer;
        }

        /**
         * Obtain an object to create the output for a given class and
         * output format combination
         *
         * @param string $target name of the class we're trying to override
         * @param string $format the output format we want to render
         * @return object if successfull, null otherwise
         */
        public function getFormatRenderer($target, $format)
        {
                // do we have a format renderer registered for this combo
                // of target and output format?
                if (isset($this->formatRenderers[$target][$format]))
                {
                        // yes we do ... return it
                        return $this->formatRenderers[$target][$format];
                }

                // no we do not ... return null
                return null;
        }
}

// ========================================================================

/**
 * Base class for all elements of a page that need to know how to
 * render for output
 */

class Page_Component
{
        public function toOutputFormat($format)
        {
                $method = 'to' . ucfirst($format);

                // step 1: do we have an override renderer registered
                //         at all?
                //
                // we support format renderers so that themes can override
                // the default renderer as required (e.g. to insert extra
                // diffs to make alternative page layout possible)
                //
                // it also allows themes to add support for extra output
                // formats beyond the core formats (e.g. iPhone support)

                $renderer = App::$pages->getFormatRenderer(get_class($this), $format);
                if (is_object($renderer) && method_exists($renderer, $method))
                {
                        return $renderer->$method();
                }

                // step 2: does this class have a method defined to render
                //         the requested output format?

                if (method_exists($this, $format))
                {
                        return $this->$method;
                }

                // if we get here, then the output format is not supported
                throw new Page_E_OutputFormatNotSupported($this, $format);
        }
}

// ========================================================================

class Page
{
        /**
         * The title to set in the HTML <title> tag or equiv
         * @var string
         */
        public $title   = null;

        /**
         * The main heading to set on the page
         * @var string
         */
        public $h1      = null;

        /**
         * The tagline to set on the page, if any
         * @var string
         */
        public $tagline = null;

        /**
         * Holds the description of this page's layout
         * @var Page_Layout
         */
        protected $layout  = null;

        /**
         * A list of the sections that this page permits
         * @var array
         */
        protected $validSections = array();
        /**
         * A list of the blocks added to each section
         * @var array
         */
        protected $blocks       = array();

        // ----------------------------------------------------------------
        // init code
        
        public function setDefaultTitlesEtc()
        {
                if (isset(App::$config['page']))
                {
                        foreach (App::$config['page'] as $var => $value)
                        {
                                $this->$var = $value;
                        }
                }
        }

        // ----------------------------------------------------------------
        // layout support

        /**
         *
         * @return Page_Layout
         */
        public function getLayout()
        {
                $this->requireValidLayout();
        	return $this->layout;
        }

        public function setLayout(Page_Layout $layout)
        {
                $this->layout = $layout;
                $this->setValidSections($layout->getSections());
                $layout->addDefaultBlocks($this);
        }

        public function requireValidLayout()
        {
                if (!isset($this->layout))
                {
                        throw new Page_E_NoLayout();
                }
        }

        // ----------------------------------------------------------------
        // page section support

        public function setValidSections($sections)
        {
                constraint_mustBeArray($sections);

                $this->validSections = $sections;
        }

        public function requireValidSection($section)
        {
                if (!isset($this->validSections[$section]))
                {
                        throw new PHP_E_ConstraintFailed(__FUNCTION__);
                }
        }

        public function addBlockToSection($sectionName)
        {
                $this->requireValidSection($sectionName);
                $block = new Page_Block();
                $this->blocks[$sectionName][] = $block;

                return $block;
        }

        public function getBlocks($sectionName)
        {
        	$this->requireValidSection($sectionName);
                return $this->blocks[$sectionName];
        }

        public function outputBlocks($sectionName)
        {
                $blocks = $this->getBlocks($sectionName);

                foreach ($blocks as $block)
                {
                        $this->outputBlock($block);
                }
        }

        protected function outputBlock(Page_Block $block)
        {
                // step 1: create the models in the current scope
                foreach ($block->models as $name => $model)
                {
                        $$name = $model;
                }

                // step 2: load the correct snippet
                $snippetFilename = App::$theme->snippetFile($block->snippet);

                // step 3: execute the snippet
                require($snippetFilename);
        }
}

// ========================================================================

class Page_Layout
{
        /**
         * A list of the valid sections that appear in this page
         * @var <array
         */
        protected $sections = array();

        /**
         * The name of the file to use to render this layout
         * @var string
         */
        public $layoutFile = null;

        public function __construct()
        {
                // do we have a layout file specified?
                // if not, set a sensible default

                if ($this->layoutFile === null)
                {
                        // work out a sensible default
                        $className = get_class($this);
                        $parts     = explode('_', $className);

                        // remove the module name from our list
                        array_shift($parts);

                        // remember the first word we found; we treat it
                        // specially
                        $firstPart = strtolower($parts[0][0]) . substr($parts[0], 1);

                        array_shift($parts);

                        if (count($parts) > 0)
                        {
                                // array_walk($parts, 'ucfirst');
                                foreach ($parts as $key => $value)
                                {
                                        $parts[$key] = ucfirst($value);
                                }
                                
                                $this->layoutFile = $firstPart
                                                  . implode('', $parts);
                        }
                        else
                        {
                                $this->layoutFile = $firstPart;
                        }
                }
        }

        public function addSection($name)
        {
                $this->sections[$name] = $name;
        }

        public function getSections()
        {
                return $this->sections;
        }
}

// ========================================================================

class Page_Block extends Page_Component
{
        /**
         * The block's unique name
         * @var string
         */
        public $name = null;

        /**
         * A list of the different content held within this block
         * @var array
         */
        public $models = array();

        /**
         * Name of the (probably) XHTML snippet to use to render this block
         *
         * @var string
         */
        public $snippet = null;

        public function usingSnippet($snippet)
        {
                constraint_mustBeString($snippet);
                $this->snippet = $snippet;

                // fluid interface
                return $this;
        }

        public function usingModel($name, Model $obj)
        {
                $this->models[$name] = $obj;

                // fluid interface
                return $this;
        }
}

// ========================================================================

/**
 * Base class for all individual pieces of content that can exist.
 *
 * The main function of Page_Content classes is to know how to turn data
 * from underlying models into output displayed on the screen or data
 * to return to an API call
 */

class Page_Content extends Page_Component
{
}

?>