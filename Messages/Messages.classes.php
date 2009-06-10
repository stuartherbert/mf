<?php

// ========================================================================
//
// Messages/Messages.classes.php
//              Defines the classes for the Messages component
//
//              Part of the Methodosity Framework for PHP Applications
//              http://blog.stuartherbert.com/php/mf/
//
// Author       Stuart Herbert
//              (stuart@stuartherbert.com)
//
// Copyright    (c) 2007-2009 Stuart Herbert
//              Released under v3 of the GNU Affero Public License
//
// ========================================================================

// ========================================================================
// When         Who     What
// ------------------------------------------------------------------------
// 2007-12-02   SLH     Created from separate components
// 2008-01-03   SLH     Moved Render_Messages class to AppMessages
// 2009-06-07   SLH     Moved Messages class into own component
// ========================================================================

class Messages extends Obj
{
        public $messages     = array();
        public $errors       = array();

        public function addMessage($module, $message, $params = array())
        {
                // special case - do not add empty messages
                if ($message === null || strlen($message) == 0)
                        return;

                $this->messages[] = array
                (
                        'module'    => $module,
                        'message'   => $message,
                        'params'    => $params,
                );
        }

        public function getMessageCount()
        {
                return count($this->messages);
        }

        public function addError($module, $message, $params = array())
        {
                // special case - do not add empty messages
                if ($message === null || strlen($message) == 0)
                {
                        return;
                }

                $this->errors[] = array
                (
                        'module'  => $module,
                        'message' => $message,
                        'params'  => $params,
                );
        }

        public function getErrorCount()
        {
                return count($this->messages);
        }
}

?>
