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
 * Add a mixin to the class hierarchy, or extend one object with
 * a decorator
 *
 * @param mixed $classOrObject class or object to extend
 * @param string $extensionClassOrObject class to mix into $classOrObject
 */
function __mf_extend($classOrObject, $extensionClassOrObject)
{
        if (is_object($classOrObject))
        {
                // we are adding a decorator to an object
                constraint_mustBeExtensible($classOrObject);
                constraint_mustBeObject($extensionClassOrObject);

                $classOrObject->addDecorator($extensionClassOrObject);
        }
        else
        {
                // we are adding a mixin to the class hierarchy
                MF_Obj_MixinsManager::extend($classOrObject)->withClass($extensionClassOrObject);
        }
}

function constraint_mustBeExtensible($object)
{
        if (!$object instanceof MF_Obj_Extensible)
        {
                throw new MF_PHP_E_ConstraintFailed(__FUNCTION__);
        }
}

function constraint_mustBeMixinClass($classname)
{
        // a list of the classes we've seen before, to speed things up
        // (hopefully)
        static $cachedClasses = array();

        $refObj = new ReflectionClass($classname);
        $classesExamined = array();

        while ($refObj !== false)
        {
                if (isset($cachedClasses[$refObj->name]))
                {
                        if ($cachedClasses[$refObj->name])
                                return;

                        throw new MF_PHP_E_ConstraintFailed(__FUNCTION__);
                }

                if ($refObj->name == 'MF_Obj_Mixin')
                {
                        // update the cache
                        foreach ($classesExamined as $seenClass)
                        {
                                $cachedClasses[$seenClass] = true;
                        }
                        return;
                }

                // if we get here, we need to look at the parent class
                // of the parent class
                $classesExamined[] = $refObj->name;
                $refObj = $refObj->getParentClass();
        }

        // if we get here, then this class is not a valid mixin

        // update the cache
        foreach ($classesExamined as $seenClass)
        {
                $cachedClasses[$seenClass] = false;
        }

        throw new MF_PHP_E_ConstraintFailed(__FUNCTION__);
}

/*
 * currently not used
 *
 * will uncomment out when it is needed
function constraint_mustBeValidMixin($obj)
{
        if (!is_object($obj))
        {
                throw new MF_PHP_E_ConstraintFailed(__FUNCTION__, null);
        }

        if (!$obj instanceof MF_Obj_Mixin)
        {
                throw new MF_PHP_E_ConstraintFailed(__FUNCTION__, null);
        }
}
*/
?>