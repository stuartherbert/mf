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
                return $this->log->info($message);
        }

        public function warn($message)
        {
                return $this->log->warn($message);
        }

        public function error($message)
        {
                return $this->log->error($message);
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

        protected $thresholds = array();

        public function __construct($slow = 0.1, $tooSlow = 0.2)
        {
                $this->startTime = microtime(true);
                $this->setThresholds ($slow, $tooSlow);
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
                $secs      = (int)($duration);
                $millisecs = (int)($duration * 1000 % 1000);

                return sprintf('%0'. $secDigits . '.' . $secDigits . 'd.%0' . $millisecDigits . '.' . $millisecDigits . 'd', $secs, $millisecs);
        }

        public function log($message, $duration)
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

        public function startEvent($name, $type)
        {
                $this->pauseStackedEvent();

                $event = new Debug_TimerEvent($name, $type);
                array_push($event, $this->eventStack);
        }

        public function endEvent()
        {
                // do we have an event currently in progress?
                if (count($this->eventStack) == 0)
                {
                        // no, we do not
                        return;
                }

                // handle the event in progress
                $event = array_pop($this->eventStack);
                $event->pause();
                $this->updateTypeTimings($event);
                $this->outputTimings($event);

                // restart the next event
                $this->resumeStackedEvent();
        }

        public function endAllEvents()
        {
                while (count($this->eventStack) > 0)
                {
                        $this->endEvent();
                }
        }

        public function summary()
        {
                $this->endAllEvents();

                // work out how long we have spent doing things
                $totalDuration = microtime(true) - $this->startTime;

                $this->log('FINAL SUMMARY :: Total Time: ' . $this->formatDuration($totalDuration));

                foreach ($this->eventTypes as $eventType)
                {
                        list($duration, $percentage) = $eventType->getDuration();
                        $this->log('PERCENTAGE: ' . $this->formatDuration($percentage, 2, 1) . '; TIME TAKEN: ' . $this->formatDuration($duration));
                }

                $this->log('FINAL SUMMARY :: Total Time: ' . $this->formatDuration($totalDuration));
        }

        public function pauseStackedEvent()
        {
                if (count($this->eventStack) == 0)
                {
                        return;
                }

                $event = array_pop($this->eventStack);
                $event->pause();
                array_push($event, $this->eventStack);
        }

        public function resumeStackedEvent()
        {
                if (count($this->eventStack) == 0)
                {
                        return;
                }

                $event = array_pop($this->eventStack);
                $event->resume();
                array_push($event, $this->eventStack);
        }

        public function updateTypeTimings(Debug_TimerEvent $event)
        {
                $type = $event->type;
                if (!isset($this->eventTypes[$type]))
                {
                        $this->eventTypes[$type] = new Debug_TimerType($type);
                }

                $this->eventsType[$type]->addDuration($event);
        }

        public function outputTimings(Debug_TimerEvent $event)
        {
                list($totalDuration, $activeDuration) = $event->getDuration();

                $timeSoFar = microtime(true) - $this->startTime;
                $out  = 'TIME SO FAR: ' . $this->formatDuration($timeSoFar) . '; ';
                $out .= 'DURATION: ' . $this->formatDuration($duration) . '; ';

                $this->log($out . $event->name, $totalDuration);
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
        public function __construct($name, $type)
        {
                $this->startTime  = microtime(true);
                $this->resumeTime = $this->startTime;

                $this->name = $name;
                $this->type = $type;
        }

        public function pause()
        {
                $this->duration += (microtime(true) - $this->resumeTime);
                $this->paused = true;
        }

        public function resume()
        {
                $this->paused     = false;
                $this->resumeTime = microtime(true);
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
                list($totalDuration, $activeDuration) = $event->duration();
                $this->duration .= $activeDuration;
        }
        
        public function getDuration($totalDuration)
        {
                $percentage = round($totalDuration / $this->duration * 100, 2);
                return array($this->duration, $percentage);
        }


}
?>