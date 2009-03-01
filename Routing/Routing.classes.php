<?php

// ========================================================================
//
// components/Routing/Routing.classes.php
//              Classes to support mapping URLs onto modules
//
//              Part of the Modular Framework for PHP applications
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2007 Stuart Herbert
//              All rights reserved
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2007-11-19   SLH     Created
// 2007-12-02   SLH     Renamed Routes to RouteManager
// 2007-12-11   SLH     Added support for conditional routes
// 2008-10-26   SLH     RouteManager is now a static class Routes
//                      to avoid global vars in apps that use it
// 2008-10-26   SLH     Much improved support for parameterised routes
// 2009-03-01   SLH     Routing_Routes is no longer a singleton
// ========================================================================

class Routing_Routes
{
        protected $routes     = array();
        protected $conditions = array();
        protected $map        = array();

        /**
         * define a new route
         */

        public function addRoute($name)
        {
        	$this->routes[$name] = new Routing_Route($name);
                return $this->routes[$name];
        }

        public function getRoute($name)
        {
        	$this->requireValidRouteName($name);

                return $this->routes[$name];
        }

        public function requireValidRouteName($name)
        {
        	if (!isset($this->routes[$name]))
                {
                	throw new Routing_E_NoSuchRoute($name);
                }
        }

        /**
         * called by the Route class, to help us pre-seed the map of
         * available routes
         *
         * NOTE: we do nothing special here to ensure that conditional
         *       routes come earlier in the map than non-conditional
         *       routes.  For any given URL, make sure that you ALWAYS
         *       define your conditional URLs first!
         */

        public function addToMap($url, $oRoute)
        {
                // special case
                if ($url == '/')
                {
                	$this->map['/'][] = $oRoute;
                        return;
                }

        	$parts = explode('/', $url);

                if (empty($parts[0]))
                {
                	array_shift($parts);
                }

                // special case - something like /home.php
                if (empty($parts[1]))
                {
                        $this->map['/'][] = $oRoute;
                }
                else
                {
                        $this->map[$parts[0]][] = $oRoute;
                }
        }

        /**
         * define additional conditions to help us work out which route
         * we want
         */

        public function setConditions($conditions)
        {
        	constraint_mustBeArray($conditions);
                $this->conditions = $conditions;
        }

        public function setCondition($name, $value)
        {
        	$this->conditions[$name] = $value;
        }

        /**
         * called by applications that want to know which route we are
         * looking at
         */

        public function matchUrl($url)
        {
                return $this->findRoute($url);
        }

        private function findRoute($url)
        {
                // var_dump($url);
                // var_dump($this->map);
                
        	// special case - homepage
                if ($url == '/')
                {
                        if (isset($this->map['/']))
                        {
                	       $map = $this->map['/'];
                        }
                        else
                        {
                               // oops - we don't have a route for the homepage
                               throw new Routing_E_NoMatchingRoute($url);
                        }
                }
                else
                {
                        // general case
                        $parts = explode('/', $url);
                        if (empty($parts[0]))
                        {
                                array_shift($parts);
                        }

                        // special case - top level URL
                        if (empty($parts[1]))
                        {
                                $map = $this->map['/'];
                        }
                        else if (isset($this->map[$parts[0]]))
                        {
                                // special case - top-level URLs
                                $map = $this->map[$parts[0]];
                        }
                        else
                        {
                        	throw new Routing_E_NoMatchingRoute($url);
                        }
                }

                // if we get here, we have a map to search through
                foreach ($map as $oRoute)
                {
                	if ($params = $oRoute->matchesUrl($url, $this->conditions))
                        {
                        	return $params;
                        }
                }

                // no matching route found
                throw new Routing_E_NoMatchingRoute($url);
        }

        /**
         * reset the list of stored routes
         *
         * useful for unit testing
         */

        public function resetRoutes()
        {
        	$this->map        = array();
                $this->conditions = array();
                $this->routes     = array();
        }
}

class Routing_Route
{
        protected $routeName     = null;
        protected $routeToClass  = null;
        protected $rawUrl        = null;
        protected $paramRegexs   = array();
        protected $urlParameters = array();
        protected $regex         = null;
        protected $conditions    = array();

        public function __construct($name)
        {
                $this->routeName = $name;
        }

        // ================================================================
        // Methods to define a route in the first place
        // ----------------------------------------------------------------

        /**
         * set the URL that this route applies to
         */

        public function withUrl($url)
        {
        	$this->rawUrl       = $url;
                $this->paramRegexes = null;
                $this->urlRegex     = null;

                // tell our container about us
                App::$routes->addToMap($url, $this);

                return $this;
        }

        public function routeToClass($class = null, $method = null)
        {
                // are we getting this value, or are we setting it?
                if ($class == null)
                {
                	return $this->routeToClass;
                }

                // if we get here, then we are setting instead of getting
        	$this->routeToClass = $class;

                if ($method == null)
                {
                	$this->routeToMethod = $this->routeName;
                }
                else
                {
                        $this->routeToMethod = $method;
                }

                return $this;
        }

