<?php

// ========================================================================
//
// Datastore/Datastore.tests.php
//              PHPUnit tests for the Datastore component
//
//              Part of the Methodosity Framework for applications
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
// 2007-08-12   SLH     Consolidated from separate files
// 2008-05-22   SLH     Updated to support Models being separate from
//                      Datastore_Records
//                      Updated to use Datastore_Query instead of
//                      Datastore_Table
// 2008-06-19   SLH     Removed explicit definition of records
//                      (Datastore_Record now automatically encapsulates
//                      the model)
// 2008-07-28   SLH     Moved definition of models into setup()
// 2008-08-07   SLH     Removed setTable() calls from the test models
// 2008-08-07   SLH     Added support for per-datastore storage of each
//                      model
// 2008-08-13   SLH     Added testCanRetrieveAllRowsFromATable() to
//                      DatastoreXXX_Query_Tests
// 2009-03-18   SLH     Added tests to trap errors introduced by support
//                      for complex primary keys
// ========================================================================

// ========================================================================
// Test models for use in the test scripts
// ------------------------------------------------------------------------

class Test_Customer extends Model
{
}

class Test_Order extends Model
{
}


class Test_OrderContent extends Model
{

}

class Test_Product extends Model
{

}

class Test_RelatedProducts extends Model
{

}

function defineDatastoreTestModels()
{
        Model_Definitions::destroy();

        $oMeta = Model_Definitions::get('Test_Customer');
        $oMeta->addField('customerId')
              ->asMandatory();
        $oMeta->addField('customerFirstName');
        $oMeta->addField('customerSurname');
        $oMeta->addField('customerAddress1');
        $oMeta->addField('customerAddress2');
        $oMeta->addField('customerCounty');
        $oMeta->addField('customerCity');
        $oMeta->addField('customerCountry');
        $oMeta->addField('customerPostcode');
        $oMeta->addField('customerEmailAddress');
        $oMeta->setPrimaryKey('customerId');

        $oMeta->addView('name')
              ->withField('customerId')
              ->withField('customerFirstName')
              ->withField('customerSurname');


/*
        $oMeta = Model_Definitions::get('Test_PremiumCustomer');
        $oMeta->inheritsFrom('customer');
        $oMeta->addField('customerTerms');
*/

        $oMeta = Model_Definitions::get('Test_Order');
        $oMeta->addField('masterCustomerId');
        $oMeta->addField('giftCustomerId');
        $oMeta->addField('orderId');
        $oMeta->addField('orderStatus');
        $oMeta->addField('orderTotal');
        $oMeta->addField('orderPostage');
        $oMeta->addField('orderStatusChange');
        $oMeta->addField('dateCreated');
        $oMeta->setPrimaryKey('orderId');

        $oMeta->hasOne('customer')
              ->ourFieldIs('masterCustomerId')
              ->theirModelIs('Test_Customer')
              ->theirFieldIs('customerId');

        $oMeta->hasOne('giftRecipient')
              ->ourFieldIs('giftCustomerId')
              ->theirModelIs('Test_Customer')
              ->theirFieldIs('customerId');

        $oMeta->hasMany('lineItems')
              ->ourFieldIs('orderId')
              ->theirModelIs('Test_OrderContent')
              ->theirFieldIs('masterOrderId');


        $oMeta = Model_Definitions::get('Test_OrderContent');
        $oMeta->addField('uid');
        $oMeta->addField('masterOrderId');
        $oMeta->addField('pid');
        $oMeta->addField('quantity');
        $oMeta->addField('cost');
        $oMeta->setPrimaryKey('uid');
        $oMeta->hasOne('order')
              ->ourFieldIs('masterOrderId')
              ->theirModelIs('Test_Order')
              ->theirFieldIs('orderId');
        $oMeta->hasOne('product')
              ->ourFieldIs('pid')
              ->theirModelIs('Test_Product')
              ->theirFieldIs('pid');


        $oMeta = Model_Definitions::get('Test_Product');
        $oMeta->addField('pid');
        $oMeta->addField('productName');
        $oMeta->addField('productSummary');
        $oMeta->addField('productUrl');
        $oMeta->addField('productCode');
        $oMeta->addField('productCost');
        $oMeta->addField('isActive');
        $oMeta->setPrimaryKey('pid');

/*
        $oMeta->sharesMany('relatedProducts')
              ->ourFieldIs('pid')
              ->theirModelIs('Test_RelatedProducts', 'relatedProducts')
              ->theirFieldIs('productId1');
*/

        $oDef = Model_Definitions::get('Test_RelatedProducts');
        $oDef->addField('productId1');
        $oDef->addField('productId2');
        $oDef->addField('uid');
        $oDef->setPrimaryKey('uid');
        $oDef->hasMany('relatedProducts')
             ->ourFieldIs('productId2')
             ->theirModelIs('Test_Product')
             ->theirFieldIs('pid');
}

