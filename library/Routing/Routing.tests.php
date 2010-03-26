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
// Copyright    (c) 2007-2010 Stuart Herbert
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
// 2009-03-24   SLH     Routes now go to modules and pages instead
// 2009-03-31   SLH     Fixes for BC breakage in Routing classes
// 2009-05-01   SLH     Conditions moved to App::
// 2009-07-09   SLH     Fixes because users are now always logged in or
//                      logged out
// 2009-07-13   SLH     Added tests for routing to an external URL
// 2009-07-24   SLH     Removed tests for shortcut function (which is now
//                      in the XHTML module)
// 2009-07-24   SLH     Routing_Route::toUrl() renamed to expandUrl()
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

                App::$routes->addRoute('index')
                       ->withUrl('/')
                       ->withConditions(array('loggedIn' => true))
                       ->routeToModuleAndPage('Homepage', 'indexLoggedIn');

                App::$routes->addRoute('index')
                        ->withUrl('/')
                        ->withConditions(array('loggedIn' => false))
                        ->routeToModuleAndPage('Homepage', 'indexLoggedOut');

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

                App::$routes->addRoute('externalPage')
                            ->routeToUrl('http://www.example.com/externalPage');

                App::$routes->addRoute('externalPage2')
                            ->routeToUrl('http://www.example.com/externalPage/:year/:month/:day')
                            ->withParams(array(':year' => '[0-9]{4}', ':month' => '[0-9]{2}', ':day' => '[0-9]{2}'));
        }

        public function testCanCreateBasicNamedRoute()
        {
                $indexUrl = App::$routes->findByName('index')->expandUrl();

                $this->assertEquals($indexUrl, '/');
        }

        public function testCanCreateParameterisedRoute()
        {
                $route = App::$routes->findByName('userProfile');

                $profileUrl = $route->expandUrl(array(':username' => 'stuartherbert'));
                $module     = $route->routeToModule();

                $this->assertEquals('/profile/stuartherbert', $profileUrl);
                $this->assertEquals('profile', $module);
        }

        public function testTrapsMissingParameters()
        {
                try
                {
                	$url = App::$routes->findByName('showPhoto')
                                       ->expandUrl(array(':username' => 'stuartherbert'));
                }
                catch (Routing_E_MissingParameters $e)
                {
                	$this->assertTrue(true);
                }
        }

        public function testMatchesHomepageUrl()
        {
                $route = App::$routes->findByUrl('/');
                
                $this->assertEquals('Homepage',       $route->routeToModule);
                $this->assertEquals('index',          $route->routeName);
                $this->assertEquals('indexLoggedOut', $route->routeToPage);
        }

        public function testMatchesHomepageUrlWithConditions()
        {
        	App::$conditions->loggedIn = true;

                $route = App::$routes->findByUrl('/');
                $this->assertEquals('Homepage',      $route->routeToModule);
                $this->assertEquals('index',         $route->routeName);
                $this->assertEquals('indexLoggedIn', $route->routeToPage);

                App::$conditions->loggedIn = false;

                $route = App::$routes->findByUrl('/');
                $this->assertEquals('Homepage',       $route->routeToModule);
                $this->assertEquals('index',          $route->routeName);
                $this->assertEquals('indexLoggedOut', $route->routeToPage);
        }

        public function testMatchesParameterisedUrl()
        {
        	$route = App::$routes->findByUrl('/photos/stuartherbert/098adf/show');
                $this->assertEquals('stuartherbert', $route->matchedParams[':username']);
                $this->assertEquals('098adf',        $route->matchedParams[':photoId']);
                $this->assertEquals('showPhoto',     $route->routeName);
                $this->assertEquals('photos',        $route->routeToModule);
                $this->assertEquals('showPhoto',     $route->routeToPage);
        }

        public function testUsesRestrictedParameters()
        {
                // step 1 - prove we can match numbers against our
                //          test url

        	$route = App::$routes->findByUrl('/archive/2007/11/19');
                $this->assertEquals('2007', $route->matchedParams[':year']);
                $this->assertEquals('11', $route->matchedParams[':month']);
                $this->assertEquals('19', $route->matchedParams[':day']);

                // step 2 - prove we cannot match non-numbers against our
                //          test url

                $excepted = false;
                try
                {
                	App::$routes->findByUrl('/archive/fred/11/19');
                }
                catch (Routing_E_NoMatchingRoute $e)
                {
                	$excepted = true;
                }
                $this->assertTrue($excepted);

                $excepted = false;
                try
                {
                	App::$routes->findByUrl('/archive/2007/fred/19');
                }
                catch (Routing_E_NoMatchingRoute $e)
                {
                	$excepted = true;
                }
                $this->assertTrue($excepted);

                $excepted = false;
                try
                {
                	App::$routes->findByUrl('/archive/2007/11/fred');
                }
                catch (Routing_E_NoMatchingRoute $e)
                {
                	$excepted = true;
                }
                $this->assertTrue($excepted);
        }

        public function testUsesWholeUrlInParameters()
        {
        	$route = App::$routes->findByUrl('/archive2/2007/11/19');
                $this->assertEquals('2007', $route->matchedParams[':year']);
                $this->assertEquals('11',   $route->matchedParams[':month']);
                $this->assertEquals('19',   $route->matchedParams[':day']);
        }

        public function testUsesMultipleParametersInPathPart()
        {
        	$route = App::$routes->findByUrl('/api/milestone/4.xml');
                $this->assertEquals(4,     $route->matchedParams[':id']);
                $this->assertEquals('xml', $route->matchedParams[':format']);
        }

        public function testCanMixParameterStyles()
        {
        	$route = App::$routes->findByUrl('/api2/milestone/fred.xml');
                $this->assertEquals('fred', $route->matchedParams[':id']);
                $this->assertEquals('xml',  $route->matchedParams[':format']);
        }

        public function testCanRouteToExternalPage()
        {
                $route = App::$routes->findByName('externalPage');

                $this->assertFalse($route->isInternal);
                $this->assertEquals(null, $route->routeToModule);
                $this->assertEquals(null, $route->routeToPage);

                $url = $route->toUrl();
                $this->assertEquals('http://www.example.com/externalPage', $url);
        }

        public function testCanRouteToExternalPageWithParams()
        {
                $route = App::$routes->findByName('externalPage2');

                $url = $route->toUrl(array(
                        ':year'  => 2009,
                        ':month' => '07',
                        ':day'   => '02'
                ));
                $this->assertEquals('http://www.example.com/externalPage/2009/07/02', $url);
        }
}

?>