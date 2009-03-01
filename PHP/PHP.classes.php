<?php

// ========================================================================
//
// PHP/PHP.classes.php
//              Classes to help with working with the PHP language
//
//              Part of the Modular Framework for PHP applications
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
// 2007-08-11   SLH     Consolidated from individual files
// ========================================================================

class PHP_Array implements Iterator
{
        private $aData = array();
        private $aKeys = array();

        private $index = 0;

        public function __construct (&$aArray = null)
        {
                if ($aArray != null)
                {
                        constraint_mustBeArray($aArray);
                        $this->aData =& $aArray;
                        $this->rewind();
                }
        }

        // ================================================================
        // Interface: Iterator
        // ----------------------------------------------------------------

        public function rewind()
        {
                $this->index = 0;
                $this->aKeys  = array_keys($this->aData);
        }

        public function valid()
        {
                if (!isset($this->aKeys[$this->index]))
                        return false;

                if (!isset($this->aData[$this->aKeys[$this->index]]))
                        return false;

                return true;
        }

        public function key()
        {
                return $this->aKeys[$this->index];
        }

        public function current()
        {
                return $this->aData[$this->aKeys[$this->index]];
        }

        public function value()
        {
                return $this->aData[$this->aKeys[$this->index]];
        }

        public function next()
        {
                $this->index++;

                return $this->valid();
        }

        // ================================================================
        // Additional methods to make dealing with arrays more useful

        public function previous()
        {
                if ($this->index > 0)
                {
                        $this->index--;
                }

                return $this->valid();
        }

        public function index()
        {
                return $this->index;
        }

        public function &to_array()
        {
                return $this->aData;
        }

        public function &getData($key)
        {
                if (!isset($this->aData[$key]))
                {
                        throw new Exception();
                }

                return $this->aData[$key];
        }

        public function setData(&$aData)
        {
                $this->aData =& $aData;
                $this->rewind();
        }

        // ----------------------------------------------------------------
        // like setData(), except it doesn't break the connection to the
        // array that this object references.

        public function replaceData(&$aData)
        {
                $this->aData = $aData;
                $this->rewind();
        }

        // ----------------------------------------------------------------
        //
        // NOTES:
        //
        // *  Originally added for when we increase the length of the
        //    existing array.  We do not want to automatically rewind()
        //    the entire array

        protected function resetKeys()
        {
                $this->aKeys = array_keys($this->aData);
        }

        // ================================================================
        // The standard PHP array methods, acting on this object

        public function change_key_case($case = CASE_LOWER)
        {
                $this->replaceData(array_change_key_case($this->aData, $case));

                return $this;
        }

        public function split_into_chunks($size, $preserveKeys = false)
        {
                $oReturn = new PHP_Array(array_chunk($this->aData, $size, $preserveKeys));

                return $oReturn;
        }

        public function combine_keys_and_values($aKeys, $aValues)
        {
                if ($aKeys instanceof PHP_Array)
                {
                        $aKeys =& $aKeys->to_array();
                }

                if ($aValues instanceof PHP_Array)
                {
                        $aValues =& $aValues->to_array();
                }

                $this->replaceData(array_combine($aKeys, $aValues));

                return $this;
        }

        public function count_values()
        {
                $oReturn = new PHP_Array(array_count_values($this->aData));

                return $oReturn;
        }

