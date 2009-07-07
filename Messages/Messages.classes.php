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
// 2009-06-10   SLH     Added Messages::addErrorForField()
// 2009-06-10   SLH     Added Messages::addMessageForField()
// 2009-06-10   SLH     Fixxed Messages::getErrorCount()
// ========================================================================

class Messages extends Obj
{
        public $messages     = array();
        public $errors       = array();

        public function addMessage($module, $message, $params = array())
        {
                return $this->addMessageForField('unknown', $module, $message, $params);
        }

        public function addMessageForField($field, $module, $message, $params = array())
        {
                // special case - do not add empty messages
                if ($message === null || strlen($message) == 0)
                        return;

                $this->messages[$field][] = array
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
                return $this->addErrorForField('unknown', $module, $message, $params);
        }

        public function addErrorForField($field, $module, $message, $params = array())
        {
                // special case - do not add empty messages
                if ($message === null || strlen($message) == 0)
                {
                        return;
                }

                $this->errors[$field][] = array
                (
                        'module'  => $module,
                        'message' => $message,
                        'params'  => $params,
                );
        }

        public function getErrorCount()
        {
                return count($this->errors);
        }

        public function getErrorsForField($field)
        {
                if (!isset($this->errors[$field]))
                {
                        return array();
                }

                return $this->errors[$field];
        }

        public function getMessagesForField($field)
        {
                if (!isset($this->messages[$field]))
                {
                        return array();
                }

                return $this->messages[$field];
        }
}

?>
