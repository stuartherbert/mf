<?php

// ========================================================================
//
// Events/Events.classes.php
//              Classes defined by the Events component
//
//              Part of the Methodosity Framework for PHP applications
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
// 2009-05-24   SLH     Created
// 2009-05-24   SLH     Added Events_Manager::destroy() to make the code
//                      testable
// ========================================================================

class Events_Manager
{
        static protected $listeners = array();

        const TYPE_OBJECT = 1;
        const TYPE_STATIC = 2;

        static public function destroy()
        {
                self::$listeners = array();
        }
        
        static protected function objectListensToEvents(Events_Listener $obj)
        {
                $reflectionObj = new ReflectionObject($obj);
                $methods = $reflectionObj->getMethods(ReflectionMethod::IS_PUBLIC);

                foreach ($methods as $method)
                {
                        if (substr($method->name, 0, 8) == 'listenTo')
                        {
                                $event = lcfirst(substr($method->name, 8));
                                self::$listeners[$event][] = array (
                                        'type'   => Events_Manager::TYPE_OBJECT,
                                        'obj'    => $obj,
                                        'method' => $method->name
                                );
                        }
                }
        }

        static protected function staticListensToEvents($className)
        {
                $reflectionClass = new ReflectionClass($className);
                $methods = $reflectionClass->getMethods(ReflectionMethod::IS_STATIC);

                foreach ($methods as $method)
                {
                        if (substr($method->name, 0, 8) == 'listenTo')
                        {
                                $event = lcfirst(substr($method->name, 8));
                                self::$listeners[$event][] = array (
                                        'type'   => Events_Manager::TYPE_STATIC,
                                        'class'  => $className,
                                        'method' => $method->name
                               );
                        }
                }
        }

        static public function listensToEvents($classOrObj)
        {
                if (is_object($classOrObj))
                {
                        self::objectListensToEvents($classOrObj);
                }
                else
                {
                        self::staticListensToEvents($classOrObj);
                }
        }

        static public function getListeners()
        {
                return self::$listeners;
        }

        static public function triggerEvent($event, $source, $data)
        {
                // do we have anyone who cares about this event?
                if (!isset(self::$listeners[$event]))
                        return;

                // yes we do (fancy that!)
                foreach (self::$listeners[$event] as $listener)
                {
                        switch ($listener['type'])
                        {
                                case Events_Manager::TYPE_OBJECT:
                                        $obj    = $listener['obj'];
                                        $method = $listener['method'];

                                        $obj->$method($source, $data);
                                        break;

                                case Events_Manager::TYPE_STATIC:
                                        $class  = $listener['class'];
                                        $method = $listener['method'];

                                        call_user_func_array(array($class, $method), array ($source, $data));
                                        break;
                        }
                }
        }
}

interface Events_Listener
{

}

?>