function defineDatastoreTestStorage_RDBMS(Datastore $oDB)
{
        // define the storage for the models
        $oDB->storeModel('Test_Customer')
            ->inTable('customers');

/*
        $oDB->storeModel('Test_PremiumCustomer')
            ->inTable('premiumCustomers');
 */

        $oDB->storeModel('Test_Order')
            ->inTable('orders');

        $oDB->storeModel('Test_OrderContent')
            ->inTable('orderContents');

        $oDB->storeModel('Test_Product')
            ->inTable('products');

        $oDB->storeModel('Test_RelatedProducts')
            ->inTable('relatedProducts');
}

// ========================================================================
// Generic tests, designed to be re-used against multiple types of
// datastore
// ------------------------------------------------------------------------

class DatastoreXXX_Record_Tests extends PHPUnit_Framework_TestCase
{
        public function testCanRetrieveByPrimaryKey()
        {
//                $this->setup();

                // echo __FUNCTION__ . "\n";

                try
                {
                        $this->fixture->retrieve($this->db, 1);
                }
                catch (Exception $e)
                {
                        $this->fail($e->getMessage());
                }

                $this->assertEquals(1, (int) $this->fixture->customerId);
                $this->assertEquals('Stuart', $this->fixture->customerFirstName);
        }

        public function testCanReadContentsAsAttributes()
        {
//                $this->setup();

                // echo __FUNCTION__ . "\n";

                try
                {
                        $this->fixture->retrieve($this->db, 1);
                }
                catch (Exception $e)
                {
                        $this->fail($e->getMessage());
                }

                $this->assertType('string', $this->fixture->customerFirstName);
        }

        public function testCanRetrieveForeignKeys()
        {
                $order = new Datastore_Record('Test_Order');
                $order->retrieve($this->db, 1);

                // make sure we have the record
                $this->assertEquals(1, $order->orderId);
                $this->assertEquals(1, $order->masterCustomerId);

                // now, let's get the relationship between order and
                // customer

                $oRelationship = $order->oModel->getDefinition()->getRelationship('customer');
                $this->assertTrue ($oRelationship instanceof Model_Relationship);

                // make sure the relationship tells us what we expect
                $this->assertEquals ($oRelationship->getOurFields(), array('masterCustomerId'));

                // make sure we get the right thing back when testing
                // the relationship
                $fields = $order->getFields($oRelationship->getOurFields());
                $this->assertEquals(1, count($fields));
        }
        
        public function testCanRetrieveRelatedRecord()
        {
//                $this->setup();

                // echo __FUNCTION__ . "\n";

                $order = new Datastore_Record('Test_Order');
                $order->retrieve($this->db, 1);

                // make sure that the record has been retrieved
                $this->assertEquals(1, $order->orderId);
                $this->assertEquals(1, $order->masterCustomerId);

                $customer = $order->retrieve_customer($this->db);

                $this->assertTrue($customer instanceof Datastore_Record);
                $this->assertEquals(1, (int) $customer->customerId);

                $customer = $order->retrieve_giftRecipient($this->db);

                $this->assertTrue($customer instanceof Datastore_Record);
                $this->assertEquals(2, (int) $customer->customerId);
        }

        public function testCanStoreRecord()
        {
//                $this->setup();

                // echo __FUNCTION__ . "\n";

                $customer = new Datastore_Record('Test_Customer');
                $customer->customerId = 1000;
                $customer->customerFirstName    = 'Fred';
                $customer->customerSurname      = 'Bloggs';
                $customer->customerAddress1     = '123 Example Street';
                $customer->customerCity         = 'Example City';
                $customer->customerPostcode     = 'EX1 AM23';
                $customer->customerCountry      = 'Example Country';
                $customer->customerEmailAddress = 'fred.bloggs@example.com';

                $customer->store($this->db);

                $customer2 = new Datastore_Record('Test_Customer');
                $customer2->retrieve($this->db, 1000);

                $this->assertEquals(1000,     (int) $customer2->customerId);
                $this->assertEquals('Fred',   $customer2->customerFirstName);
                $this->assertEquals('Bloggs', $customer2->customerSurname);
        }

