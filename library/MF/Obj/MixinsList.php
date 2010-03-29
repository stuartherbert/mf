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
        protected $mixins          = array();
        protected $mixinMethods    = array();
        protected $mixinProperties = array();

        protected $cachedMixinMethods    = array();
        protected $cachedMixinProperties = array();

        protected $lastSeenMixinAutoInc = 0;
        protected $baseClasses     = array();

        public function __construct($classname)
        {
                $this->name = $classname;
        }

        public function withClass($extensionClass)
        {
                // var_dump('Extending ' . $this->name . ' with ' . $extensionClass);
                // var_dump('We have ' . count($this->mixins) . ' mixins before this one');

                constraint_mustBeString($extensionClass);

                $this->mixins[$extensionClass] = $extensionClass;

                $methods    = $this->getMethodsFromClass($extensionClass);
                $properties = $this->getPropertiesFromClass($extensionClass);

                foreach ($methods as $method)
                {
                        $this->mixinMethods[$method][] = $extensionClass;
                }

                foreach ($properties as $property)
                {
                        $this->mixinProperties[$property] = $extensionClass;
                }

                MF_Events_Manager::triggerEvent('classExtended', null, array('class' => $this->name, 'extension' => $extensionClass));

                // var_dump('We now have ' . count($this->mixins) . ' mixins after this one');
        }

        public function getMixinCount()
        {
                $baseClasses = $this->getBaseClasses();

                $count = 0;

                foreach ($baseClasses as $baseClass)
                {
                        $mixins = MF_Obj_MixinsManager::getMixinsFor($baseClass);
                        if ($mixins === null)
                                continue;

                        // var_dump('getMixinCount(): found ' . $mixins->_getMixinCount() . ' mixins for base class ' . $baseClass);
                        // var_dump($mixins);
                        $count += $mixins->_getMixinCount();
                }

                return $count;
        }

        public function _getMixinCount()
        {
                return count($this->mixins);
        }

        public function updateBaseClassList()
        {
                // we need to look at all the base classes of the class
                // we have extended, and make sure we know about their
                // mixins too
                //
                // to boost performance, we look at the auto increment
                // counter in Obj_MixinDefinitions to see whether our
                // cache is still valid or not

                //var_dump('Started updateBaseClassList');
                if ($this->lastSeenMixinAutoInc !== MF_Obj_MixinsManager::$mixinAutoInc)
                {
                        // we need to rebuild our list
                        $baseClasses = $this->getBaseClasses();
                        foreach ($baseClasses as $baseClass)
                        {
                                $mixins = MF_Obj_MixinsManager::_getMixinsFor($baseClass);

                                // skip over any classes that have not been
                                // extended at this time
                                if ($mixins === null)
                                {
                                        continue;
                                }

                                $methods    = $mixins->getMethods();
                                constraint_mustBeArray($methods);
                                
                                foreach ($methods as $method => $classnames)
                                {
                                        constraint_mustBeArray($classnames);
                                        foreach ($classnames as $classname)
                                        {
                                                // var_dump('Adding ' . $classname . '::' . $method . ' to list of cached methods');
                                                $this->cachedMixinMethods[$method][] = $classname;
                                        }
                                }

                                $properties = $mixins->getProperties();
                                $this->cachedMixinProperties += $properties;
                        }

                        $this->lastSeenMixinAutoInc = MF_Obj_MixinsManager::$mixinAutoInc;

                }

                // at this point, the cachedMixin* properties have the
                // right values (in the right order!!) to be used by the
                // classes we have extended
                // var_dump('Finished updateBaseClassList()');
        }

        public function getBaseClasses()
        {
                $refObj      = new ReflectionClass($this->name);
                $baseClasses = array();

                while ($refObj !== false)
                {
                        $baseClasses[] = $refObj->getName();
                        $refObj = $refObj->getParentClass();
                }

                // var_dump($baseClasses);
                return $baseClasses;
        }

        public function getMethodsFromClass($classname)
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

        public function getPropertiesfromClass($classname)
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
                if (!isset($this->cachedMixinMethods[$method]))
                {
                        return null;
                }
                return $this->cachedMixinMethods[$method];
        }

        public function getClassnameForMethod($method)
        {
                // var_dump('looking in cached for ' . $method);

                if (!isset($this->cachedMixinMethods[$method]))
                {
                        return null;
                }

                $count = count($this->cachedMixinMethods[$method]);
                if ($count == 0)
                {
                        // should never happen, but just in case
                        return null;
                }

                // var_dump($this->cachedMixinMethods[$method]);
                // var_dump($count);
                // var_dump($this->cachedMixinMethods[$method][$count - 1]);

                return $this->cachedMixinMethods[$method][$count - 1];
        }

        public function getClassnameForProperty($property)
        {
                if (!isset($this->cachedMixinProperties[$property]))
                {
                        return null;
                }
                return $this->cachedMixinProperties[$property];
        }
}

?>