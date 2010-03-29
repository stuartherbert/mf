<?php

class MF_PHP_Array_Tests extends PHPUnit_Framework_TestCase
{
        public function setup ()
        {
                $this->testArray = array
                (
                        'alice'         => 1,
                        'joanne'        => 2,
                        'marie'         => 3,
                        'vanessa'       => 4,
                        'nicola'        => 5,
                        'samantha'      => 6,
                        'catherine'     => 7,
                        'emily'         => 8,
                        'karen'         => 9,
                        'elizabeth'     => 10
                );

                // take a copy, when we need to prove that the
                // testArray has not been changed
                $this->testArrayOrig = $this->testArray;

                $this->fixture = new MF_PHP_Array($this->testArray);
        }

        function testImplementsIterator()
        {
                $this->assertTrue($this->fixture instanceof Iterator);
        }

        function testHasPrivateData()
        {
                $this->assertFalse(isset($this->fixture->aData));
        }

        function testCanRewind()
        {
                $this->fixture->next();
                $this->fixture->rewind();
                $this->assertEquals(1, $this->fixture->current());
        }

        function testDoesNotFallOffTheEnd()
        {
                for($i = 0; $i < 10; $i++)
                        $this->fixture->next();

                $this->assertFalse($this->fixture->next());
        }

        function testHandlesKeysCorrectly()
        {
                for ($i = 0; $i < 9; $i++)
                        $this->fixture->next();

                $this->assertEquals('elizabeth', $this->fixture->key());
        }

        function testCopesWithChangesToTheUnderlyingArray()
        {
                $this->testArray[] = 2;

                $this->fixture->rewind();
                for ($i = 0 ; $i < 10; $i++)
                {
                        $this->fixture->next();
                }

                $this->assertEquals(2, $this->fixture->current());
        }

        function testCanMoveForwardsAndBackwards()
        {
                $this->fixture->next();
                $this->fixture->next();
                $this->fixture->next();

                $this->assertEquals('vanessa', $this->fixture->key());
                $this->assertEquals(4, $this->fixture->current());

                $this->fixture->previous();

                $this->assertEquals('marie', $this->fixture->key());
                $this->assertEquals(3, $this->fixture->current());
        }

        function testCanDeterminePositionInArray()
        {
                $this->fixture->next();
                $this->fixture->next();
                $this->fixture->next();

                $this->assertEquals(3, $this->fixture->index());
        }

        function testCanRetrieveUnderlyingArray()
        {
                $this->assertEquals($this->testArray, $this->fixture->to_array());
        }

        function testCanRetrieveByKey()
        {
                $this->assertEquals(1, $this->fixture->getData('alice'));
                $this->assertEquals(2, $this->fixture->fetch('joanne'));
        }

        function testCanChangeCaseOfKeys()
        {
                $testArray = array
                (
                        'ALICE'         => 1,
                        'JOANNE'        => 2,
                        'MARIE'         => 3,
                        'VANESSA'       => 4,
                        'NICOLA'        => 5,
                        'SAMANTHA'      => 6,
                        'CATHERINE'     => 7,
                        'EMILY'         => 8,
                        'KAREN'         => 9,
                        'ELIZABETH'     => 10
                );

                $this->assertEquals(
                        $testArray,
                        $this->fixture->change_key_case(CASE_UPPER)->to_array()
                );

                $this->assertEquals($testArray, $this->fixture->to_array());
        }

        function testCanSplitIntoChunks()
        {
                $testArray = array
                (
                        array (
                                'alice'         => 1,
                                'joanne'        => 2,
                                'marie'         => 3,
                        ),
                        array (
                                'vanessa'       => 4,
                                'nicola'        => 5,
                                'samantha'      => 6,
                        ),
                        array (
                                'catherine'     => 7,
                                'emily'         => 8,
                                'karen'         => 9,
                        ),
                        array (
                                'elizabeth'     => 10
                        )
                );

                $this->assertEquals(
                        $testArray,
                        $this->fixture->split_into_chunks(3, true)->to_array()
                );
        }

        function testSplittingIntoChunksDoesNotAffectObject()
        {
                $this->fixture->split_into_chunks(3, true);

                $this->assertEquals($this->testArrayOrig, $this->testArray);
        }

