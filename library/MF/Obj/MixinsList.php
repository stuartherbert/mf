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
 * @package    MF_Obj
 * @copyright  Copyright (c) 2008-2010 Stuart Herbert.
 * @license    http://www.opensource.org/licenses/bsd-license.php Simplified BSD License
 * @version    0.1
 * @link       http://framework.methodosity.com
 */

/**
 * @category   MF
 * @package    MF_Obj
 */
class MF_Obj_MixinsList
{
        /**
         * A list of all of the classes that *directly* are mixins
         * for the class name stored in $this->name
         *
         * @var array
         */
        protected $mixins          = array();

        /**
         * A list of which methods have been mixed in, and which classes
         * the method is defined on
         *
         * NOTE: we deliberately keep a list of all of the classes which
         * define a method, so that we can call all of them if requested
         *
         * @var array
         */
        protected $mixinMethods    = array();

        /**
         * A list of the properties that have been mixed in, and which
         * class the property is defined on
         *
         * NOTE: we only store the latest class for each property (ie,
         * the class that has been mixed in last).
         *
         * @var array
         */
        protected $mixinProperties = array();

        /**
         * A list of the base classes, to avoid having to recalculate
         * the list every time we need to iterate over it
         *
         * @var array
         */
        protected $baseClasses     = array();

        /**
         * The name of the PHP class that we are tracking mixin information
         * about
         * 
         * @var string
         */
        public $name = null;

        /**
         * A counter of how many classes both directly and indirectly
         * are mixed into the class listed in $this->name
         * @var int
         */
        protected $mixinsCount = 0;

        /**
         * A cache of MF_Obj_MixinsManager::mixinAutoInc. We use this to
         * work out whether or not we really do need to regenerate the
         * information we've cached regarding the methods and properties
         * of the mixins
         * 
         * @var int
         */
        protected $lastMixinAutoInc = 0;

        public function __construct($classname)
        {
                $this->name        = $classname;
                $this->baseClasses = MF_Obj_MixinsManager::getBaseclassesForClass($classname);
        }

        public function withClass($extensionClass)
        {
                // var_dump('Extending ' . $this->name . ' with ' . $extensionClass);
                // var_dump('We have ' . count($this->mixins) . ' mixins before this one');

                constraint_mustBeString($extensionClass);
                constraint_mustBeMixinClass($extensionClass);

                $this->mixins[$extensionClass] = $extensionClass;
                $this->updateMethodsAndProperties();
                MF_Events_Manager::triggerEvent('classExtended', null, array('class' => $this->name, 'extension' => $extensionClass));

                // var_dump('We now have ' . count($this->mixins) . ' mixins after this one');
        }

        public function getMixinsCount()
        {
                return $this->mixinsCount;
        }

        public function updateMethodsAndProperties()
        {
                // if our cache is up to date, nothing to do
                if ($this->lastMixinAutoInc === MF_Obj_MixinsManager::$mixinAutoInc)
                {
                        return;
                }
                $this->lastMixinAutoInc = MF_Obj_MixinsManager::$mixinAutoInc;

                // var_dump('Updating methods and properties for ' . $this->name);

                // we need to look at all the base classes of the class
                // we have extended, and make sure we know about their
                // mixins too

                $this->mixinMethods    = array();
                $this->mixinProperties = array();
                $this->mixinsCount     = 0;

                // step 1: pull in all the mixins for our baseclasses
                $this->addMethodsFromMixins($this->name);
                $this->addPropertiesFromMixins($this->name);
                $this->updateMixinsCountFromMixins($this->name);

                // step 2: pull in all the mixins that we need to add
                foreach ($this->mixins as $extensionClass)
                {
                        $this->addMethodsFromClass($extensionClass);
                        $this->addPropertiesFromClass($extensionClass);
                        $this->updateMixinsCountFromClass($extensionClass);
                }
        }

        public function addMethodsFromClass($extensionClass)
        {
                // get the class's main methods
                $definedMethods = $this->getMethodsFromClass($extensionClass);
                foreach ($definedMethods as $method)
                {
                        $this->mixinMethods[$method][] = $extensionClass;
                }

                $this->addMethodsFromMixins($extensionClass);
                // all done
        }

        public function addPropertiesFromClass($extensionClass)
        {
                // get the class's defined properties first
                $definedProperties = $this->getPropertiesfromClass($extensionClass);
                foreach ($definedProperties as $property)
                {
                        $this->mixinProperties[$property] = $extensionClass;
                }

                $this->addPropertiesFromMixins($extensionClass);
        }

