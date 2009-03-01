CREATE TABLE auditTrail (
  auditId integer PRIMARY KEY AUTOINCREMENT,
  eventId integer,
  pageGroupId integer,
  userId integer,
  pageId integer,
  eventDesc text,
  eventTimestamp integer,
  eventStatus integer,
  recordTableId integer,
  recordId integer,
  tracerId text,
  ipAddress text
);

CREATE TABLE auditTrailHistory (
  uid integer PRIMARY KEY AUTOINCREMENT,
  tableid integer,
  tableuid integer,
  auditId integer
);


CREATE TABLE auditTrailRelations (
  uid integer PRIMARY KEY AUTOINCREMENT,
  fromTableId integer,
  fromRecordId integer,
  toTableId integer,
  toRecordId integer,
  auditId integer,
  relationship text
);


CREATE TABLE customerHistory (
  historyUid integer PRIMARY KEY AUTOINCREMENT,
  customerId integer,
  customerFirstName text,
  customerSurname text,
  customerAddress1 text,
  customerAddress2 text,
  customerCity text,
  customerCounty text,
  customerCountry text,
  customerPostcode text,
  customerEmailAddress text,
  customerPassword text,
  dateCreated integer
);


CREATE TABLE customers (
  customerId integer PRIMARY KEY AUTOINCREMENT,
  customerFirstName text,
  customerSurname text,
  customerAddress1 text,
  customerAddress2 text,
  customerCity text,
  customerCounty text,
  customerCountry text,
  customerPostcode text,
  customerEmailAddress text,
  customerPassword text,
  dateCreated integer
);

INSERT INTO customers (customerId, customerFirstName, customerSurname, customerAddress1, customerAddress2, customerCity, customerCounty, customerCountry, customerPostcode, customerEmailAddress, customerPassword, dateCreated) VALUES (1, 'Stuart', 'Herbert', '123 Example Road', NULL, 'Example City', 'Example County', 'UK', 'CF10 2GE', 'stuart@example.com', NULL, 0);
INSERT INTO customers (customerId, customerFirstName, customerSurname, customerAddress1, customerAddress2, customerCity, customerCounty, customerCountry, customerPostcode, customerEmailAddress, customerPassword, dateCreated) VALUES (2, 'ExampleFirstName2', 'ExampleSurname2', '234 Example Road', 'Example Address 2', 'Example City 2', 'Example County 2', 'UK', 'Example Postcode 2', 'example2@example.com', NULL, 0);


CREATE TABLE notes (
  noteuid integer PRIMARY KEY AUTOINCREMENT,
  recordTableId integer,
  recordId integer,
  notetext text,
  notetimestamp integer
);


CREATE TABLE orderHistory (
  historyUid integer PRIMARY KEY AUTOINCREMENT,
  customerId integer,
  orderId integer,
  orderStatus integer,
  orderTotal float,
  orderPostage float,
  orderStatusChange integer,
  dateCreated integer
);


CREATE TABLE orderContents (
  uid integer PRIMARY KEY AUTOINCREMENT,
  masterOrderId integer,
  pid integer,
  quantity integer,
  cost float
);


INSERT INTO orderContents (uid, masterOrderId, pid, quantity, cost) VALUES (1, 1, 1, 5, 8.99);
INSERT INTO orderContents (uid, masterOrderId, pid, quantity, cost) VALUES (2, 1, 4, 20, 50.99);


CREATE TABLE ordercontentsHistory (
  historyUid integer PRIMARY KEY AUTOINCREMENT,
  uid integer,
  masterOrderId integer,
  pid integer,
  quantity integer,
  cost float
);


CREATE TABLE orders (
  masterCustomerId integer,
  giftCustomerId integer,
  orderId integer PRIMARY KEY AUTOINCREMENT,
  orderStatus integer,
  orderTotal float,
  orderPostage float,
  orderStatusChange integer,
  dateCreated integer
);

INSERT INTO orders (masterCustomerId, giftCustomerId, orderId, orderStatus, orderTotal, orderPostage, orderStatusChange, dateCreated) VALUES (1, 2, 1, 1, 8.59, 0, 2006, 2006);
INSERT INTO orders (masterCustomerId, giftCustomerId, orderId, orderStatus, orderTotal, orderPostage, orderStatusChange, dateCreated) VALUES (1, 2, 2, 2, 99.99, 5.99, 1970, 2006);


CREATE TABLE pluginConstants (
  uid integer PRIMARY KEY AUTOINCREMENT,
  package text,
  constType text,
  constName text,
  constDesc text
);


CREATE TABLE products (
  pid integer PRIMARY KEY AUTOINCREMENT,
  productName text,
  productSummary text,
  productUrl text,
  productCode text,
  productCost float,
  isActive integer
);

INSERT INTO products (pid, productName, productSummary, productUrl, productCode, productCost, isActive) VALUES (1, 'Gentoo LAMP Server', 'A Linux/Apache/MySQL/PHP Stack for server environments', 'http://lamp.gentoo.org/server/', 'AA001', 15.99, 1);
INSERT INTO products (pid, productName, productSummary, productUrl, productCode, productCost, isActive) VALUES (2, 'Gentoo LAMP Developer Desktop', 'A developer''s workstation w/ the LAMP stack', 'http://lamp.gentoo.org/client/', 'AA002', 9.99, 1);
INSERT INTO products (pid, productName, productSummary, productUrl, productCode, productCost, isActive) VALUES (3, 'Gentoo Overlays', 'Per-team package trees for Gentoo', 'http://overlays.gentoo.org/', 'AA003', 5.99, 1);
INSERT INTO products (pid, productName, productSummary, productUrl, productCode, productCost, isActive) VALUES (4, 'Gentoo/ALT', 'Gentoo package management on non-Linux kernels', 'http://alt.gentoo.org/', 'AA004', 3.99, 1);


CREATE TABLE productsHistory (
  historyId integer PRIMARY KEY AUTOINCREMENT,
  pid integer,
  productName text,
  productSummary text,
  productUrl text,
  productCode text,
  productCost float,
  isActive integer
);


CREATE TABLE relatedProducts (
  uid integer PRIMARY KEY AUTOINCREMENT,
  productId1 integer,
  productId2 integer
);

INSERT INTO relatedProducts (uid, productId1, productId2 ) VALUES (1, 1, 2);
INSERT INTO relatedProducts (uid, productId1, productId2 ) VALUES (2, 1, 3);
INSERT INTO relatedProducts (uid, productId1, productId2 ) VALUES (3, 1, 4);


CREATE TABLE relations (
  uid integer PRIMARY KEY AUTOINCREMENT,
  fromTableId integer,
  fromRecordId integer,
  toTableId integer,
  toRecordId integer,
  relationship integer,
  fromTimestamp integer,
  toTimestamp integer
);


CREATE TABLE users (
  userId integer PRIMARY KEY AUTOINCREMENT,
  username text,
  password text,
  isActive integer,
  emailAddress text,
  adminUser integer
);