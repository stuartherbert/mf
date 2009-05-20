<?php

// ========================================================================
//
// components/Routing/Routing.classes.php
//              Classes to support mapping URLs onto modules
//
//              Part of the Methodosity Framework for PHP applications
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
// 2009-03-02   SLH     Routes now support different main loops
// 2009-03-24   SLH     Routing now supports modules and pages
// 2009-03-30   SLH     Renamed Routing_Routes to Routing_Engine
// 2009-03-31   SLH     Moved user creation out into the main loop
// 2009-03-31   SLH     Renamed Routing_Engine to Routing_Manager
// 2009-03-31   SLH     Routing_Manager::findUrl() renamed to findByUrl()
// 2009-03-31   SLH     Routing_Manager::getRoute() renamed to findByName()
// 2009-04-01   SLH     Now supports publishing absolute URLs
// 2009-04-16   SLH     Promoted conditions up to be part of App
// 2009-05-19   SLH     Added support for caching routes (experimental)
// ========================================================================

class Routing_Manager implements DimensionCache_PublicCacheable
{
        protected $routes     = array();
        protected $map        = array();

        protected $defaultMainLoop = 'WebApp';

        /**
         * A list of the files we load routes from
         *
         * @var array
         */

        protected $routeFiles = array();

        protected $cache = null;

        public function __construct()
        {
                $this->cache = new DimensionCache_FileCache('routes');
        }

        /**
         * define a new route
         */

        public function addRoute($name)
        {
        	$this->routes[$name] = new Routing_Route($name, $this->defaultMainLoop);
                return $this->routes[$name];
        }

        /**
         *
         * @param string $name
         * @return Routing_Route
         */
        public function findByName($name)
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
         * Sets the mainLoop that all subsequent new routes will use.
         *
         * Created mainly as a time saver.  Can be overrided by calling
         * the route's withMainLoop() method.  Does not affect any routes
         * created before setDefaultMainLoop() is called.
         *
         * @param string $mainLoop which class's mainLoop() method to
         *               call if this route is matched
         */
        public function setDefaultMainLoop($mainLoop)
        {
                constraint_mustBeString($mainLoop);

                $this->defaultMainLoop = $mainLoop;
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
         * called by applications that want to know which route we are
         * looking at
         */

        public function findByUrl($url)
        {
                $routes = $this->findRoutes($url);
                return $this->filterRoutesMatchingConditions($routes);
        }

        private function findRoutes($url)
        {
                // var_dump($url);
                // var_dump($this->map);

                $return = array();

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
                	if ($oRoute->matchesUrl($url))
                        {
                                // this route matches the URL
                                //
                                // it may not match the required conditions,
                                // but at this stage in the execution plan,
                                // it is too soon to work out whether all
                                // of the required conditions are matched
                                // or not
                        	$return[] = $oRoute;
                        }
                }

                // have we found any matching routes?
                if (count($return) == 0)
                {
                        // no matching route found
                        throw new Routing_E_NoMatchingRoute($url);
                }

                // yes - we have matching routes
                return $return;
        }

        private function filterRoutesMatchingConditions($routes)
        {
                // the first match wins
                foreach ($routes as $route)
                {
                        if ($route->matchesConditions(App::$conditions))
                        {
                                return $route;
                        }
                }

                // if we get here, then we have no matches
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
                $this->routes     = array();
        }

        public function toUrl($route, $params = array())
        {
                $route = $this->findByName($route);
                return App::$request->baseUrl . $route->toUrl($params);
        }

        // ================================================================
        //
        // Support for caching loaded routes

        public function addRoutesFile($file)
        {
                $this->routesFiles[] = $file;
        }

        public function cacheRoutes()
        {
                $this->cache->loadOrRefreshCache($this, $this->routesFiles);
        }

        public function loadFromOriginalSources()
        {
                foreach ($this->routesFiles as $routeFile)
                {
                        require_once($routeFile);
                }
        }
        
        public function loadFromPublicCache()
        {
                $contents = $this->cache->loadCache();

                $cache = unserialize($contents);
                $this->routes = $cache['routes'];
                $this->map    = $cache['map'];
        }

        public function saveToPublicCache()
        {
                $contents = array ('routes' => $this->routes, 'map' => $this->map);

                $this->cache->saveCache(serialize($contents));
        }
}

class Routing_Route
{
        public $routeToModule = null;
        public $routeToPage   = null;
        public $mainLoop      = null;
        public $matchedParams = array();
        public $routeName     = null;
        
        protected $rawUrl        = null;
        protected $paramRegexs   = array();
        protected $urlParameters = array();
        protected $regex         = null;
        protected $conditions    = array();

        public function __construct($name, $mainLoop = 'WebApp')
        {
                $this->routeName   = $name;
                $this->mainLoop    = $mainLoop;
                $this->routeToPage = $name;
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

        public function routeToModule($module = null, $page = null)
        {
                // are we getting this value, or are we setting it?
                if ($module == null)
                {
                	return $this->routeToModule;
                }

                // if we get here, then we are setting instead of getting
        	$this->routeToModule = $module;

                if ($page != null)
                {
                	$this->routeToPage = $page;
                }

                return $this;
        }

        public function routeToModuleAndPage($class, $method)
        {
        	return $this->routeToModule($class, $method);
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

        public function withMainLoop($mainLoop)
        {
                constraint_mustBeString($mainLoop);

                $this->mainLoop = $mainLoop;
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

                if ($this->routeToModule == null)
                        $this->routeToModule = $parts[0];

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

        public function matchesUrl($url)
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

                // if we get here, then our route matches both the
                // supplied URL, and also matches the conditions that must
                // be met for our route to be valid

                // reset matchedParams() just in case we've been called
                // at some point in the past
                //
                // I do not like the way matchesUrl() changes the state
                // of these objects, but cannot think of a better solution
                // right now

                $this->matchedParams = array();

                array_shift($aMatches);

                foreach ($this->urlParameters as $param => $regex)
                {
                        $this->matchedParams[$param] = $aMatches[0];
                        array_shift($aMatches);
                }

                // add the module to the end
                return true;
        }

        /**
         *
         * @param array $conditions
         * @return boolean
         */
        public function matchesConditions($conditions)
        {
                // does our set of conditions match against the
                // general conditions currently in effect?

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

                return true;
        }
}

?>