        public function diff_using_keys_and_values($aArray)
        {
                if ($aArray instanceof PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new PHP_Array(array_diff_assoc($this->aData, $aArray));

                return $oReturn;
        }

        public function diff_using_keys($aArray)
        {
                if ($aArray instanceof PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new PHP_Array(array_diff_key($this->aData, $aArray));

                return $oReturn;
        }

        public function diff_using_keys_values_and_callback($aArray, $callback)
        {
                if ($aArray instanceof PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new PHP_Array(array_diff_uassoc($this->aData, $aArray, $callback));

                return $oReturn;
        }

        public function diff_using_keys_and_callback($aArray, $callback)
        {
                if ($aArray instanceof PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new PHP_Array(array_diff_ukey($this->aData, $aArray, $callback));

                return $oReturn;
        }

        public function diff_using_values($aArray)
        {
                if ($aArray instanceof PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new PHP_Array(array_diff($this->aData, $aArray));

                return $oReturn;
        }

        public function fill_keys($aArray, $aValue)
        {
                if ($aArray instanceof PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $this->replaceData(array_fill_keys($aArray, $aValue));

                return $this;

        }

        public function fill($start, $length, $value)
        {
                $this->replaceData(array_fill($start, $length, $value));

                return $this;
        }

        public function filter($callback)
        {
                $this->replaceData(array_filter($this->aData, $callback));

                return $this;
        }

        public function flip()
        {
                $this->replaceData(array_flip($this->aData));

                return $this;
        }

        public function intersect_assoc($aArray)
        {
                if ($aArray instanceof PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new PHP_Array(array_intersect_assoc($this->aData, $aArray));

                return $oReturn;
        }

        public function intersect_key($aArray)
        {
                if ($aArray instanceof PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new PHP_Array(array_intersect_key($this->aData, $aArray));

                return $oReturn;
        }

        public function intersect_uassoc($aArray, $callback)
        {
                if ($aArray instanceof PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new PHP_Array(array_intersect_uassoc($this->aData, $aArray, $callback));

                return $oReturn;
        }

        public function intersect_ukey($aArray, $callback)
        {
                if ($aArray instanceof PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new PHP_Array(array_intersect_ukey($this->aData, $aArray, $callback));

                return $oReturn;
        }

        public function intersect($aArray)
        {
                if ($aArray instanceof PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new PHP_Array(array_intersect($this->aData, $aArray));

                return $oReturn;
        }

        public function key_exists($a_szKey)
        {
                return array_key_exists($a_szKey, $this->aData);
        }

        public function keys()
        {
                return new PHP_Array(array_keys($this->aData));
        }

        public function map($callback)
        {
                $this->setData($callback, $this->aData);
        }

        public function merge_recursive($aArray)
        {
                if ($aArray instanceof PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $this->setData(array_merge_recursive($this->aData, $aArray));

                return $this;
        }

        public function merge($aArray)
        {
                if ($aArray instanceof PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $this->setData(array_merge($this->aData, $aArray));

                return $this;
        }

        // NOTE:
        //
        // We do not support array_multisort; it's too complicated
        // to support in this object

        public function pad($size, $value)
        {
                $this->setData(array_pad($this->aData, $size, $value));

                return $this;
        }

        public function pop()
        {
                $return = array_pop($this->aData);
                $this->rewind();

                return $return;
        }

        public function product()
        {
                return array_product($this->aData);
        }

        public function push($value)
        {
                array_push($this->aData, $value);
                $this->resetKeys();

                return $this;
        }

        public function random_subset($noRequired = 1)
        {
                if ($noRequired == 1)
                {
                        $aKeys = array(array_rand($this->aData, 1));
                }
                else
                {
                        $aKeys = array_rand($this->aData, $noRequired);
                }

                $aReturn = array();
                foreach ($aKeys as $key)
                {
                        $aReturn[$key] = $this->aData[$key];
                }

                $oReturn = new PHP_Array($aReturn);
                return $oReturn;
        }

        public function random_keys($noRequired = 1)
        {
                if ($noRequired == 1)
                {
                        $aKeys = array(array_rand($this->aData, 1));
                }
                else
                {
                        $aKeys = array_rand($this->aData, $noRequired);
                }

                $oReturn = new PHP_Array($aKeys);
                return $oReturn;
        }

        public function reduce($callback, $initial = null)
        {
                if ($initial == null)
                {
                        return array_reduce($this->aData, $callback);
                }
                else
                {
                        return array_reduce($this->aData, $callback, $initial);
                }
        }

        public function reverse($preserveKeys = true)
        {
                $this->replaceData(array_reverse($this->aData, $preserveKeys));

                return $this;
        }

        public function search($value, $strict = false)
        {
                return array_search($value, $this->aData, $strict);
        }

        public function shift($count = 1)
        {
                $toShift = count($this->aData) < $count ? count($this->aData) : $count;
                while ($toShift > 0)
                {
                        array_shift($this->aData);
                        $toShift--;
                }

                $this->rewind();

                return $this;
        }

        public function slice($offset, $length = null, $preserveKeys = false)
        {
                $oReturn = new PHP_Array(array_slice($this->aData, $offset, $length, $preserveKeys));

                return $oReturn;
        }

        public function splice($offset, $aReplacement)
        {
                //$this->replaceData(array_splice($this->aData, $a_iOffset, $a_iLength, $a_aReplacement));
                array_splice($this->aData, $offset, count($aReplacement), $aReplacement);

                return $this;
        }

        public function sum()
        {
                return array_sum($this->aData);
        }

        public function udiff_assoc($aArray, $callback)
        {
                if ($aArray instanceof PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new PHP_Array(array_udiff_assoc($this->aData, $aArray, $callback));

                return $oReturn;
        }

        public function udiff_uassoc($aArray, $dataCallback, $keyCallback)
        {
                if ($aArray instanceof PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new PHP_Array(array_udiff_uassoc($this->aData, $aArray, $dataCallback, $keyCallback));

                return $oReturn;
        }

        public function udiff($aArray, $callback)
        {
                if ($aArray instanceof PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new PHP_Array(array_udiff($this->aData, $aArray, $callback));

                return $oReturn;
        }

        public function uintersect_assoc($aArray, $callback)
        {
                if ($aArray instanceof PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new PHP_Array(array_uintersect_assoc($this->aData, $aArray, $callback));

                return $oReturn;
        }

        public function uintersect_uassoc($aArray, $dataCallback, $keyCallback)
        {
                if ($aArray instanceof PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new PHP_Array(array_uintersect_uassoc($this->aData, $aArray, $dataCallback, $keyCallback));

                return $oReturn;
        }

        public function uintersect($aArray, $callback)
        {
                if ($aArray instanceof PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new PHP_Array(array_uintersect($this->aData, $aArray, $callback));

                return $oReturn;
        }

        public function unique()
        {
                $this->replaceData(array_unique($this->aData));

                return $this;
        }

        public function unshift($value)
        {
                array_unshift($this->aData, $value);
                $this->reset();
                return $this;
        }

        public function values()
        {
                $oReturn = new PHP_Array(array_values($this->aData));

                return $oReturn;
        }

        public function walk_recursive($callback, $aData = null)
        {
                array_walk_recursive($this->aData, $callback, $aData);

                return $this;
        }

        public function walk($callback, $aData = null)
        {
                array_walk($this->aData, $callback, $aData);

                return $this;
        }

        public function arsort($flags = SORT_REGULAR)
        {
                arsort($this->aData, $flags);
                $this->reset();

                return $this;
        }

        public function asort($flags = SORT_REGULAR)
        {
                asort($this->aData, $flags);
                $this->reset();

                return $this;
        }

/*
        public function compact()
        {

        }
*/

        public function count()
        {
                return count($this->aData);
        }

        public function each()
        {
                if (!$this->valid())
                {
                        return false;
                }

                $oReturn[$this->key()] = $this->current();
                $this->next();

                return $oReturn;
        }

        public function end()
        {
                // special case: empty array
                if ($this->count() == 0)
                        return false;

                $this->index = count($this->aKeys) - 1;
                return $this->current();
        }

/*

not supported because it is dangerous

        public function extract()
        {

        }
*/

        public function in_array($needle, $strict = false)
        {
                return in_array($needle, $this->aData, $strict);
        }

        public function implode($separator = ',')
        {
                return implode($separator, $this->aData);
        }

        function implode_with_quotes($quote = "'", $separator = ',')
        {
                $append = false;
                $return = '';

                foreach ($this->aData as $value)
                {
                        if ($append)
                        {
                                $return .= $separator;
                        }
                        $append = true;

                        $return .= $quote . $value . $quote;
                }

                return $return;
        }

        public function krsort($flags = SORT_REGULAR)
        {
                krsort($this->aData, $flags);
                $this->rewind();

                return $this;
        }

        public function ksort($flags = SORT_REGULAR)
        {
                ksort($this->aData, $flags);
                $this->rewind();

                return $this;
        }

        public function natcasesort()
        {
                natcasesort($this->aData);
                $this->rewind();

                return $this;
        }

        public function natsort()
        {
                natsort($this->aData);
                $this->rewind();

                return $this;
        }

        public function pos()
        {
                return $this->current();
        }

        public function prev()
        {
                if ($this->iIndex == 0)
                {
                        return false;
                }

                $this->index--;

                return $this->current();
        }

        public function range($low, $high, $step = 1)
        {
                $oReturn = new PHP_Array(range($low, $high, $step));

                return $oReturn;
        }

        public function rsort($flags = SORT_REGULAR)
        {
                rsort($this->aData, $flags);

                $this->reset();
                return $this;
        }

        public function shuffle()
        {
                shuffle($this->aData);

                $this->reset();
                return $this;
        }

        public function sizeof()
        {
                return count($this->aData);
        }

        public function sort($flags = SORT_REGULAR)
        {
                sort($this->aData, $flags);
                $this->reset();

                return $this;
        }

        public function uasort($callback)
        {
                uasort($this->aData, $callback);
                $this->reset();

                return $this;
        }

        public function uksort($callback)
        {
                uksort($this->aData, $callback);
                $this->reset();

                return $this;
        }

        public function usort($callback)
        {
                usort($this->aData, $callback);
                $this->reset();

                return $this;
        }

        // ================================================================
        // Additional methods inspired by other languages
        // ================================================================

        // ----------------------------------------------------------------
        // empties the current array

        public function clear()
        {
                $aEmpty = array();
                $this->replaceData($aEmpty);

                return $this;
        }

        // ----------------------------------------------------------------
        // alias for merge_recursive

        public function concat($aArray)
        {
                return $this->merge_recursive($aArray);
        }

        // ----------------------------------------------------------------
        // removes every element where the callback returns true

        public function delete_if($callback)
        {
                foreach ($this->aData as $key => $value)
                {
                        if ($callback($value))
                        {
                                unset($this->aData[$key]);
                        }
                }

                $this->rewind();

                return $this;
        }

        // ----------------------------------------------------------------
        // returns TRUE if the array is empty

        public function is_empty()
        {
                if ($this->count() == 0)
                {
                        return true;
                }

                return false;
        }

        // ----------------------------------------------------------------
        // return an element from the array, or $default if the element
        // does not exist

        public function fetch($key, $default = null)
        {
                if (!isset($this->aData[$key]))
                {
                        return $default;
                }

                return $this->aData[$key];
        }

        // ----------------------------------------------------------------
        // returns the first element(s) of the array

        public function first($elements = 1)
        {
                // if they only want one element, return the value
                if ($elements == 1)
                {
                        $return = array_slice($this->aData, 0, 1);
                        reset($return);
                        return current($return);
                }

                // they want more than one element, so return an array
                return $this->slice(0, $elements);
        }

        // ----------------------------------------------------------------
        // returns a string made from all the values in the array
        // this is really an alias of implode() for the ruby crowd

        public function join($separator = '')
        {
                return $this->implode($separator);
        }

        // ----------------------------------------------------------------
        // returns the last element(s) of the array

        public function last($elements = 1)
        {
                // just in case someone tries to be clever
                if ($elements < 1)
                {
                        return null;
                }

                // cannot return the last element of an empty array
                if (count($this->aData) == 0)
                {
                	return null;
                }

                // only want one element?
                if ($elements == 1)
                {
                	return end($this->aData);
                }

                $start = $this->count() - $elements;

                if ($start < 0 )
                {
                        // trying to return more than the array holds
                        $start = 0;
                }

                return $this->slice($start, $elements);
        }

        // ----------------------------------------------------------------
        // returns the serialized array, not the serialized object

        public function serialize()
        {
                return serialize($this->aData);
        }

        // ----------------------------------------------------------------
        // get an element from the array

        public function get_element($key)
        {
                if (!isset($this->aData[$key]))
                {
                        return null;
                }

                return $this->aData[$key];
        }

        // ----------------------------------------------------------------
        // set an element in the array

        public function set_element($key, $value)
        {
                $this->aData[$key] = $value;
        }

        // ----------------------------------------------------------------
        // remove an element from the array

        public function unset_element($key)
        {
                if ($this->key_exists($key))
                {
                        unset($this->aData[$key]);
                        $this->resetKeys();
                }

                return $this;
        }
}

// ========================================================================

class PHP_StringUtils
{
        static function quote($string)
        {
                if (is_numeric($string))
                {
                        return $string;
                }

                return "'" . $string . "'";
        }

        static public function stripQuotes ($text)
        {
                // step 1: does the text start with any quotes?
                $quote = trim($text[0]);
                if ($quote != '"' and $quote != "'")
                        return $text;

                // step 2: yes it does
                //
                // strip this quote from the front of the text, and
                // from the rear of the text if present

                $start = 1;
                $end   = strlen($text);
                if ($text[$end - 1] == $quote)
                        $end--;

                return substr($text, $start, $end - $start);
        }
}

?>