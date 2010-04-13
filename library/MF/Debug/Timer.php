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

class MF_Debug_Timer extends MF_Obj_Extensible
{
        /**
         * Is debugging enabled or not?
         *
         * @var boolean
         */
        protected $enabled = true;

        /**
         *
         * @var array a stack of events we are currently interested in
         */
        protected $eventStack = array();

        /**
         *
         * @var array a list of the types of event we have come across
         */
        protected $eventTypes = array();

        /**
         * When did we start tracking the time things are taking?
         *
         * @var float
         */
        protected $startTime = 0.0;
        protected $activeDuration = 0.0;

        protected $thresholds = array();

        public function __construct($slow = 0.1, $tooSlow = 0.2)
        {
                if (defined('START_TIME'))
                {
                        $this->startTime = START_TIME;
                }
                else
                {
                        $this->startTime = microtime(true);
                }
                $this->setThresholds ($slow, $tooSlow);
        }

        public function setEnabled($enabled = true)
        {
                $this->enabled = $enabled;
        }

        public function setThresholds($slow = 0.1, $tooSlow = 0.2)
        {
                $this->thresholds['error'] = $tooSlow;
                $this->thresholds['warn']  = $slow;
                $this->thresholds['info']  = 0.0;
        }

        /**
         * Format elapsed time. Necessary because sprintf() et al are
         * unable to format floats.
         *
         * @param float $duration the elapsed time to be formated
         * @param int $secDigits min number of second digits to be shown
         * @param int $millisecDigits number of millisecond digits to be shown
         * @return string the formatted elapsed time
         */
        public function formatDuration($duration, $secDigits = 2, $millisecDigits = 3)
        {
                $secs         = (int)($duration);
                $millisecs    = (int)($duration * 1000 % 1000);
                $millisecs    = substr($millisecs, 0, $millisecDigits);

                $formatString = "%{$secDigits}.{$secDigits}d.%0{$millisecDigits}.{$millisecDigits}d";

                return sprintf($formatString, $secs, $millisecs);
        }

        protected function log($message, $duration = 0)
        {
                foreach ($this->thresholds as $func => $threshold)
                {
                        if ($duration >= $threshold)
                        {
                                // we have found what we need;
                                // bail out
                                break;
                        }
                }

                App::$debug->$func($message);
        }

        public function markEvent($name)
        {
                if (!$this->enabled)
                        return;

                $this->log('MARK ' . $name);
        }

        public function startEvent($name, $type)
        {
                if (!$this->enabled)
                        return;

                $now = microtime(true);

                // do we have an event on the stack?
                // if not, we add an event to track the bootstrap time
                if (count($this->eventStack) == 0)
                {
                        $event = new Debug_TimerEvent('bootstrap', 'bootstrap', $this->startTime);
                        array_push($this->eventStack, $event);
                }

                // pause the currently active event
                $this->pauseStackedEvent($now);

                $event = new Debug_TimerEvent($name, $type, $now);
                $this->outputStartTimings($event);

                array_push($this->eventStack, $event);
        }

        public function endEvent()
        {
                if (!$this->enabled)
                        return;

                $now = microtime(true);

                // do we have an event currently in progress?
                if (count($this->eventStack) == 0)
                {
                        // no, we do not
                        return;
                }

                // handle the event in progress
                $event = array_pop($this->eventStack);
                $event->pause($now);
                $this->updateTypeTimings($event);
                $this->outputEndTimings($event);

                // restart the next event
                $this->resumeStackedEvent($now);
        }

        public function endAllEvents()
        {
                if (!$this->enabled)
                        return;

                while (count($this->eventStack) > 0)
                {
                        $this->endEvent();
                }
        }

        public function summary()
        {
                if (!$this->enabled)
                        return;

                $this->endAllEvents();

                // work out how long we have spent doing things
                $totalDuration = microtime(true) - $this->startTime;
                $activePercentage = $this->activeDuration / $totalDuration * 100;

                $this->log('FINAL SUMMARY :: Total Time: ' . $this->formatDuration($totalDuration) . '; Active Time: ' . $this->formatDuration($this->activeDuration) . ' (' . $this->formatDuration($activePercentage, 2, 1) . '%)');

                foreach ($this->eventTypes as $eventType)
                {
                        list($duration, $percentage) = $eventType->getDuration($totalDuration);
                        $this->log('-- TIME TAKEN: ' . $this->formatDuration($duration) . ' (' . $this->formatDuration($percentage, 2, 1) . '%); TYPE ' . $eventType->type);
                }

                $this->log('FINAL SUMMARY :: Total Time: ' . $this->formatDuration($totalDuration) . '; Active Time: ' . $this->formatDuration($this->activeDuration) . ' (' . $this->formatDuration($activePercentage, 2, 1) . '%)');
        }

        protected function pauseStackedEvent($now)
        {
                if (count($this->eventStack) == 0)
                {
                        return;
                }

                $event = array_pop($this->eventStack);
                $event->pause($now);
                array_push($this->eventStack, $event);
        }

        protected function resumeStackedEvent($now)
        {
                if (count($this->eventStack) == 0)
                {
                        return;
                }

                $event = array_pop($this->eventStack);
                $event->resume($now);
                array_push($this->eventStack, $event);
        }

        protected function updateTypeTimings(Debug_TimerEvent $event)
        {
                $type = $event->type;
                if (!isset($this->eventTypes[$type]))
                {
                        $this->eventTypes[$type] = new Debug_TimerType($type);
                }

                $this->eventTypes[$type]->addDuration($event);

                //list($totalDuration, $percentage) = $this->eventTypes[$type]->getDuration(microtime(true));
                //$this->log('Event ' . $type . ' now has duration ' . $totalDuration);
        }

        public function getTimingPrefix()
        {
                $timeSoFar = microtime(true) - $this->startTime;
                $out  = 'TIME SO FAR: ' . $this->formatDuration($timeSoFar) . '; '
                      . str_repeat('--', count($this->eventStack)) . ' ';

                return $out;
        }

        protected function outputStartTimings(Debug_TimerEvent $event)
        {
                $out = 'START ' . $event->name;
                $this->log($out, $event->name);
        }

        protected function outputEndTimings(Debug_TimerEvent $event)
        {
                list($totalDuration, $activeDuration) = $event->getDuration();

                $out  = 'END ' . $event->name . '; ';
                $out .= 'DURATION: ' . $this->formatDuration($totalDuration) . '; ';
                $out .= 'ACTIVE: ' . $this->formatDuration($activeDuration);

                $this->activeDuration += $activeDuration;

                $this->log($out, $totalDuration);
        }
}

?>