        public function testCanUpdateRecord()
        {
//                $this->setup();

                // echo __FUNCTION__ . "\n";

                $this->fixture->retrieve($this->db, 1);
                $this->assertTrue($this->fixture->hasData());
                $this->assertEquals('Stuart', $this->fixture->customerFirstName);

                $this->fixture->customerFirstName = 'Kristi';
                $this->assertTrue($this->fixture->getNeedSave());
                $this->fixture->store();

                $oCustomer = new Datastore_Record('Test_Customer');
                $this->assertFalse($oCustomer->hasData());

                $oCustomer->retrieve($this->db, 1);
                $this->assertEquals('Kristi', $oCustomer->customerFirstName);

                $oCustomer->customerFirstName = 'Spike';
                $oCustomer->store();

                $oCustomer2 = new Datastore_Record('Test_Customer');
                $oCustomer2->retrieve($this->db, 1);
                $this->assertEquals('Spike', $oCustomer2->customerFirstName);
        }

        public function testCanDeleteRecord()
        {
//                $this->setup();

                // echo __FUNCTION__ . "\n";

                $this->fixture->retrieve($this->db, 1);
                $this->assertTrue($this->fixture->hasData());
                $this->assertEquals('Stuart', $this->fixture->customerFirstName);

                $this->fixture->delete();

                $oCustomer = new Datastore_Record('Test_Customer');
                $this->assertFalse($oCustomer->hasData());

                $excepted = false;
                try
                {
                        $oCustomer->retrieve($this->db, 1);
                }
                catch (Datastore_E_RetrieveFailed $e)
                {
                        $excepted = true;
                }

                $this->assertTrue($excepted);
                $this->assertFalse($oCustomer->hasData());
        }

        public function testCanRetrieveRelatedRecordset()
        {
                // echo __FUNCTION__ . "\n";

                $oOrder = new Datastore_Record('Test_Order');
                $oOrder->retrieve($this->db, 1);

                $this->assertTrue($oOrder->hasData());

//                $oLineItems = $oOrder->retrieve_lineItems($this->db);
//                $this->assertTrue($oLineItems instanceof Datastore_Records);
//                $this->assertEquals(2, $oLineItems->getCount());

                $aLineItems = $oOrder->retrieve_lineItems($this->db);

                $this->assertTrue(is_array($aLineItems));
                $this->assertEquals(2, count($aLineItems));
        }

        public function testCanRetrieveRelatedRecordWithView()
        {
                // echo __FUNCTION__ . "\n";

                $oOrder = new Datastore_Record('Test_Order');
                $oOrder->retrieve($this->db, 1);

                $this->assertTrue($oOrder->hasData());

                // step 1: prove that, by default, we get information
                //         that isn't in the view

                $oCustomer = $oOrder->retrieve_customer($this->db);
                $this->assertTrue($oCustomer instanceof Datastore_Record);
                $this->assertEquals(1, $oCustomer->getUniqueId());
                $this->assertEquals('Stuart', $oCustomer->customerFirstName);
                $this->assertEquals('Herbert', $oCustomer->customerSurname);
                $this->assertTrue(isset($oCustomer->customerAddress1));

                // step 2: now prove that the view only has a subset of
                //         the information

                $GLOBALS['VAR_DUMP'] = true;
                $oCustomer = $oOrder->retrieve_customer_name($this->db);
                $GLOBALS['VAR_DUMP'] = false;
                $this->assertTrue($oCustomer instanceof Datastore_Record);
                $this->assertEquals(1, $oCustomer->getUniqueId());
                $this->assertEquals('Stuart', $oCustomer->customerFirstName);
                $this->assertEquals('Herbert', $oCustomer->customerSurname);
                $this->assertFalse(isset($oCustomer->customerAddress1));
        }
}


