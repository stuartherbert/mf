<?php

// ========================================================================
//
// Routing/Routing.tests.php
//              PHPUnit tests for the Routing component
//
//              Part of the Methodosity Framework for PHP applications
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2007-2009 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2007-11-19   SLH     Created
// 2007-12-11   SLH     Added tests for conditional homepage routes
// 2008-09-09   SLH     Updated tests to cope with new
//                      routeToClassAndMethod() approach
// 2008-10-26   SLH     Routes are now defined using a static class
// 2008-10-26   SLH     Added tests for more complicated parameterised
//                      routes
// 2009-03-18   SLH     Fixed up to use the new task-based approach
// ========================================================================

// bootstrap the framework
define('UNIT_TEST', true);
define('APP_TOPDIR', realpath(dirname(__FILE__) . '/../../'));
require_once(APP_TOPDIR . '/mf/mf.inc.php');

// load additional files we explicitly require
__mf_require_once('Testsuite');

if (!defined('URL_TO_TOPDIR'))
        define('URL_TO_TOPDIR', 'http://www.example.com');

Testsuite_registerTests('Routing_Tests');
class Routing_Tests extends PHPUnit_Framework_TestCase
{
        public function setup()
        {
                // add several routes for testing purposes
                //
                // NOTE: the order that routes are defined in matters
                //       to the Routes manager

                App::$routes->resetRoutes();

                App::$routes->addRoute('userProfile')
                        ->withUrl('/profile/:username')
                        ->withParams(array(':username'));

                App::$routes->addRoute('indexLoggedIn')
                       ->withUrl('/')
                       ->withConditions(array('loggedIn' => true))
                       ->routeToClassAndMethod('Homepage', 'handleIndexLoggedIn');

                App::$routes->addRoute('indexLoggedOut')
                        ->withUrl('/')
                        ->withConditions(array('loggedIn' => false))
                        ->routeToClassAndMethod('Homepage', 'handleIndexLoggedOut');

                App::$routes->addRoute('index')
                        ->withUrl('/')
                        ->routeToClass('Homepage');

                App::$routes->addRoute('showPhoto')
                        ->withUrl('/photos/:username/:photoId/show')
                        ->withParams(array(':username', ':photoId'));

                App::$routes->addRoute('blogArchive')
                        ->withUrl('/archive/:year/:month/:day')
                        ->withParams(array(':year' => '([0-9]{4})', ':month' => '([0-9]{2})', ':day' => '([0-9]{2})'));

                App::$routes->addRoute('blogArchive2')
                        ->withUrl('/archive2/:year/:month/:day')
                        ->withParams(array(':year' => true, ':month' => true, ':day' => true));

                App::$routes->addRoute('api1')
                        ->withUrl('/api/milestone/:id.:format')
                        ->withParams(array(':id' => '([0-9]+)', ':format' => '(xml|php|json)'));

                App::$routes->addRoute('api2')
                        ->withUrl('/api2/milestone/:id.:format')
                        ->withParams(array(':id', ':format' => '(xml|php|json)'));
        }

        public function testCanCreateBasicNamedRoute()
        {
                $indexUrl = App::$routes->getRoute('index')->toUrl();

                $this->assertEquals($indexUrl, '/');
        }

        public function testCanCreateParameterisedRoute()
        {
                $route = App::$routes->getRoute('userProfile');

                $profileUrl = $route->toUrl(array(':username' => 'stuartherbert'));
                $class      = $route->routeToClass();

                $this->assertEquals('/profile/stuartherbert', $profileUrl);
                $this->assertEquals('profile', $class);
        }

        public function testTrapsMissingParameters()
        {
                try
                {
                	$url = App::$routes->getRoute('showPhoto')
                                       ->toUrl(array(':username' => 'stuartherbert'));
                }
                catch (Routing_E_MissingParameters $e)
                {
                	$this->assertTrue(true);
                }
        }

        public function testMatchesHomepageUrl()
        {
                $route = App::$routes->matchUrl('/');
                $this->assertEquals('Homepage', $route->routeToClass);
                $this->assertEquals('index',    $route->routeName);
                $this->assertEquals('index',    $route->routeToMethod);
        }

        public function testMatchesHomepageUrlWithConditions()
        {
        	App::$routes->setConditions(array('loggedIn' => true));

                $route = App::$routes->matchUrl('/');
                $this->assertEquals('Homepage',                 $route->routeToClass);
                $this->assertEquals('indexLoggedIn',            $route->routeName);
                $this->assertEquals('handleIndexLoggedIn',      $route->routeToMethod);

                App::$routes->setConditions(array('loggedIn' => false));

                $route = App::$routes->matchUrl('/');
                $this->assertEquals('Homepage',                 $route->routeToClass);
                $this->assertEquals('indexLoggedOut',           $route->routeName);
                $this->assertEquals('handleIndexLoggedOut',     $route->routeToMethod);
        }

        public function testMatchesParameterisedUrl()
        {
        	$route = App::$routes->matchUrl('/photos/stuartherbert/098adf/show');
                $this->assertEquals('stuartherbert',    $route->matchedParams[':username']);
                $this->assertEquals('098adf',           $route->matchedParams[':photoId']);
                $this->assertEquals('showPhoto',        $route->routeName);
                $this->assertEquals('photos',           $route->routeToClass);
                $this->assertEquals('showPhoto',        $route->routeToMethod);
        }

        public function testUsesRestrictedParameters()
        {
                // step 1 - prove we can match numbers against our
                //          test url

        	$route = App::$routes->matchUrl('/archive/2007/11/19');
                $this->assertEquals('2007', $route->matchedParams[':year']);
                $this->assertEquals('11', $route->matchedParams[':month']);
                $this->assertEquals('19', $route->matchedParams[':day']);

                // step 2 - prove we cannot match non-numbers against our
                //          test url

                $excepted = false;
                try
                {
                	App::$routes->matchUrl('/archive/fred/11/19');
                }
                catch (Routing_E_NoMatchingRoute $e)
                {
                	$excepted = true;
                }
                $this->assertTrue($excepted);

                $excepted = false;
                try
                {
                	App::$routes->matchUrl('/archive/2007/fred/19');
                }
                catch (Routing_E_NoMatchingRoute $e)
                {
                	$excepted = true;
                }
                $this->assertTrue($excepted);

                $excepted = false;
                try
                {
                	App::$routes->matchUrl('/archive/2007/11/fred');
                }
                catch (Routing_E_NoMatchingRoute $e)
                {
                	$excepted = true;
                }
                $this->assertTrue($excepted);
        }

        public function testUsesWholeUrlInParameters()
        {
        	$route = App::$routes->matchUrl('/archive2/2007/11/19');
                $this->assertEquals('2007', $route->matchedParams[':year']);
                $this->assertEquals('11',   $route->matchedParams[':month']);
                $this->assertEquals('19',   $route->matchedParams[':day']);
        }

        public function testUsesMultipleParametersInPathPart()
        {
        	$route = App::$routes->matchUrl('/api/milestone/4.xml');
                $this->assertEquals(4,     $route->matchedParams[':id']);
                $this->assertEquals('xml', $route->matchedParams[':format']);
        }

        public function testCanMixParameterStyles()
        {
        	$route = App::$routes->matchUrl('/api2/milestone/fred.xml');
                $this->assertEquals('fred', $route->matchedParams[':id']);
                $this->assertEquals('xml',  $route->matchedParams[':format']);
        }
}

?>