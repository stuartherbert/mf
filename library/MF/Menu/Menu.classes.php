<?php

// ========================================================================
//
// Menu/Menu.classes.php
//              Classes defined by the Menu component
//
//              Part of the Methodosity Framework for PHP applications
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2007-2010 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2007-08-07   SLH     Created
// 2007-11-15   SLH     Renamed from 'MainMenu' to just 'Menu'
// 2009-04-16   SLH     Added Menu_Option
// 2009-05-19   SLH     Fixed bug in Menu_Block::addOption()
// ========================================================================

class Menu_Block extends Page_Block
{
        const ALIGN_LEFT        = 1;
        const ALIGN_CENTER      = 2;
        const ALIGN_RIGHT       = 3;
        
	private $menu = array();

        public function addOption($name)
        {
                $option = new Menu_Option($this, $name);
                $this->menu[$name] = $option;

                return $option;
        }

        public function addSubMenuToOption($name)
        {
                if (!isset($this->menu[$name]))
                {
                	throw new Menu_E_NoSuchOption($name);
                }

        	return $this->menu[$name];
        }

        public function getMenuEntries()
        {
        	return $this->menu;
        }
}

class Menu_Option
{
        /**
         *
         * @var string
         */
        protected $name     = null;

        /**
         *
         * @var string
         */
        protected $translationModule = null;

        /**
         *
         * @var string
         */
        protected $translationName = null;

        /**
         *
         * @var string
         */
        protected $url      = null;

        /**
         *
         * @var Routing_Route
         */
        protected $route    = null;

        /**
         *
         * @var boolean
         */
        protected $selected = false;

        /**
         *
         * @var Menu_Option
         */
        protected $subMenu  = null;

        /**
         *
         * @var integer
         */
        protected $alignment = Menu_Block::ALIGN_LEFT;

        /**
         *
         * @var array
         */
        protected $conditions = array();

        public function __construct($parent, $name)
        {
                constraint_mustBeString($name);

                $this->parent = $parent;
                $this->name   = $name;
        }

        public function withTranslation($module, $name)
        {
                constraint_mustBeString($module);
                constraint_mustBeString($name);

                $this->translationModule = $module;
                $this->translationName   = $name;

                return $this;
        }

        public function withRoute($name, $params = array())
        {
                $route = App::$routes->findByName($name);
                $this->url = $route->toUrl($params);

                return $this;
        }

        public function withUrl($url)
        {
                constraint_mustBeString($url);

                $this->url = $url;
                return $this;
        }

        public function withAlignment($alignment)
        {
                $this->alignment = $alignment;
                return $this;
        }

        public function withConditions($conditions)
        {
                constraint_mustBeArray($conditions);
                $this->conditions = $conditions;

                return $this;
        }
}

?>