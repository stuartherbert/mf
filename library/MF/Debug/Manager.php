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
 * @package    MF_Debug
 * @copyright  Copyright (c) 2008-2010 Stuart Herbert.
 * @license    http://www.opensource.org/licenses/bsd-license.php Simplified BSD License
 * @version    0.1
 * @link       http://framework.methodosity.com
 */

__mf_init_module('FirePHP');

class MF_Debug_Manager
{
        /**
         * The object to use to log debugging messages
         *
         * @var Debug_Logger
         */
        public $log = null;

        /**
         * The object to use to track where the app spends its time
         *
         * @var Debug_Timer
         */
        public $timer = null;

        /**
         * A list of the different debug objects that are active, for us
         * to iterate over
         *
         * @var array
         */
        protected $debugPlugins = array();

        /**
         * Create the MF_Debug_Manager object
         *
         * @param MF_Debug_Logger $log    override the default logging object
         * @param MF_Debug_Timer  $timer  override the default timing object
         */
        public function __construct($log = null, $timer = null)
        {
                $this->setLogger($log);
                $this->setTimer($timer);
        }

        /**
         * Set the object to use to create log messages
         *
         * @param MF_Debug_Logger $log
         */
        public function setLogger($log = null)
        {
                if ($log === null)
                {
                        $log = new FirePHP(true);
                }

                $this->setPlugin('log', $log);
        }

        /**
         * Set the object to use to keep track of app performance
         *
         * @param Debug_Timer $timer
         */
        public function setTimer($timer = null)
        {
                if ($timer === null)
                {
                        $timer = new MF_Debug_Timer();
                }

                $this->setPlugin('timer', $timer);
        }

        public function setPlugin($name, $plugin)
        {
                $this->$name = $plugin;
                $this->debugPlugins[$name] = $plugin;
        }

        // ================================================================
        // Proxy support for enabling / disabling debugging
        // ----------------------------------------------------------------

        public function setEnabled($enabled = true)
        {
                foreach ($this->debugPlugins as $plugin)
                {
                        $plugin->setEnabled($enabled);
                }
        }

        // ================================================================
        // Proxy support for logging messages
        // ----------------------------------------------------------------

        public function info($message)
        {
                return $this->log->info($this->timer->getTimingPrefix(). $message);
        }

        public function warn($message)
        {
                return $this->log->warn($this->timer->getTimingPrefix() . $message);
        }

        public function error($message)
        {
                return $this->log->error($this->timer->getTimingPrefix() . $message);
        }
}

?>