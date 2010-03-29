<?php

/**
 * Methodosity Framework
 *
 * LICENSE
 *
 * Copyright (c) 2010 Stuart Herbert
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *  * Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *  * Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in
 *    the documentation and/or other materials provided with the
 *    distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   MF
 * @package    MF_Events
 * @copyright  Copyright (c) 2008-2010 Stuart Herbert.
 * @license    http://www.opensource.org/licenses/bsd-license.php Simplified BSD License
 * @version    0.1
 * @link       http://framework.methodosity.com
 */

/**
 * @category MF
 * @package MF_Events
 */

class MF_Events_Manager
{
        static protected $listeners = array();

        const TYPE_OBJECT = 1;
        const TYPE_STATIC = 2;

        static public function destroy()
        {
                self::$listeners = array();
        }

        static protected function objectListensToEvents(MF_Events_Listener $obj)
        {
                $reflectionObj = new ReflectionObject($obj);
                $methods = $reflectionObj->getMethods(ReflectionMethod::IS_PUBLIC);

                foreach ($methods as $method)
                {
                        if (substr($method->name, 0, 8) == 'listenTo')
                        {
                                $event = lcfirst(substr($method->name, 8));
                                self::$listeners[$event][] = array (
                                        'type'   => MF_Events_Manager::TYPE_OBJECT,
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
                                        'type'   => MF_Events_Manager::TYPE_STATIC,
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
                {
                        // var_dump('triggerEvent:: no listeners for event ' . $event);
                        return;
                }

                // yes we do (fancy that!)
                foreach (self::$listeners[$event] as $listener)
                {
                        switch ($listener['type'])
                        {
                                case MF_Events_Manager::TYPE_OBJECT:
                                        $obj    = $listener['obj'];
                                        $method = $listener['method'];

                                        $obj->$method($source, $data);
                                        break;

                                case MF_Events_Manager::TYPE_STATIC:
                                        $class  = $listener['class'];
                                        $method = $listener['method'];

                                        call_user_func_array(array($class, $method), array ($source, $data));
                                        break;
                        }
                }
        }
}

?>