class DatastoreXXX_Query_Tests extends PHPUnit_Framework_TestCase
{
        public function testCanRetrieveAllRowsFromATable()
        {
        	$customers = $this->db->newQuery()
                           ->findEvery('Test_Customer')
                           ->go();

                foreach ($customers as $customer)
                {
                	$this->assertTrue($customer instanceof Datastore_Record);
                        $this->assertTrue($customer->oModel instanceof Test_Customer);
                }

                $this->assertEquals
                (
                        array (
                                'customerId'            => 1,
                                'customerFirstName'     => 'Stuart',
                                'customerSurname'       => 'Herbert',
                                'customerAddress1'      => '123 Example Road',
                                'customerCounty'        => 'Example County',
                                'customerCity'          => 'Example City',
                                'customerCountry'       => 'UK',
                                'customerPostcode'      => 'CF10 2GE',
                                'customerEmailAddress'  => 'stuart@example.com',
                        ),
                        $customers[0]->getData()
                );

                $this->assertEquals
                (
                        array (
                                'customerId'            => 2,
                                'customerFirstName'     => 'ExampleFirstName2',
                                'customerSurname'       => 'ExampleSurname2',
                                'customerAddress1'      => '234 Example Road',
                                'customerAddress2'      => 'Example Address 2',
                                'customerCounty'        => 'Example County 2',
                                'customerCity'          => 'Example City 2',
                                'customerCountry'       => 'UK',
                                'customerPostcode'      => 'Example Postcode 2',
                                'customerEmailAddress'  => 'example2@example.com',
                        ),
                        $customers[1]->getData()
                );
        }

        public function testCanRetrieveByPrimaryKey()
        {
//                $this->setup();

                // echo __FUNCTION__ . "\n";

                $custQ = $this->db->newQuery()
                       ->findFirst('Test_Customer')
                       ->withUniqueID(1);

                $customer = $this->db->search($custQ);
                $this->assertTrue($customer instanceof Datastore_Record);
                $this->assertTrue($customer->oModel instanceof Test_Customer);
                $this->assertEquals(1, (int) $customer->customerId);
        }

        public function testCanRetrieveByASpecifiedField()
        {
                // echo __FUNCTION__ . "\n";

                $orderQ = $this->db->newQuery()
                        ->findEvery('Test_Order')
                        ->withForeignKey('masterCustomerId', 1);

                $orders = $this->db->search($orderQ);

                $this->assertType('array', $orders);
                $this->assertEquals(2, count($orders));

                // check the orders are correct types
                foreach ($orders as $order)
                {
                        $this->assertTrue($order instanceof Datastore_Record);
                        $this->assertTrue($order->oModel instanceof Test_Order);
                }
        }

        public function testCanRetrieveRecordsUsingOrOperator()
        {
//                $this->setup();

                // echo __FUNCTION__ . "\n";

                $products = $this->db->newQuery()
                          ->findEvery('Test_Product')
                          ->matchingExpression("productCost < 7.99 or productCost > 12.99")
                          ->go();

                $this->assertType('array', $products);
                $this->assertEquals(3, count($products));

                foreach ($products as $product)
                {
                        $this->assertTrue($product->oModel instanceof Test_Product);
                }
        }

        public function testCanRetrieveRecordsUsingLikeOperator()
        {
//                $this->setup();

                // echo __FUNCTION__ . "\n";

                $customerQ = $this->db->newQuery()
                           ->findEvery('Test_Customer')
                           ->matchingExpression("customerSurname LIKE '%herb%'");

                $customers = $this->db->search($customerQ);
                $this->assertType('array', $customers);
                $this->assertEquals(1, count($customers));

                foreach ($customers as $customer)
                {
                        $this->assertTrue($customer->oModel instanceof Test_Customer);
                }
        }

        public function testCanRetrieveFromARecord ()
        {
//        	$this->setup();

                // echo __FUNCTION__ . "\n";

                $oOrder = $this->db->newQuery()
                       ->findFirst('Test_Order')
                       ->withUniqueId(1)
                       ->go();

                $this->assertEquals(1, $oOrder->orderId);

                $orders = $this->db->newQuery()
                        ->findAll('lineItems', $oOrder)
                        ->go();

                $this->assertEquals
                (
                        array
                        (
                                'uid'                   => '1',
                                'masterOrderId'         => '1',
                                'pid'                   => '1',
                                'quantity'              => '5',
                                'cost'                  => '8.99',
                        ),
                        $orders[0]->getData()
                );

                $this->assertEquals
                (
                        array
                        (
                                'uid'                   => '2',
                                'masterOrderId'         => '1',
                                'pid'                   => '4',
                                'quantity'              => '20',
                                'cost'                  => '50.99',
                        ),
                        $orders[1]->getData()
                );

        }

