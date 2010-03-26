<?php

// ========================================================================
//
// Debug/Debug.classes.php
//              Classes defined by the debug component
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
// 2009-07-26   SLH     Created
// 2009-07-26   SLH     Fixes and improvement to debugging time output
// 2009-07-26   SLH     Added setEnabled() to Debug_Timer
// ========================================================================

class Debug_Manager
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
         * Create the Debug_Manager object
         *
         * @param Debug_Logger $log    override the default logging object
         * @param Debug_Timer  $timer  override the default timing object
         */
        public function __construct($log = null, $timer = null)
        {
                $this->setLogger($log);
                $this->setTimer($timer);
        }

        /**
         * Set the object to use to create log messages
         *
         * @param Debug_Logger $log
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
                        $timer = new Debug_Timer();
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

interface Debug_Logger
{
        public function setEnabled($enabled);
        public function info($message);
        public function warn($message);
        public function error($message);
}

class Debug_Timer extends Obj
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

class Debug_TimerEvent
{
        public $name = null;
        public $type = null;

        protected $startTime  = 0;
        protected $resumeTime = 0;
        protected $duration   = 0;

        protected $paused     = false;

        /**
         * Create a new event
         */
        public function __construct($name, $type, $startTime)
        {
                $this->setStartTime($startTime);

                $this->name = $name;
                $this->type = $type;
        }

        public function setStartTime($startTime)
        {
                $this->startTime  = $startTime;
                $this->resumeTime = $startTime;
        }
        
        public function pause($now)
        {
                $this->duration += ($now - $this->resumeTime);
                $this->paused = true;

//                App::$debug->info ('Pausing ' . $this->name . ' with duration ' . $this->duration);
        }

        public function resume($now)
        {
                $this->paused     = false;
                $this->resumeTime = $now;

//                App::$debug->info('Resuming ' . $this->name);
        }

        public function getDuration()
        {
                $now = microtime(true);
                $totalDuration = $now - $this->startTime;

                $activeDuration = $this->duration;
                if (!$this->paused)
                {
                        $activeDuration .= $now - $this->resumeTime;
                }

                return array($totalDuration, $activeDuration);
        }
}

class Debug_TimerType
{
        public $type = null;

        protected $duration = 0;

        public function __construct($type)
        {
                $this->type = $type;
        }

        public function addDuration(Debug_TimerEvent $event)
        {
                list($totalDuration, $activeDuration) = $event->getDuration();
                $this->duration += $activeDuration;
        }
        
        public function getDuration($totalDuration)
        {
                $percentage = round(($this->duration / $totalDuration) * 100, 2);
                return array($this->duration, $percentage);
        }
}

?>