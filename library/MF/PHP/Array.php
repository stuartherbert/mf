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
 * @package    MF_PHP
 * @copyright  Copyright (c) 2008-2010 Stuart Herbert.
 * @license    http://www.opensource.org/licenses/bsd-license.php Simplified BSD License
 * @version    0.1
 * @link       http://framework.methodosity.com
 */

__mf_init_module('PHP');

/**
 * @category   MF
 * @package    MF_PHP
 */
class MF_PHP_Array extends MF_PHP_ArrayWrapper
{
        // ================================================================
        // Additional methods to make dealing with arrays more useful

        public function &to_array()
        {
                return $this->__data;
        }

        public function &getData($key)
        {
                if (!isset($this->__data[$key]))
                {
                        throw new Exception();
                }

                return $this->__data[$key];
        }

        public function setData(&$__data)
        {
                $this->__data =& $__data;
                $this->rewind();
        }

        // ----------------------------------------------------------------
        // like setData(), except it doesn't break the connection to the
        // array that this object references.

        public function replaceData(&$__data)
        {
                $this->__data = $__data;
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
                $this->__keys = array_keys($this->__data);
        }

        // ================================================================
        // The standard PHP array methods, acting on this object

        public function change_key_case($case = CASE_LOWER)
        {
                $this->replaceData(array_change_key_case($this->__data, $case));

                return $this;
        }

        public function split_into_chunks($size, $preserveKeys = false)
        {
                $oReturn = new MF_PHP_Array(array_chunk($this->__data, $size, $preserveKeys));

                return $oReturn;
        }

        public function combine_keys_and_values($__keys, $aValues)
        {
                if ($__keys instanceof MF_PHP_Array)
                {
                        $__keys =& $__keys->to_array();
                }

                if ($aValues instanceof MF_PHP_Array)
                {
                        $aValues =& $aValues->to_array();
                }

                $this->replaceData(array_combine($__keys, $aValues));

                return $this;
        }

        public function count_values()
        {
                $oReturn = new MF_PHP_Array(array_count_values($this->__data));

                return $oReturn;
        }