        public function testCanRetrieveFromTwoTables()
        {
                // echo __FUNCTION__ . "\n";

                $oOrder = $this->db->newQuery()
                          ->findFirst('Test_Order')
                          ->withUniqueId(1)
                          ->go();

                $aOrderContents = $this->db->newQuery()
                                 ->findAll('lineItems', $oOrder)
                                 ->including('product')
                                 ->go();

                $orderContent = $aOrderContents[0]['Test_OrderContent'];
                $product      = $aOrderContents[0]['Test_Product'];
                $raw          = $aOrderContents[0]['__raw'];

                $this->assertEquals
                (
                        array
                        (
                                'uid'                   => '1',
                                'masterOrderId'         => '1',
                                'pid'                   => '1',
                                'quantity'              => '5',
                                'cost'                  => '8.99',
                                'productName'           => 'Gentoo LAMP Server',
                                'productSummary'        => 'A Linux/Apache/MySQL/PHP Stack for server environments',
                                'productUrl'            => 'http://lamp.gentoo.org/server/',
                                'productCode'           => 'AA001',
                                'productCost'           => '15.99',
                                'isActive'              => '1',
                        ),
                        $raw
                );

                $this->assertEquals
                (
                        array
                        (
                                'uid'                   => '1',
                                'masterOrderId'         => '1',
                                'pid'                   => '1',
                                'quantity'              => '5',
                                'cost'                  => '8.99',
                        ),
                        $orderContent->getData()
                );

                $this->assertEquals
                (
                        array
                        (
                                'pid'                   => 1,
                                'productName'           => 'Gentoo LAMP Server',
                                'productSummary'        => 'A Linux/Apache/MySQL/PHP Stack for server environments',
                                'productUrl'            => 'http://lamp.gentoo.org/server/',
                                'productCode'           => 'AA001',
                                'productCost'           => '15.99',
                                'isActive'              => '1',
                        ),
                        $product->getData()
                );

                $orderContent = $aOrderContents[1]['Test_OrderContent'];
                $product      = $aOrderContents[1]['Test_Product'];
                $raw          = $aOrderContents[1]['__raw'];

                $this->assertEquals
                (
                        array
                        (
                                'uid'                   => '2',
                                'masterOrderId'         => '1',
                                'pid'                   => '4',
                                'quantity'              => '20',
                                'cost'                  => '50.99',
                                'productName'           => 'Gentoo/ALT',
                                'productSummary'        => 'Gentoo package management on non-Linux kernels',
                                'productUrl'            => 'http://alt.gentoo.org/',
                                'productCode'           => 'AA004',
                                'productCost'           => '3.99',
                                'isActive'              => '1',
                        ),
                        $raw
                );

                $this->assertEquals
                (
                        array
                        (
                                'uid'                   => '2',
                                'masterOrderId'         => '1',
                                'pid'                   => '4',
                                'quantity'              => '20',
                                'cost'                  => '50.99',
                        ),
                        $orderContent->getData()
                );

                $this->assertEquals
                (
                        array
                        (
                                'pid'                   => '4',
                                'productName'           => 'Gentoo/ALT',
                                'productSummary'        => 'Gentoo package management on non-Linux kernels',
                                'productUrl'            => 'http://alt.gentoo.org/',
                                'productCode'           => 'AA004',
                                'productCost'           => '3.99',
                                'isActive'              => '1',
                        ),
                        $product->getData()
                );
        }