        function testCanBuildFromSeparateListOfKeysAndValues()
        {
                $aKeys   = array_keys($this->testArray);
                $aValues = array_values($this->testArray);

                $this->assertEquals(
                        $aKeys,
                        array
                        (
                                'alice',
                                'joanne',
                                'marie',
                                'vanessa',
                                'nicola',
                                'samantha',
                                'catherine',
                                'emily',
                                'karen',
                                'elizabeth'
                        )
                );

                $this->assertEquals
                (
                        $aValues,
                        array
                        (
                                1,
                                2,
                                3,
                                4,
                                5,
                                6,
                                7,
                                8,
                                9,
                                10
                        )
                );

                // prove that our test object starts out empty
                $oTestArray = new MF_PHP_Array();
                $this->assertEquals(0, $oTestArray->count());
                $this->assertEquals(array(), $oTestArray->to_array());

                // now, combine the keys and values
                $oTestArray->combine_keys_and_values($aKeys, $aValues);

                // prove that it worked
                $this->assertEquals(count($this->testArray), $oTestArray->count());
                $this->assertEquals($this->testArray, $oTestArray->to_array());
        }

        function testCanCountValues()
        {
                $oReturn = $this->fixture->count_values();
                $this->assertEquals(
                        $oReturn->to_array(),
                        array (
                                1  => 1,
                                2  => 1,
                                3  => 1,
                                4  => 1,
                                5  => 1,
                                6  => 1,
                                7  => 1,
                                8  => 1,
                                9  => 1,
                                10 => 1
                        )
                );
        }

        function testCanDiffTwoArraysUsingExactMatching()
        {
                $oReturn = $this->fixture->diff_using_keys_and_values
                (
                        array
                        (
                                'vanessa' => 4,
                                'marie'   => 3
                        )
                );

                // make sure this hasn't changed our original array
                $this->assertEquals($this->testArrayOrig, $this->testArray);

                // remove those two keys from our comparison array
                unset($this->testArray['vanessa']);
                unset($this->testArray['marie']);

                // make sure we have a new object back
                $this->assertNotSame($oReturn, $this->fixture);
                $this->assertEquals($this->testArray, $oReturn->to_array());
        }

        function testCanDiffTwoArraysUsingJustTheKeys()
        {
                $oReturn = $this->fixture->diff_using_keys
                (
                        array
                        (
                                'vanessa' => 1,
                                'marie'   => 1
                        )
                );

                // make sure this hasn't changed our original array
                $this->assertEquals($this->testArrayOrig, $this->testArray);

                // remove those two keys from our comparison array
                unset($this->testArray['vanessa']);
                unset($this->testArray['marie']);

                // make sure we have a new object back
                $this->assertNotSame($oReturn, $this->fixture);
                $this->assertEquals($this->testArray, $oReturn->to_array());
        }

        function testCanDiffTwoArraysUsingTheKeysValuesAndACallback()
        {

        }

        function testCanDiffTwoArraysUsingTheKeysAndACallback()
        {

        }

        function testCanDiffTheKeysUsingJustTheValues()
        {
                $oReturn = $this->fixture->diff_using_values
                (
                        array
                        (
                                'fred'  => 4,
                                'joe'   => 3
                        )
                );

                // make sure this hasn't changed our original array
                $this->assertEquals($this->testArrayOrig, $this->testArray);

                // remove those two keys from our comparison array
                unset($this->testArray['vanessa']);
                unset($this->testArray['marie']);

                // make sure we have a new object back
                $this->assertNotSame($oReturn, $this->fixture);
                $this->assertEquals($this->testArray, $oReturn->to_array());
        }

        function testCanCreateAnArrayFilledWithTheSameValue()
        {
                $this->fixture->fill_keys
                (
                        array_keys($this->testArray),
                        1
                );

                // prove that our original array has changed
                $this->assertNotEquals($this->testArrayOrig, $this->testArray);

                // prove that we have the result we want
                $this->assertEquals(array_keys($this->testArrayOrig), array_keys($this->testArray));

                foreach ($this->fixture as $key => $value)
                {
                        $this->assertTrue(isset($this->testArrayOrig[$key]));
                        $this->assertEquals(1, $value);
                }
        }

