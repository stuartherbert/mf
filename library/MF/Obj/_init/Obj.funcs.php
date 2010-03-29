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

function debug_vardump($file, $line, $function, $title, $var)
{
	echo "--- var_dump: $function: $title ---\n";
	echo basename($file) . "@$line\n";
	echo "--- data ---\n";
	var_dump($var);
	echo "--- end of var_dump ---\n";
}

/**
 *
 * @param mixed $classOrObject class or object to extend
 * @param string $extensionClass class to mix into $classOrObject
 */
function __mf_extend($classOrObject, $extensionClass)
{
        if (is_object($classOrObject))
        {
                $classname = get_class($classOrObject);
        }
        else
        {
                $classname = $classOrObject;
        }

        MF_Obj_MixinsManager::extend($classname)->withClass($extensionClass);
}

function constraint_mustBeValidMixin($obj)
{
        if (!is_object($obj))
        {
                throw new MF_PHP_E_ConstraintFailed(__FUNCTION__, null);
        }

        if (!$obj instanceof Obj_Mixin)
        {
                throw new MF_PHP_E_ConstraintFailed(__FUNCTION__, null);
        }
}

function constraint_mustBeObject($obj)
{
        if (!is_object($obj))
        {
                throw new MF_PHP_E_ConstraintFailed(__FUNCTION__, null);
        }
}

?>