        public function testCanRetrieveUsingRawSql()
        {
                // echo __FUNCTION__ . "\n";

                $aOrderContents = $this->db->newQuery()
                                  ->findRaw('select ordercontents.*, products.* from ordercontents inner join products on products.pid = ordercontents.pid where masterOrderId = ? order by uid asc',
                                            'uid', array('1'))
                                  ->extractInto('Test_OrderContent')
                                  ->extractInto('Test_Product')
                                  ->go();

                $orderContent = $aOrderContents[0]['Test_OrderContent'];
                $product      = $aOrderContents[0]['Test_Product'];
                $raw          = $aOrderContents[0]['__raw'];

                $this->assertEquals
                (
                        array
                        (
                                'uid'                   => '1',
                                'masterOrderId'         => '1',
                                'pid'                   => '1',
                                'quantity'              => '5',
                                'cost'                  => '8.99',
                                'productName'           => 'Gentoo LAMP Server',
                                'productSummary'        => 'A Linux/Apache/MySQL/PHP Stack for server environments',
                                'productUrl'            => 'http://lamp.gentoo.org/server/',
                                'productCode'           => 'AA001',
                                'productCost'           => '15.99',
                                'isActive'              => '1',
                        ),
                        $raw
                );

                $this->assertEquals
                (
                        array
                        (
                                'uid'                   => '1',
                                'masterOrderId'         => '1',
                                'pid'                   => '1',
                                'quantity'              => '5',
                                'cost'                  => '8.99',
                        ),
                        $orderContent->getData()
                );

                $this->assertEquals
                (
                        array
                        (
                                'pid'                   => 1,
                                'productName'           => 'Gentoo LAMP Server',
                                'productSummary'        => 'A Linux/Apache/MySQL/PHP Stack for server environments',
                                'productUrl'            => 'http://lamp.gentoo.org/server/',
                                'productCode'           => 'AA001',
                                'productCost'           => '15.99',
                                'isActive'              => '1',
                        ),
                        $product->getData()
                );

                $orderContent = $aOrderContents[1]['Test_OrderContent'];
                $product      = $aOrderContents[1]['Test_Product'];
                $raw          = $aOrderContents[1]['__raw'];

                $this->assertEquals
                (
                        array
                        (
                                'uid'                   => '2',
                                'masterOrderId'         => '1',
                                'pid'                   => '4',
                                'quantity'              => '20',
                                'cost'                  => '50.99',
                                'productName'           => 'Gentoo/ALT',
                                'productSummary'        => 'Gentoo package management on non-Linux kernels',
                                'productUrl'            => 'http://alt.gentoo.org/',
                                'productCode'           => 'AA004',
                                'productCost'           => '3.99',
                                'isActive'              => '1',
                        ),
                        $raw
                );

                $this->assertEquals
                (
                        array
                        (
                                'uid'                   => '2',
                                'masterOrderId'         => '1',
                                'pid'                   => '4',
                                'quantity'              => '20',
                                'cost'                  => '50.99',
                        ),
                        $orderContent->getData()
                );

                $this->assertEquals
                (
                        array
                        (
                                'pid'                   => '4',
                                'productName'           => 'Gentoo/ALT',
                                'productSummary'        => 'Gentoo package management on non-Linux kernels',
                                'productUrl'            => 'http://alt.gentoo.org/',
                                'productCode'           => 'AA004',
                                'productCost'           => '3.99',
                                'isActive'              => '1',
                        ),
                        $product->getData()
                );
        }

}

