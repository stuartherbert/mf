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
 * @package    MF_Language
 * @copyright  Copyright (c) 2008-2010 Stuart Herbert.
 * @license    http://www.opensource.org/licenses/bsd-license.php Simplified BSD License
 * @version    0.1
 * @link       http://framework.methodosity.com
 */

/**
 * Short-cut to save on typing when retrieving translated format strings
 *
 * @param string $module The name of the module which defines the translation
 * @param string $stringName The name of the translation to retrieve
 * @return string the transation, or $stringName if no translation found
 */
function mf_l($module, $stringName)
{
        return MF_App::$languages->getTranslation($module, $stringName);
}

/**
 * Retrieve a fully-exploded translation
 *
 * @param string $module The name of the module which defines the translation
 * @param string $stringName The name of the translation to retrieve
 * @param array $params The parameters to plug into the translation
 * @return string the translation with parameters plugged in
 */
function mf_translation($module, $stringName, $params = array())
{
        $formatString = MF_App::$languages->getTranslation($module, $stringName);
        return vsprintf($formatString, $params);
}

/**
 * Expand a translated format string previously retrived from l()
 *
 * @param string $formatString
 * @param array $params
 * @return string the translation with the parameters plugged in
 */
function mf_expandFormatString($formatString, $params = array())
{
        return vsprintf($formatString, $params);
}

?>