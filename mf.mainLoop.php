<?php

// ========================================================================
//
// mf/mf.mainLoop.php
//              Contains the main processing for apps built using MF
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
// 2009-03-02   SLH     Created
// 2009-03-31   SLH     Moved User creation into here
// 2009-05-19   SLH     Use browser type to determine which page to load
// 2009-05-20   SLH     The requested route is now stored in App_Request
// 2009-07-26   SLH     Added debug timing information
// ========================================================================

App::$debug->timer->startEvent('mf.mainLoop', 'mainloop');

// step 1: add support for multiple websites here
// TODO: add support for multiple virtual hosts at some point

// step 2: do we have a returning user?
//
// after this step,
// a) App::$user will be a valid User object (even for anonymous users)
// b) the loggedin & anonymousUser conditions will be set as appropriate
//    within the Routing_Engine

App::$debug->timer->startEvent('Authenticate user', 'mainloop');
App::$users->authenticateUser();
App::$debug->timer->endEvent();

// step 3: what type of browser is the user poking us with?
//
// aka what type of content does the user want us to return?

App::$debug->timer->startEvent('Determine browser', 'mainloop');
App::$browser = App::$browsers->determineBrowser();
App::$debug->timer->endEvent();

// step 3: what page are we trying to look at?
try
{
        // convert the queryString into its individual components
        //
        // this call will create App::$user if one is required!!
        // (we could create App::$user before this call, which would be
        // a little cleaner, but why create a $user (which is expensive,
        // because it involves database access) if we sometimes do not
        // need one?
        //
        // one side-effect of this process is that both our API and
        // website share the same idea of what a user is.  This is probably
        // a good thing
        
        App::$request->currentRoute = App::$routes->findByUrl(App::$request->pathInfo);
}
catch (Routing_E_NoMatchingRoute $e)
{
        header('Status: 404');

        App::$debug->timer->endEvent();

        // for now, throw the exception
        throw $e;
}

// step 4: transfer control to the main loop
//
// TODO: we need to install the correct exception handler for
//       each of our mainLoop types, to ensure the exception is
//       sent back in the right format
//
// TODO: this switch statement goes away when PHP 5.3 comes out

$mainLoop = App::$request->currentRoute->mainLoop;
App::$debug->timer->startEvent($mainLoop . '::preMainLoop(' . App::$request->currentRoute->routeName . ')', $mainLoop);

// call_user_func_array(array(App::$request->currentRoute->mainLoop, 'installExceptionHandler'), array());
call_user_func_array(array($mainLoop, 'preMainLoop'), array(App::$request->currentRoute));
App::$debug->timer->endEvent();

/*
switch (App::$request->currentRoute->mainLoop)
{
        case 'AnonApi':
                // AnonApi::installExceptionHandler();
                AnonApi::preMainLoop(App::$request->currentRoute);
                break;

        case 'Api':
                // Api::installExceptionHandler();
                Api::preMainLoop(App::$request->currentRoute);
                break;

        case 'WebApp':
        default:
                // WebApp::installExceptionHandler();
                WebApp::preMainLoop(App::$request->currentRoute);
                break;
}
*/

// pass control to the controller
try
{
        $page = APP_TOPDIR . '/app/' . App::$request->currentRoute->routeToModule
                . '/' . App::$browser->platform . '-pages/'
                . App::$request->currentRoute->routeToPage . '.page.php';

        App::$debug->timer->startEvent($page, 'page');
        require_once($page);
        App::$debug->timer->endEvent();
}
catch (Exception_Process $e)
{
        // end the page event, and also end the mainloop event
        App::$debug->timer->endEvent();
        App::$debug->timer->endEvent();
        // we pass the exception on
        throw $e;
}
catch (Exception $e)
{
        // we have an error that was not expected
        // we will throw a generic internal server error
        // at this point

        var_dump($e);

        // end the page event, and also end the mainloop event
        App::$debug->timer->endEvent();
        App::$debug->timer->endEvent();

        throw new App_E_InternalServerError($e);
}

// prepare the data for publishing
App::$debug->timer->startEvent(get_class(App::$theme) . '::processResponse()', 'render');
App::$theme->processResponse();
App::$debug->timer->endEvent();

// give the different managers an opportunity to do anything to the
// data before it is published
$mainLoop = App::$request->currentRoute->mainLoop;
App::$debug->timer->startEvent($mainLoop . '::postMainLoop()', $mainLoop);
call_user_func_array(array($mainLoop, 'postMainLoop'), array(App::$request->currentRoute));
App::$debug->timer->endEvent();

/*
switch ($route->mainLoop)
{
        case 'AnonApi':
                AnonApi::postMainLoop($route);
                break;

        case 'Api':
                Api::postMainLoop($route);
                break;

        case 'WebApp':
        default:
                WebApp::postMainLoop($route);
                break;
}
*/

// render the final work
App::$debug->timer->startEvent(get_class(App::$theme) . '::render()', 'render');
App::$theme->render();
App::$debug->timer->endEvent();

// final debugging summary
App::$debug->timer->summary();
?>