        public function diff_using_keys_and_values($aArray)
        {
                if ($aArray instanceof MF_PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new MF_PHP_Array(array_diff_assoc($this->__data, $aArray));

                return $oReturn;
        }

        public function diff_using_keys($aArray)
        {
                if ($aArray instanceof MF_PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new MF_PHP_Array(array_diff_key($this->__data, $aArray));

                return $oReturn;
        }

        public function diff_using_keys_values_and_callback($aArray, $callback)
        {
                if ($aArray instanceof MF_PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new MF_PHP_Array(array_diff_uassoc($this->__data, $aArray, $callback));

                return $oReturn;
        }

        public function diff_using_keys_and_callback($aArray, $callback)
        {
                if ($aArray instanceof MF_PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new MF_PHP_Array(array_diff_ukey($this->__data, $aArray, $callback));

                return $oReturn;
        }

        public function diff_using_values($aArray)
        {
                if ($aArray instanceof MF_PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new MF_PHP_Array(array_diff($this->__data, $aArray));

                return $oReturn;
        }

        public function fill_keys($aArray, $aValue)
        {
                if ($aArray instanceof MF_PHP_Array)
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
                $this->replaceData(array_filter($this->__data, $callback));

                return $this;
        }

        public function flip()
        {
                $this->replaceData(array_flip($this->__data));

                return $this;
        }

        public function intersect_assoc($aArray)
        {
                if ($aArray instanceof MF_PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new MF_PHP_Array(array_intersect_assoc($this->__data, $aArray));

                return $oReturn;
        }

        public function intersect_key($aArray)
        {
                if ($aArray instanceof MF_PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new MF_PHP_Array(array_intersect_key($this->__data, $aArray));

                return $oReturn;
        }

        public function intersect_uassoc($aArray, $callback)
        {
                if ($aArray instanceof MF_PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new MF_PHP_Array(array_intersect_uassoc($this->__data, $aArray, $callback));

                return $oReturn;
        }

        public function intersect_ukey($aArray, $callback)
        {
                if ($aArray instanceof MF_PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new MF_PHP_Array(array_intersect_ukey($this->__data, $aArray, $callback));

                return $oReturn;
        }

        public function intersect($aArray)
        {
                if ($aArray instanceof MF_PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new MF_PHP_Array(array_intersect($this->__data, $aArray));

                return $oReturn;
        }

        public function key_exists($a_szKey)
        {
                return array_key_exists($a_szKey, $this->__data);
        }

        public function keys()
        {
                return new MF_PHP_Array(array_keys($this->__data));
        }

        public function map($callback)
        {
                $this->setData($callback, $this->__data);
        }

        public function merge_recursive($aArray)
        {
                if ($aArray instanceof MF_PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $this->setData(array_merge_recursive($this->__data, $aArray));

                return $this;
        }

        public function merge($aArray)
        {
                if ($aArray instanceof MF_PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $this->setData(array_merge($this->__data, $aArray));

                return $this;
        }

        // NOTE:
        //
        // We do not support array_multisort; it's too complicated
        // to support in this object

        public function pad($size, $value)
        {
                $this->setData(array_pad($this->__data, $size, $value));

                return $this;
        }

        public function pop()
        {
                $return = array_pop($this->__data);
                $this->rewind();

                return $return;
        }

        public function product()
        {
                return array_product($this->__data);
        }

        public function push($value)
        {
                array_push($this->__data, $value);
                $this->resetKeys();

                return $this;
        }

        public function random_subset($noRequired = 1)
        {
                if ($noRequired == 1)
                {
                        $__keys = array(array_rand($this->__data, 1));
                }
                else
                {
                        $__keys = array_rand($this->__data, $noRequired);
                }

                $aReturn = array();
                foreach ($__keys as $key)
                {
                        $aReturn[$key] = $this->__data[$key];
                }

                $oReturn = new MF_PHP_Array($aReturn);
                return $oReturn;
        }

        public function random_keys($noRequired = 1)
        {
                if ($noRequired == 1)
                {
                        $__keys = array(array_rand($this->__data, 1));
                }
                else
                {
                        $__keys = array_rand($this->__data, $noRequired);
                }

                $oReturn = new MF_PHP_Array($__keys);
                return $oReturn;
        }

        public function reduce($callback, $initial = null)
        {
                if ($initial == null)
                {
                        return array_reduce($this->__data, $callback);
                }
                else
                {
                        return array_reduce($this->__data, $callback, $initial);
                }
        }

        public function reverse($preserveKeys = true)
        {
                $this->replaceData(array_reverse($this->__data, $preserveKeys));

                return $this;
        }

        public function search($value, $strict = false)
        {
                return array_search($value, $this->__data, $strict);
        }

        public function shift($count = 1)
        {
                $toShift = count($this->__data) < $count ? count($this->__data) : $count;
                while ($toShift > 0)
                {
                        array_shift($this->__data);
                        $toShift--;
                }

                $this->rewind();

                return $this;
        }

        public function slice($offset, $length = null, $preserveKeys = false)
        {
                $oReturn = new MF_PHP_Array(array_slice($this->__data, $offset, $length, $preserveKeys));

                return $oReturn;
        }

        public function splice($offset, $aReplacement)
        {
                //$this->replaceData(array_splice($this->__data, $a_iOffset, $a_iLength, $a_aReplacement));
                array_splice($this->__data, $offset, count($aReplacement), $aReplacement);

                return $this;
        }

        public function sum()
        {
                return array_sum($this->__data);
        }

        public function udiff_assoc($aArray, $callback)
        {
                if ($aArray instanceof MF_PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new MF_PHP_Array(array_udiff_assoc($this->__data, $aArray, $callback));

                return $oReturn;
        }

        public function udiff_uassoc($aArray, $__dataCallback, $keyCallback)
        {
                if ($aArray instanceof MF_PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new MF_PHP_Array(array_udiff_uassoc($this->__data, $aArray, $__dataCallback, $keyCallback));

                return $oReturn;
        }

        public function udiff($aArray, $callback)
        {
                if ($aArray instanceof MF_PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new MF_PHP_Array(array_udiff($this->__data, $aArray, $callback));

                return $oReturn;
        }

        public function uintersect_assoc($aArray, $callback)
        {
                if ($aArray instanceof MF_PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new MF_PHP_Array(array_uintersect_assoc($this->__data, $aArray, $callback));

                return $oReturn;
        }

        public function uintersect_uassoc($aArray, $__dataCallback, $keyCallback)
        {
                if ($aArray instanceof MF_PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new MF_PHP_Array(array_uintersect_uassoc($this->__data, $aArray, $__dataCallback, $keyCallback));

                return $oReturn;
        }

        public function uintersect($aArray, $callback)
        {
                if ($aArray instanceof MF_PHP_Array)
                {
                        $aArray =& $aArray->to_array();
                }

                $oReturn = new MF_PHP_Array(array_uintersect($this->__data, $aArray, $callback));

                return $oReturn;
        }

        public function unique()
        {
                $this->replaceData(array_unique($this->__data));

                return $this;
        }

        public function unshift($value)
        {
                array_unshift($this->__data, $value);
                $this->reset();
                return $this;
        }

        public function values()
        {
                $oReturn = new MF_PHP_Array(array_values($this->__data));

                return $oReturn;
        }

        public function walk_recursive($callback, $__data = null)
        {
                array_walk_recursive($this->__data, $callback, $__data);

                return $this;
        }

        public function walk($callback, $__data = null)
        {
                array_walk($this->__data, $callback, $__data);

                return $this;
        }

        public function arsort($flags = SORT_REGULAR)
        {
                arsort($this->__data, $flags);
                $this->reset();

                return $this;
        }

        public function asort($flags = SORT_REGULAR)
        {
                asort($this->__data, $flags);
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
                return count($this->__data);
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

                $this->__index = count($this->__keys) - 1;
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
                return in_array($needle, $this->__data, $strict);
        }

        public function implode($separator = ',')
        {
                return implode($separator, $this->__data);
        }

        function implode_with_quotes($quote = "'", $separator = ',')
        {
                $append = false;
                $return = '';

                foreach ($this->__data as $value)
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
                krsort($this->__data, $flags);
                $this->rewind();

                return $this;
        }

        public function ksort($flags = SORT_REGULAR)
        {
                ksort($this->__data, $flags);
                $this->rewind();

                return $this;
        }

        public function natcasesort()
        {
                natcasesort($this->__data);
                $this->rewind();

                return $this;
        }

        public function natsort()
        {
                natsort($this->__data);
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

                $this->__index--;

                return $this->current();
        }

        public function range($low, $high, $step = 1)
        {
                $oReturn = new MF_PHP_Array(range($low, $high, $step));

                return $oReturn;
        }

        public function rsort($flags = SORT_REGULAR)
        {
                rsort($this->__data, $flags);

                $this->reset();
                return $this;
        }

        public function shuffle()
        {
                shuffle($this->__data);

                $this->reset();
                return $this;
        }

        public function sizeof()
        {
                return count($this->__data);
        }

        public function sort($flags = SORT_REGULAR)
        {
                sort($this->__data, $flags);
                $this->reset();

                return $this;
        }

        public function uasort($callback)
        {
                uasort($this->__data, $callback);
                $this->reset();

                return $this;
        }

        public function uksort($callback)
        {
                uksort($this->__data, $callback);
                $this->reset();

                return $this;
        }

        public function usort($callback)
        {
                usort($this->__data, $callback);
                $this->reset();

                return $this;
        }

        // ================================================================
        // Additional methods inspired by other languages
        // ================================================================

        // ----------------------------------------------------------------
        // appends an array to the current array
        //
        // if either of the arrays has non-numeric keys, then the resulting
        // behaviour is undefined

        public function append($secondArray)
        {
                constraint_mustBeArray($secondArray);

                foreach ($secondArray as $secondArrayData)
                {
                        $this->__data[] = $secondArrayData;
                }
        }

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
                foreach ($this->__data as $key => $value)
                {
                        if ($callback($value))
                        {
                                unset($this->__data[$key]);
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
                if (!isset($this->__data[$key]))
                {
                        return $default;
                }

                return $this->__data[$key];
        }

        // ----------------------------------------------------------------
        // returns the first element(s) of the array

        public function first($elements = 1)
        {
                // if they only want one element, return the value
                if ($elements == 1)
                {
                        $return = array_slice($this->__data, 0, 1);
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
                if (count($this->__data) == 0)
                {
                	return null;
                }

                // only want one element?
                if ($elements == 1)
                {
                	return end($this->__data);
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
                return serialize($this->__data);
        }

        // ----------------------------------------------------------------
        // get an element from the array

        public function get_element($key)
        {
                if (!isset($this->__data[$key]))
                {
                        return null;
                }

                return $this->__data[$key];
        }

        // ----------------------------------------------------------------
        // set an element in the array

        public function set_element($key, $value)
        {
                $this->__data[$key] = $value;
        }

        // ----------------------------------------------------------------
        // remove an element from the array

        public function unset_element($key)
        {
                if ($this->key_exists($key))
                {
                        unset($this->__data[$key]);
                        $this->resetKeys();
                }

                return $this;
        }
}

?>