        public function updateMixinsCountFromClass($extensionClass)
        {
                // var_dump($this->name . ": $extensionClass is a mixin; adding to the mixin count");
                $this->mixinsCount++;
                // var_dump($this->name . ": Mixins count now increased to " . $this->mixinsCount);
                $this->updateMixinsCountFromMixins($extensionClass);
        }

        public function addMethodsFromMixins($extensionClass)
        {
                // var_dump($this->name . ': adding methods from mixin ' . $extensionClass);
                
                // get the class's class hierarchy
                $classHierarchy = MF_Obj_MixinsManager::getBaseclassesForClass($extensionClass);

                foreach ($classHierarchy as $extensionClass)
                {
                        // var_dump($this->name . ': looking for mixins for class ' . $extensionClass);
                        // now pull in that class's mixins
                        $mixinsList = MF_Obj_MixinsManager::getMixinsFor($extensionClass);
                        if ($mixinsList == null)
                        {
                                continue;
                        }
                        // var_dump($this->name . ': found mixins for class ' . $extensionClass);
                        
                        $mixinMethods = $mixinsList->getMethods();
                        foreach ($mixinMethods as $method => $classes)
                        {
                                foreach ($classes as $class)
                                {
                                        $this->mixinMethods[$method][] = $class;
                                }
                        }

                        // the mixin will include any mixins from its
                        // base classes ... we need go no further
                        break;
                }
                // all done
        }

        public function addPropertiesFromMixins($extensionClass)
        {
                // get the class's class hierarchy
                $classHierarchy = MF_Obj_MixinsManager::getBaseclassesForClass($extensionClass);

                foreach ($classHierarchy as $extensionClass)
                {
                        // now pull in that class's mixins
                        $mixinsList = MF_Obj_MixinsManager::getMixinsFor($extensionClass);
                        if ($mixinsList == null)
                        {
                                continue;
                        }
                        
                        $mixinProperties = $mixinsList->getProperties();
                        foreach ($mixinProperties as $property => $class)
                        {
                                $this->mixinProperties[$property] = $class;
                        }

                        // the mixin will include any mixins from its
                        // base classes ... we need go no further
                        break;
                }
                // all done
        }

        public function updateMixinsCountFromMixins($extensionClass)
        {
                $classHierarchy = MF_Obj_MixinsManager::getBaseclassesForClass($extensionClass);
                foreach ($classHierarchy as $classname)
                {
                        // var_dump($this->name . ": Looking at the mixins count for $classname; current mixins count is " . $this->mixinsCount);
                        
                        $mixins = MF_Obj_MixinsManager::getMixinsFor($classname);
                        if ($mixins == null)
                                continue;

                        $this->mixinsCount += $mixins->getMixinsCount();
                        // var_dump($this->name . ": Mixins count now increased to " . $this->mixinsCount);

                        // the mixin will include how many mixins its
                        // baseclasses have defined ... we need go no further
                        break;
                }
        }

        protected function getMethodsFromClass($classname)
        {
                $reflection = new ReflectionClass($classname);
                $methods    = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
                $return     = array();

                foreach ($methods as $method)
                {
                        $return[$method->getName()] = $method->getName();
                }

                return $return;
        }

        protected function getPropertiesfromClass($classname)
        {
                $reflection = new ReflectionClass($classname);
                $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
                $return     = array();

                foreach ($properties as $property)
                {
                        $return[$property->getName()] = $property->getName();
                }

                return $return;
        }

        public function getMethods()
        {
                return $this->mixinMethods;
        }

        public function getProperties()
        {
                return $this->mixinProperties;
        }

        public function getClassnamesForMethod($method)
        {
                if (!isset($this->mixinMethods[$method]))
                {
                        return null;
                }
                return array_reverse($this->mixinMethods[$method]);
        }

        public function getClassnameForMethod($method)
        {
                // var_dump('looking in cached for ' . $method);

                if (!isset($this->mixinMethods[$method]))
                {
                        return null;
                }

                $count = count($this->mixinMethods[$method]);
                constraint_mustNotBeEmptyArray($this->mixinMethods[$method]);

                // var_dump($this->cachedMixinMethods[$method]);
                // var_dump($count);
                // var_dump($this->cachedMixinMethods[$method][$count - 1]);

                return $this->mixinMethods[$method][$count - 1];
        }

        public function getClassnameForProperty($property)
        {
                if (!isset($this->mixinProperties[$property]))
                {
                        return null;
                }
                return $this->mixinProperties[$property];
        }
}

?>