/*
// ========================================================================
// Tests done against a non-PDO database
// ------------------------------------------------------------------------

// ========================================================================
// Tests against the current session
// ------------------------------------------------------------------------

registerTests('DatastoreSession_Record_Tests');
class DatastoreSession_Record_Tests extends DatastoreXXX_Record_Tests
{
        public function setup ()
        {
                createTestArrayDatabase($_SESSION['DatastoreSession']);

                $oConn         = new Datastore_Array_Connector($_SESSION['DatastoreSession']);
                $this->db      = new Datastore($oConn);
                $this->fixture = new Test_Customer_Record();
        }
}

registerTests('DatastoreSession_Table_Tests');
class DatastoreSession_Table_Tests extends DatastoreXXX_Table_Tests
{
        public function setup ()
        {
                createTestArrayDatabase($_SESSION['DatastoreSession']);

                $oConn         = new Datastore_Array_Connector($_SESSION['DatastoreSession']);
                $this->db      = new Datastore($oConn);
        }
}

registerTests('DatastoreSession_ListQuery_Tests');
class DatastoreSession_ListQuery_Tests extends DatastoreXXX_ListQuery_Tests
{
        public function setup ()
        {
                createTestArrayDatabase($_SESSION['DatastoreSession']);

                $oConn         = new Datastore_Array_Connector($_SESSION['DatastoreSession']);
                $this->db      = new Datastore($oConn);
        }
}

function createTestArrayDatabase(&$data)
{
        $data = array();

        $data['customers'][1] = array
        (
                'customerId'            => 1,
                'customerFirstName'     => 'Stuart',
                'customerSurname'       => 'Herbert',
                'customerAddress1'      => '123 Example Road',
                'customerAddress2'      => NULL,
                'customerCity'          => 'Example City',
                'customerCounty'        => 'Example County',
                'customerCountry'       => 'UK',
                'customerPostcode'      => 'CF10 2GE',
                'customerEmailAddress'  => 'stuart@example.com',
        );

        $data['customers'][2] = array
        (
                'customerId'            => 2,
                'customerFirstName'     => 'ExampleFirstName2',
                'customerSurname'       => 'ExampleSurname2',
                'customerAddress1'      => '234 Example Road',
                'customerAddress2'      => 'Example Address 2',
                'customerCity'          => 'Example City 2',
                'customerCounty'        => 'Example County 2',
                'customerCountry'       => 'UK',
                'customerPostcode'      => 'Example Postcode 2',
                'customerEmailAddress'  => 'example2@example.com',
        );

        $data['ordercontents'][1] = array
        (
                'uid'           => 1,
                'masterOrderId' => 1,
                'pid'           => 1,
                'quantity'      => 5,
                'cost'          => 8.99
        );

        $data['ordercontents'][2] = array
        (
                'uid'           => 2,
                'masterOrderId' => 1,
                'pid'           => 4,
                'quantity'      => 20,
                'cost'          => 50.99
        );

        $data['orders'][1] = array
        (
                'masterCustomerId'      => 1,
                'giftCustomerId'        => 2,
                'orderId'               => 1,
                'orderStatus'           => 1,
                'orderTotal'            => 8.59,
                'orderPostage'          => 0,
                'orderStatusChange'     => 2006,
        );

        $data['orders'][2] = array
        (
                'masterCustomerId'      => 1,
                'giftCustomerId'        => 2,
                'orderId'               => 2,
                'orderStatus'           => 2,
                'orderTotal'            => 99.99,
                'orderPostage'          => 5.99,
                'orderStatusChange'     => 1970,
        );

        $data['products'][1] = array
        (
                'pid'                   => 1,
                'productName'           => 'Gentoo LAMP Server',
                'productSummary'        => 'A Linux/Apache/MySQL/PHP Stack for server environments',
                'productUrl'            => 'http://lamp.gentoo.org/server/',
                'productCode'           => 'AA001',
                'productCost'           => 15.99,
                'isActive'              => 1,
        );

        $data['products'][2] = array
        (
                'pid'                   => 2,
                'productName'           => 'Gentoo LAMP Developer Desktop',
                'productSummary'        => 'A developer\'s workstation w/ the LAMP stack',
                'productUrl'            => 'http://lamp.gentoo.org/client/',
                'productCode'           => 'AA002',
                'productCost'           => 9.99,
                'isActive'              => 1,
        );

        $data['products'][3] = array
        (
                'pid'                   => 3,
                'productName'           => 'Gentoo Overlays',
                'productSummary'        => 'Per-team package trees for Gentoo',
                'productUrl'            => 'http://overlays.gentoo.org/',
                'productCode'           => 'AA003',
                'productCost'           => 5.99,
                'isActive'              => 1,
        );

        $data['products'][4] = array
        (
                'pid'                   => 4,
                'productName'           => 'Gentoo/ALT',
                'productSummary'        => 'Gentoo package management on non-Linux kernels',
                'productUrl'            => 'http://alt.gentoo.org/',
                'productCode'           => 'AA004',
                'productCost'           => 3.99,
                'isActive'              => 1,
        );

        $data['relatedProducts'][1] = array
        (
                'uid'                   => 1,
                'productId1'            => 1,
                'productId2'            => 2,
        );

        $data['relatedProducts'][2] = array
        (
                'uid'                   => 2,
                'productId1'            => 1,
                'productId2'            => 3,
        );

        $data['relatedProducts'][3] = array
        (
                'uid'                   => 1,
                'productId1'            => 1,
                'productId2'            => 4,
        );
}

*/

?>