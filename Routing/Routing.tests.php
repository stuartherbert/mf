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
// ========================================================================

require_once ('PHPUnit/Framework/TestCase.php');

if (!defined('URL_TO_TOPDIR'))
        define('URL_TO_TOPDIR', 'http://www.example.com');

class Routing_Tests extends PHPUnit_Framework_TestCase
{
        public function setup()
        {
                // add several routes for testing purposes
                //
                // NOTE: the order that routes are defined in matters
                //       to the Routes manager

                Routes::resetRoutes();

                Routes::addRoute('userProfile')
                        ->withUrl('/profile/:username')
                        ->withParams(array(':username'));

                Routes::addRoute('indexLoggedIn')
                       ->withUrl('/')
                       ->withConditions(array('loggedIn' => true))
                       ->routeToClassAndMethod('Homepage', 'handleIndexLoggedIn');

                Routes::addRoute('indexLoggedOut')
                        ->withUrl('/')
                        ->withConditions(array('loggedIn' => false))
                        ->routeToClassAndMethod('Homepage', 'handleIndexLoggedOut');

                Routes::addRoute('index')
                        ->withUrl('/')
                        ->routeToClass('Homepage');

                Routes::addRoute('showPhoto')
                        ->withUrl('/photos/:username/:photoId/show')
                        ->withParams(array(':username', ':photoId'));

                Routes::addRoute('blogArchive')
                        ->withUrl('/archive/:year/:month/:day')
                        ->withParams(array(':year' => '([0-9]{4})', ':month' => '([0-9]{2})', ':day' => '([0-9]{2})'));

                Routes::addRoute('blogArchive2')
                        ->withUrl('/archive2/:year/:month/:day')
                        ->withParams(array(':year' => true, ':month' => true, ':day' => true));

                Routes::addRoute('api1')
                        ->withUrl('/api/milestone/:id.:format')
                        ->withParams(array(':id' => '([0-9]+)', ':format' => '(xml|php|json)'));

                Routes::addRoute('api2')
                        ->withUrl('/api2/milestone/:id.:format')
                        ->withParams(array(':id', ':format' => '(xml|php|json)'));
        }

        public function testCanCreateBasicNamedRoute()
        {
                $indexUrl = Routes::getRoute('index')->toUrl();

                $this->assertEquals($indexUrl, '/');
        }

        public function testCanCreateParameterisedRoute()
        {
                $route = Routes::getRoute('userProfile');

                $profileUrl = $route->toUrl(array(':username' => 'stuartherbert'));
                $class      = $route->routeToClass();

                $this->assertEquals('/profile/stuartherbert', $profileUrl);
                $this->assertEquals('profile', $class);
        }

        public function testTrapsMissingParameters()
        {
                try
                {
                	$url = Routes::getRoute('showPhoto')
                                       ->toUrl(array(':username' => 'stuartherbert'));
                }
                catch (Routing_E_MissingParameters $e)
                {
                	$this->assertTrue(true);
                }
        }

        public function testMatchesHomepageUrl()
        {
                $params = Routes::matchUrl('/');
                $this->assertEquals('Homepage', $params['routeToClass']);
                $this->assertEquals('index',    $params['routeName']);
                $this->assertEquals('index',    $params['routeToMethod']);
        }

        public function testMatchesHomepageUrlWithConditions()
        {
        	Routes::setConditions(array('loggedIn' => true));

                $params = Routes::matchUrl('/');
                $this->assertEquals('Homepage',                 $params['routeToClass']);
                $this->assertEquals('indexLoggedIn',            $params['routeName']);
                $this->assertEquals('handleIndexLoggedIn',      $params['routeToMethod']);

                Routes::setConditions(array('loggedIn' => false));

                $params = Routes::matchUrl('/');
                $this->assertEquals('Homepage',                 $params['routeToClass']);
                $this->assertEquals('indexLoggedOut',           $params['routeName']);
                $this->assertEquals('handleIndexLoggedOut',     $params['routeToMethod']);
        }

        public function testMatchesParameterisedUrl()
        {
        	$params = Routes::matchUrl('/photos/stuartherbert/098adf/show');
                $this->assertEquals('stuartherbert',    $params[':username']);
                $this->assertEquals('098adf',           $params[':photoId']);
                $this->assertEquals('showPhoto',        $params['routeName']);
                $this->assertEquals('photos',           $params['routeToClass']);
                $this->assertEquals('showPhoto',        $params['routeToMethod']);
        }

        public function testUsesRestrictedParameters()
        {
                // step 1 - prove we can match numbers against our
                //          test url

        	$params = Routes::matchUrl('/archive/2007/11/19');
                $this->assertEquals('2007', $params[':year']);
                $this->assertEquals('11', $params[':month']);
                $this->assertEquals('19', $params[':day']);

                // step 2 - prove we cannot match non-numbers against our
                //          test url

                $excepted = false;
                try
                {
                	Routes::matchUrl('/archive/fred/11/19');
                }
                catch (Routing_E_NoMatchingRoute $e)
                {
                	$excepted = true;
                }
                $this->assertTrue($excepted);

                $excepted = false;
                try
                {
                	Routes::matchUrl('/archive/2007/fred/19');
                }
                catch (Routing_E_NoMatchingRoute $e)
                {
                	$excepted = true;
                }
                $this->assertTrue($excepted);

                $excepted = false;
                try
                {
                	Routes::matchUrl('/archive/2007/11/fred');
                }
                catch (Routing_E_NoMatchingRoute $e)
                {
                	$excepted = true;
                }
                $this->assertTrue($excepted);
        }

        public function testUsesWholeUrlInParameters()
        {
        	$params = Routes::matchUrl('/archive2/2007/11/19');
                $this->assertEquals('2007', $params[':year']);
                $this->assertEquals('11',   $params[':month']);
                $this->assertEquals('19',   $params[':day']);
        }

        public function testUsesMultipleParametersInPathPart()
        {
        	$params = Routes::matchUrl('/api/milestone/4.xml');
                $this->assertEquals(4,     $params[':id']);
                $this->assertEquals('xml', $params[':format']);
        }

        public function testCanMixParameterStyles()
        {
        	$params = Routes::matchUrl('/api2/milestone/fred.xml');
                $this->assertEquals('fred', $params[':id']);
                $this->assertEquals('xml',  $params[':format']);
        }
}

?>