        public function routeToClassAndMethod($class, $method)
        {
        	return $this->routeToClass($class, $method);
        }

        public function withParams($aRegexes = array())
        {
        	constraint_mustBeArray($aRegexes);

                foreach ($aRegexes as $param => $regex)
                {
                        // special case ... when we have the param
                        // name as the value in the array, rather than
                        // the key
                        if ($regex[0] == ':')
                        {
                        	$param = $regex;
                                $regex = true;
                        }

                        if ($regex === true)
                        {
                                if ($param[0] == ':')
                                {
                                        $this->urlParameters[$param] = '([^/]+)';
                                }
                                else if ($param[0] == '*')
                                {
                                        $this->urlParameters[$param] = '(.+)';
                                }
                        }
                	else if ($regex[0] != '(')
                        {
                        	$this->urlParameters[$param] = '(' . $regex . ')';
                        }
                        else
                        {
                        	$this->urlParameters[$param] = $regex;
                        }
                }

                return $this;
        }

        /**
         * set the conditions that must match for this route
         */

        public function withConditions($conditions)
        {
                constraint_mustBeArray($conditions);
        	$this->conditions = $conditions;

                return $this;
        }

        protected function analyseUrl()
        {
                // if we have already analysed this URL, do not analyse
                // it again

                if (isset($this->urlRegex))
                        return;

                // step 1: break the module up using the path separator

        	$parts = explode('/', $this->rawUrl);
                if (empty($parts[0]))
                {
                        // the URL started with a '/'
                	array_shift($parts);
                }

                // step 2: by default, the module to handle this URL
                //         is the first part of the path
                //
                // this can be overridden using the routeToModule() method

                if ($this->routeToClass() == null)
                        $this->routeToClass($parts[0]);

                // step 3: does this URL have any parameters, and if so,
                //         what are they?
                //
                // whilst we're at it, we build up the regex that will be
                // used to attempt to match this route

                if (count($this->urlParameters) > 0)
                {
                        $this->urlRegex = ':'
                                        . str_replace(array_keys($this->urlParameters), $this->urlParameters, $this->rawUrl)
                                        . '$:U';
                }
                else
                {
                	$this->urlRegex = ':' . $this->rawUrl . '$:U';
                }
        }

        // ================================================================
        // Methods to generate a URL for this route
        // ----------------------------------------------------------------

        public function toUrl($aParams = array())
        {
                if ($this->urlRegex == null)
                {
                	$this->analyseUrl();
                }

                $this->requireValidParams($aParams);

                return str_replace(array_keys($aParams), array_values($aParams), $this->rawUrl);
        }

        public function requireValidParams($aParams)
        {
        	$missingParams = array();

                foreach ($this->urlParameters as $param => $regex)
                {
                	if (!isset($aParams[$param]))
                        {
                        	$missingParams[] = $param;
                        }
                }

                if (count($missingParams) == 0)
                {
                	return;
                }

                // if we get here, we are missing one or more parameters
                throw new Routing_E_MissingParameters(count($missingParams), implode(',', $missingParams));
        }

        // ================================================================
        // Methods to match this route to a URL

        /**
         * check to see if this route matches a given URL and set of
         * conditions
         */

        public function matchesUrl($url, $conditions)
        {
                // we call analyseUrl() here to ensure that this route
                // object is fully initialised.  we cannot guarantee that
                // it has been called before now

                if ($this->urlRegex == null)
                {
                        $this->analyseUrl();
                }

                // var_dump($this->urlRegex);

                // step 1: does the supplied URL match our route's regex?
        	if (!preg_match($this->urlRegex, $url, $aMatches))
                {
                        // the route doesn't match against our regex
                        return false;
                }

                // var_dump($this->urlRegex);
                // var_dump($url);
                // var_dump($aMatches);

                // step 2: does our set of conditions match against the
                //         general conditions currently in effect?

                foreach ($this->conditions as $name => $value)
                {
                	if (!isset($conditions[$name]) && $value !== null)
                        {
                        	// our condition is not set.
                                return false;
                        }

                        if ($conditions[$name] != $value)
                        {
                                // our condition is not met.
                        	return false;
                        }
                }

                // if we get here, then our route matches both the
                // supplied URL, and also matches the conditions that must
                // be met for our route to be valid

                array_shift($aMatches);

                $aReturn = array();
                foreach ($this->urlParameters as $param => $regex)
                {
                        $aReturn['params'][$param] = $aMatches[0];
                        array_shift($aMatches);
                }

                // add the module to the end
                $aReturn['routeToClass']  = $this->routeToClass;
                $aReturn['routeToMethod'] = $this->routeToMethod;
                $aReturn['routeName']     = $this->routeName;

                return $aReturn;
        }
}

?>