        function testCanCreateARangedArrayFilledWithTheSameValue()
        {
                $this->fixture->fill(10, 20, 99);

                // prove that our original array has changed
                $this->assertNotEquals($this->testArrayOrig, $this->testArray);

                // prove that we have the result we want
                $this->assertEquals
                (
                        array
                        (
                                10 => 99,
                                11 => 99,
                                12 => 99,
                                13 => 99,
                                14 => 99,
                                15 => 99,
                                16 => 99,
                                17 => 99,
                                18 => 99,
                                19 => 99,
                                20 => 99,
                                21 => 99,
                                22 => 99,
                                23 => 99,
                                24 => 99,
                                25 => 99,
                                26 => 99,
                                27 => 99,
                                28 => 99,
                                29 => 99
                        ),
                        $this->fixture->to_array()
                );
        }

        public function testCanFilterContentsUsingCallback()
        {
                $this->fixture->filter('MF_PHP_Array_filter_callback');

                $this->assertNotEquals($this->testArrayOrig, $this->testArray);
                $this->assertEquals
                (
                        array
                        (
                                'samantha'      => 6,
                                'catherine'     => 7,
                                'emily'         => 8,
                                'karen'         => 9,
                                'elizabeth'     => 10
                        ),
                        $this->fixture->to_array()
                );
        }

        public function testCanFlipKeysAndValues()
        {
                $this->fixture->flip();
                $this->assertNotEquals($this->testArrayOrig, $this->testArray);

                $this->assertEquals
                (
                        array
                        (
                                1  => 'alice',
                                2  => 'joanne',
                                3  => 'marie',
                                4  => 'vanessa',
                                5  => 'nicola',
                                6  => 'samantha',
                                7  => 'catherine',
                                8  => 'emily',
                                9  => 'karen',
                                10 => 'elizabeth'
                        ),
                        $this->fixture->to_array()
                );
        }

        public function testCanGetIntersectionUsingKeysAndValues()
        {

        }

        public function testCanGetIntersectionUsingKeys()
        {

        }

        public function testCanGetIntersectionUsingKeysValuesAndCallback()
        {

        }

        public function testCanGetIntersectionUsingKeysAndCallback()
        {

        }

        public function testCanGetIntersectionUsingValues()
        {

        }

        public function testCanCheckKeyExists()
        {
                $keys = array_keys($this->testArrayOrig);

                foreach ($keys as $key)
                {
                        $this->assertTrue($this->fixture->key_exists($key));
                }
        }

        public function testCanGetKeys()
        {
                $this->assertEquals
                (
                        array_keys($this->testArrayOrig),
                        $this->fixture->keys()->to_array()
                );
        }

        public function testCanMapArrayUsingCallback()
        {

        }

        public function testCanMergeTwoArraysTogetherRecursively()
        {

        }

        public function testCanMergeTwoArraysTogether()
        {

        }

        public function testCanPadArrayWithValues()
        {

        }

        public function testCanTreatArrayLikeAStack()
        {
                $oArray = new MF_PHP_Array();
                for ($i = 1; $i <= 10; $i++)
                {
                        $oArray->push($i);
                }

                $this->assertEquals
                (
                        array
                        (
                                1,
                                2,
                                3,
                                4,
                                5,
                                6,
                                7,
                                8,
                                9,
                                10
                        ),
                        $oArray->to_array()
                );

                for ($i = 10; $i > 0; $i--)
                {
                        $this->assertEquals($i, $oArray->pop());
                }
        }

        public function testCanGetRandomSubsets()
        {
                $oReturn = $this->fixture->random_subset(4);

                $this->assertEquals(4, $oReturn->count());
                foreach ($oReturn as $key => $value)
                {
                        $this->assertTrue(array_key_exists($key, $this->testArray));
                        $this->assertEquals($value, $this->testArray[$key]);
                }
        }

        public function testCanGetRandomKeys()
        {
                $oReturn = $this->fixture->random_keys(6);

                $this->assertEquals(6, $oReturn->count());
                foreach ($oReturn as $dummy => $key)
                {
                        $this->assertTrue(array_key_exists($key, $this->testArray));
                }
        }

        public function testCanReduceArrayUsingCallback()
        {

        }

        public function testCanReverseOrderOfKeys()
        {
                $aExpectedResult = array
                (
                        'elizabeth'     => 10,
                        'karen'         => 9,
                        'emily'         => 8,
                        'catherine'     => 7,
                        'samantha'      => 6,
                        'nicola'        => 5,
                        'vanessa'       => 4,
                        'marie'         => 3,
                        'joanne'        => 2,
                        'alice'         => 1,
                );

                // make sure that the underlying PHP method hasn't
                // regressed
                $this->assertEquals
                (
                        $aExpectedResult,
                        array_reverse($this->testArrayOrig, true)
                );

                // reverse the array
                $this->fixture->reverse();

                // make sure the array is still the required length
                $this->assertEquals(count($this->testArrayOrig), count($this->testArray));

                // we cannot check that the two arrays are now different,
                // because PHPUnit doesn't take the order of the keys into
                // account
                // $this->assertNotEquals($this->testArrayOrig, $this->testArray);

                // make sure the array really has been reversed
                $this->assertEquals
                (
                        $aExpectedResult,
                        $this->fixture->to_array()
                );
        }

        public function testCanSearchArrayForValues()
        {
                $this->assertEquals('vanessa', $this->fixture->search(4));
                $this->assertEquals('alice', $this->fixture->search(1));
                $this->assertEquals('karen', $this->fixture->search(9));
        }

        public function testCanShiftArray()
        {
                // make sure we are at the start of the array
                $this->assertEquals(1, $this->fixture->current());

                // shift the array along one
                $this->fixture->shift();

                // make sure the array has changed
                $this->assertEquals(2, $this->fixture->current());
                $this->assertEquals(9, $this->fixture->count());

                // make sure the underlying array has changed
                $this->assertEquals(9, count($this->testArray));

                // now, shift the array along more than one
                $this->fixture->shift(2);

                // make sure the array has changed
                $this->assertEquals(4, $this->fixture->current());
                $this->assertEquals(7, $this->fixture->count());

                // make sure the underlying array has changed
                $this->assertEquals(7, count($this->testArray));
        }

        public function testCanSliceArray()
        {
                // we need to flip the array to make this test work
                // with our test data
                $this->fixture->flip();

                // cut us a slice of this array
                $oReturn = $this->fixture->slice(3, 3);

                $this->assertEquals(3, $oReturn->count());
                $this->assertEquals
                (
                        array
                        (
                                0  => 'vanessa',
                                1  => 'nicola',
                                2  => 'samantha',
                        ),
                        $oReturn->to_array()
                );

                // make sure the original is unchanged
                $this->assertEquals(10, $this->fixture->count());
        }

        public function testCanSliceArrayAndPreserveKeys()
        {
                // we need to flip the array to make this test work
                // with our test data
                $this->fixture->flip();

                // cut us a slice of this array
                $oReturn = $this->fixture->slice(3, 3, true);

                $this->assertEquals(3, $oReturn->count());
                $this->assertEquals
                (
                        array
                        (
                                4  => 'vanessa',
                                5  => 'nicola',
                                6  => 'samantha',
                        ),
                        $oReturn->to_array()
                );

                // make sure the original is unchanged
                $this->assertEquals(10, $this->fixture->count());
        }

        public function testCanSpliceArray()
        {
                // we need to flip the array to make this test work
                // with our test data
                $this->fixture->flip();

                // splice in the replacment array
                $this->fixture->splice
                (
                        3,
                        array
                        (
                                'fred',
                                'tom',
                                'harry',
                                'jack'
                        )
                );

                // prove that the splice has happened
                $this->assertEquals(10, $this->fixture->count());
                $this->assertEquals
                (
                        array
                        (
                                0  => 'alice',
                                1  => 'joanne',
                                2  => 'marie',
                                3  => 'fred',
                                4  => 'tom',
                                5  => 'harry',
                                6  => 'jack',
                                7  => 'emily',
                                8  => 'karen',
                                9  => 'elizabeth'
                        ),
                        $this->fixture->to_array()
                );

                // prove that the underlying array has changed
                $this->assertNotEquals($this->testArrayOrig, $this->testArray);
        }

        function testCanSumArray()
        {
                $this->assertEquals(55, $this->fixture->sum());
        }

        function testCanUdiffAssoc()
        {

        }

        function testCanUdiffUassoc()
        {

        }

        function testCanUdiff()
        {

        }

        function testCanUintersectAssoc()
        {

        }

        function testCanUintersectUassoc()
        {

        }

        function testCanUintersect()
        {

        }

        function testCanReduceArrayToUniqueValues()
        {
                $this->fixture->unique();
                $this->assertEquals
                (
                        $this->testArrayOrig,
                        $this->testArray
                );

                $this->fixture->set_element('fred', 3);
                $this->assertNotEquals
                (
                        $this->testArrayOrig,
                        $this->testArray
                );

                $this->fixture->unique();
                $this->assertEquals
                (
                        $this->testArrayOrig,
                        $this->testArray
                );
        }

        function testCanUnshiftArray()
        {

        }

        function testCanRetrieveArrayValues()
        {

        }

        function testCanWalkArrayRecursively()
        {

        }

        function testCanWalkArray()
        {

        }

        function testCanReverseSortArrayAndPreserveKeys()
        {

        }

        function testCanSortArrayAndPreserveKeys()
        {

        }

        function testCanGetSizeOfArray()
        {
                $this->assertEquals(10, $this->fixture->count());
                $this->fixture->push(1);
                $this->assertEquals(11, $this->fixture->count());
        }

        function testCanGetLastElementInArray()
        {
                // entry conditions
                $this->assertEquals(10, $this->fixture->count());

                // test conditions
                $this->assertEquals(10, $this->fixture->end());
                $this->assertEquals(10, $this->fixture->last());
        }

        function testCanCheckIfValueIsInArray()
        {
                $this->assertTrue($this->fixture->in_array(1));
                $this->assertFalse($this->fixture->in_array(11));
        }

        function testCanImplodeArray()
        {
                $this->assertEquals
                (
                        '1,2,3,4,5,6,7,8,9,10',
                        $this->fixture->implode(',')
                );
        }

        function testCanImplodeWithQuotes()
        {

        }

        function testCanReverseSortUsingKeys()
        {

        }

        function testCanSortUsingKeys()
        {

        }

        function testCanCaseInsensitiveSortUsingEnglishRules()
        {

        }

        function testCanCaseSensitiveSortUsingEnglishRules()
        {

        }

        function testCanRetrievePreviousElement()
        {

        }

        function testCanCreateArrayOfRangedValues()
        {

        }

        function testCanReverseSortUsingValues()
        {

        }

        function testCanShuffleTheArray()
        {

        }

        function testCanSortUsingValues()
        {

        }

        function testCanSortUsingCallbackAndPreserveKeys()
        {

        }

        function testCanSortUsingKeysAndCallback()
        {

        }

        function testCanSortUsingValuesAndCallback()
        {

        }

        function testCanEmptyArray()
        {
                $this->assertEquals(10, $this->fixture->count());
                $this->assertEquals($this->testArrayOrig, $this->testArray);

                $this->fixture->clear();

                $this->assertEquals(0, $this->fixture->count());
                $this->assertNotEquals($this->testArrayOrig, $this->testArray);
        }

        function testCanDeleteElementsIfCallbackReturnsTrue()
        {
                $this->fixture->delete_if('MF_PHP_Array_filter_callback');

                $this->assertEquals
                (
                        array
                        (
                                'alice'         => 1,
                                'joanne'        => 2,
                                'marie'         => 3,
                                'vanessa'       => 4,
                                'nicola'        => 5,
                        ),
                        $this->fixture->to_array()
                );
        }

        function testCanTellIfArrayIsEmpty()
        {
                $this->assertFalse($this->fixture->is_empty());

                $this->fixture->clear();
                $this->assertTrue($this->fixture->is_empty());
                $this->assertEquals(0, $this->fixture->count());
        }

        function testCanFetchElements()
        {
                $this->assertEquals(1, $this->fixture->fetch('alice'));
                $this->assertEquals(2, $this->fixture->fetch('joanne'));

                $this->assertFalse($this->fixture->fetch('fred', false));
        }

        function testCanGetFirstValue()
        {
                $this->assertEquals(1, $this->fixture->first());
        }

        function testCanGetAllValuesAsASingleString()
        {
                $this->assertEquals('12345678910', $this->fixture->join());
        }

        function testCanGetLastValue()
        {
                $this->assertEquals(10, $this->fixture->last());
        }

        function testCanSerialize()
        {
                $this->assertEquals
                (
                        serialize($this->testArrayOrig),
                        $this->fixture->serialize()
                );
        }

        function testCanWorkWithElements()
        {
                $this->assertFalse(isset($this->testArray['fred']));
                $this->fixture->set_element('fred', 10);
                $this->assertTrue(isset($this->testArray['fred']));
                $this->assertEquals(10, $this->fixture->get_element('fred'));

                $this->fixture->unset_element('fred');
                $this->assertFalse(isset($this->testArray['fred']));
        }
}

function MF_PHP_Array_filter_callback($value)
{
        if ($value > 5)
                return true;

        return false;
}

?>