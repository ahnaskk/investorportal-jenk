/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
SET NAMES utf8mb4;


LOCK TABLES `industries` WRITE;
/*!40000 ALTER TABLE `industries` DISABLE KEYS */;

INSERT INTO `industries` (`created_at`, `updated_at`, `id`, `name`)
VALUES
	(NULL,NULL,1,'Agriculture and Mining other'),
	(NULL,NULL,2,'Business Services'),
	(NULL,NULL,3,'Computer and Electronics'),
	(NULL,NULL,4,'Consumer Services'),
	(NULL,NULL,5,'Education'),
	(NULL,NULL,6,'Energy and Utilities'),
	(NULL,NULL,7,'Financial Services'),
	(NULL,NULL,8,'Health, Pharmaceuticals, and Biotech'),
	(NULL,NULL,9,'Manufacturing'),
	(NULL,NULL,10,'Media and Entertainment'),
	(NULL,NULL,11,'Real Estate and Construction'),
	(NULL,NULL,12,'Software and Internet'),
	(NULL,NULL,13,'Telecommunications'),
	(NULL,NULL,14,'Transportation and Storage'),
	(NULL,NULL,15,'Travel Recreation and Leisure'),
	(NULL,NULL,16,'Wholesale and Distribution'),
	(NULL,NULL,17,'construction'),
	(NULL,NULL,18,'Retail'),
	(NULL,NULL,19,'Auto repair'),
	(NULL,NULL,20,'Accounting and Tax Preparation'),
	(NULL,NULL,21,'Advertising, Marketing and PR'),
	(NULL,NULL,22,'Alcoholic Beverages'),
	(NULL,NULL,23,'Alternative Energy Sources'),
	(NULL,NULL,24,'Audio, Video and Photography'),
	(NULL,NULL,25,'Automobiles, Boats and Motor Vehicles'),
	(NULL,NULL,26,'Automotive Sales'),
	(NULL,NULL,27,'Biotechnology'),
	(NULL,NULL,28,'Chemicals and Petrochemicals'),
	(NULL,NULL,29,'Computers, Parts and Repair'),
	(NULL,NULL,30,'Concrete, Glass and Building Materials'),
	(NULL,NULL,31,'Consumer Electronics, Parts and Repair'),
	(NULL,NULL,32,'1111 Consumer Services'),
	(NULL,NULL,33,'Data and Records Management'),
	(NULL,NULL,34,'Diagnostic Laboratories'),
	(NULL,NULL,35,'Doctors and Health Care Practitioners'),
	(NULL,NULL,36,'Elementary and Secondary Schools'),
	(NULL,NULL,37,'Facilities Management and Maintenance'),
	(NULL,NULL,38,'Farming and Mining Machinery and Equipment'),
	(NULL,NULL,39,'Farming and Ranching'),
	(NULL,NULL,40,'Fishing, Hunting and Forestry and Logging'),
	(NULL,NULL,41,'Food and Dairy Product Manufacturing and Packaging'),
	(NULL,NULL,42,'Funeral Homes and Services'),
	(NULL,NULL,43,'Furniture Manufacturing'),
	(NULL,NULL,44,'Gasoline and Oil Refineries'),
	(NULL,NULL,45,'Hospitals'),
	(NULL,NULL,46,'HR and Recruiting Services'),
	(NULL,NULL,47,'IT and Network Services and Support'),
	(NULL,NULL,48,'Laundry and Dry Cleaning'),
	(NULL,NULL,49,'Legal Services'),
	(NULL,NULL,50,'Lending and Mortgage'),
	(NULL,NULL,51,'Libraries, Archives and Museums'),
	(NULL,NULL,52,'Management Consulting'),
	(NULL,NULL,53,'Medical Devices'),
	(NULL,NULL,54,'Medical Supplies and Equipment'),
	(NULL,NULL,55,'Metals Manufacturing'),
	(NULL,NULL,56,'Mining and Quarrying'),
	(NULL,NULL,57,'Network Security Products'),
	(NULL,NULL,58,'Networking equipment and Systems'),
	(NULL,NULL,59,'Nonalcoholic Beverages'),
	(NULL,NULL,60,'Office Machinery and Equipment'),
	(NULL,NULL,61,'Outpatient Care Centers'),
	(NULL,NULL,62,'Paper and Paper Products'),
	(NULL,NULL,63,'Parking Lots and Garage Management'),
	(NULL,NULL,64,'Payroll Services'),
	(NULL,NULL,65,'Personal Financial Planning and Private Banking'),
	(NULL,NULL,66,'Personal Health Care Products'),
	(NULL,NULL,67,'Pharmaceuticals'),
	(NULL,NULL,68,'Plastics and Rubber Manufacturing'),
	(NULL,NULL,69,'Residential and Long-term Care Facilities'),
	(NULL,NULL,70,'Retail - apparel & accessory Stores'),
	(NULL,NULL,71,'Retail - Auto Dealers & Gasoline Stations'),
	(NULL,NULL,72,'Retail - Computer & Computer software sales'),
	(NULL,NULL,73,'Retail - Convenience stores'),
	(NULL,NULL,74,'Retail - Drug Stores'),
	(NULL,NULL,75,'Retail - Eating & Drinking Places'),
	(NULL,NULL,76,'Retail - Furniture Stores'),
	(NULL,NULL,77,'Retail - Grocery Stores'),
	(NULL,NULL,78,'Retail - Jewelry Stores'),
	(NULL,NULL,79,'Retail - Mobile Home Dealers'),
	(NULL,NULL,80,'Retail - Restaurants '),
	(NULL,NULL,81,'Retail - Retail Stores'),
	(NULL,NULL,82,'Sales Services'),
	(NULL,NULL,83,'Security Services'),
	(NULL,NULL,84,'Semiconductor and Microchip Manufacturing'),
	(NULL,NULL,85,'Sports, Arts, and Recreation Instruction'),
	(NULL,NULL,86,'Technical and Trade Schools'),
	(NULL,NULL,87,'Test Preparation'),
	(NULL,NULL,88,'Textiles, Apparel and Accessories'),
	(NULL,NULL,89,'Tools, Hardware and Light Machinery'),
	(NULL,NULL,90,'Veterinary Clinics and Services'),
	(NULL,NULL,91,'Waste Management and Recycling'),
	(NULL,NULL,92,'Insurance');

/*!40000 ALTER TABLE `industries` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table interest
# ------------------------------------------------------------



# Dump of table investor_documents
# ------------------------------------------------------------



# Dump of table investor_transactions
# ------------------------------------------------------------



# Dump of table investors_offers
# ------------------------------------------------------------



# Dump of table jobs
# ------------------------------------------------------------



# Dump of table liquidity_log
# ------------------------------------------------------------



# Dump of table m_notes
# ------------------------------------------------------------



# Dump of table mailboxrows
# ------------------------------------------------------------



# Dump of table market_offers
# ------------------------------------------------------------



# Dump of table mbatch_merchant
# ------------------------------------------------------------



# Dump of table mbatches
# ------------------------------------------------------------



# Dump of table merchant_fund_details
# ------------------------------------------------------------



# Dump of table merchant_market_offers
# ------------------------------------------------------------



# Dump of table merchant_source
# ------------------------------------------------------------

LOCK TABLES `merchant_source` WRITE;
/*!40000 ALTER TABLE `merchant_source` DISABLE KEYS */;

INSERT INTO `merchant_source` (`created_at`, `updated_at`, `id`, `name`, `status`)
VALUES
	(NULL,NULL,1,'lender',1),
	(NULL,NULL,2,'retail',1),
	(NULL,NULL,3,'call in',1),
	(NULL,NULL,4,'ISO',1);

/*!40000 ALTER TABLE `merchant_source` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table merchant_status_log
# ------------------------------------------------------------



# Dump of table merchant_user
# ------------------------------------------------------------



# Dump of table merchants
# ------------------------------------------------------------



# Dump of table message_types
# ------------------------------------------------------------


# Dump of table modules
# ------------------------------------------------------------



# Dump of table oauth_access_tokens
# ------------------------------------------------------------



# Dump of table oauth_auth_codes
# ------------------------------------------------------------



# Dump of table oauth_clients
# ------------------------------------------------------------



# Dump of table oauth_personal_access_clients
# ------------------------------------------------------------



# Dump of table oauth_refresh_tokens
# ------------------------------------------------------------



# Dump of table participent_payments
# ------------------------------------------------------------



# Dump of table password_resets
# ------------------------------------------------------------



# Dump of table payment_investors
# ------------------------------------------------------------



# Dump of table permissions
# ------------------------------------------------------------



# Dump of table rcode
# ------------------------------------------------------------

LOCK TABLES `rcode` WRITE;
/*!40000 ALTER TABLE `rcode` DISABLE KEYS */;

INSERT INTO `rcode` (`code`, `created_at`, `description`, `id`, `updated_at`)
VALUES
	('R01','2019-12-23 14:20:26','Insufficient Funds',1,'2019-12-23 14:20:26'),
	('R02','2019-12-23 14:20:45','Account Closed',2,'2019-12-23 14:20:45'),
	('R03','2019-12-23 14:20:58','No Account/Unable to Locate Account',3,'2019-12-23 14:20:58'),
	('R04','2019-12-23 14:21:13','Invalid Account Number',4,'2019-12-23 14:21:13'),
	('R05','2019-12-23 14:21:30','Not Currently Used',5,'2019-12-23 14:21:30'),
	('R06','2019-12-24 14:12:12','Returned per ODFI’s Request',6,'2019-12-24 14:12:12'),
	('R07','2019-12-24 14:12:12','Authorization Revoked by Customer',7,'2019-12-24 14:12:12'),
	('R08','2019-12-24 14:12:53','Payment Stopped or Stop Payment',8,'2019-12-24 14:12:53'),
	('R09','2019-12-24 14:12:53','Uncollected Funds',9,'2019-12-24 14:12:53'),
	('R10','2019-12-24 14:13:36','Customer Advises Not Authorized',10,'2019-12-24 14:13:36'),
	('R11','2019-12-24 14:13:36','Check Truncation Entry Return',11,'2019-12-24 14:13:36'),
	('R12','2019-12-24 14:14:02','Branch Sold to Another DFI',12,'2019-12-24 14:14:02'),
	('R13','2019-12-24 14:14:02','RDFI Not Qualified to Participate',13,'2019-12-24 14:14:02'),
	('R14','2019-12-24 14:14:33','Representative Payee Deceased',14,'2019-12-24 14:14:33'),
	('R15','2019-12-24 14:14:33','Beneficiary or Account Holder Deceased',15,'2019-12-24 14:14:33'),
	('R16','2019-12-24 14:15:18','Account Frozen',16,'2019-12-24 14:15:18'),
	('R17','2019-12-24 14:15:18','File Edit Criteria',17,'2019-12-24 14:15:18'),
	('R20','2019-12-24 14:16:21','Non-Transaction Account',18,'2019-12-24 14:16:21'),
	('R21','2019-12-24 14:16:21','Invalid Company Identification',19,'2019-12-24 14:16:21'),
	('R22','2019-12-24 14:17:17','Invalid Individual ID Number',20,'2019-12-24 14:17:17'),
	('R23','2019-12-24 14:17:17','Credit Entry Refused by Receiver',21,'2019-12-24 14:17:17'),
	('R24','2019-12-24 14:20:08','Duplicate Entry',22,'2019-12-24 14:20:08'),
	('R26','2019-12-24 14:20:08','Mandatory Field Error',23,'2019-12-24 14:20:08'),
	('R28','2019-12-24 14:21:00','Trace Number Error',24,'2019-12-24 14:21:00'),
	('R29','2019-12-24 14:21:00','Routing Number Check Digit Error',25,'2019-12-24 14:21:00'),
	('R29','2019-12-24 14:22:11','Corporate Customer Advises Not Authorized',26,'2019-12-24 14:22:11'),
	('R30','2019-12-24 14:22:11','RDFI Not Participating in Check Truncation Program',27,'2019-12-24 14:22:11'),
	('R31','2019-12-24 14:29:07','Permissible Return Entry',28,'2019-12-24 14:29:07'),
	('R32','2019-12-24 14:29:07','RDFI Non-Settlement',29,'2019-12-24 14:29:07'),
	('R34','2019-12-24 14:42:01','Limited Participation DFI',30,'2019-12-24 14:42:01'),
	('R35','2019-12-24 14:42:01','Improper Debit Entry',31,'2019-12-24 14:42:01'),
	('R36','2019-12-24 14:43:15','Improper Credit Entry',32,'2019-12-24 14:43:15'),
	('R37','2019-12-24 14:43:15','Source Document Presented for Payment',33,'2019-12-24 14:43:15'),
	('R38','2019-12-24 14:44:05','Stop Payment on Source Document',34,'2019-12-24 14:44:05'),
	('R39','2019-12-24 14:44:05','Not Currently Used',35,'2019-12-24 14:44:05'),
	('R50','2019-12-24 14:45:27','State Law Affecting RCK Acceptance',40,'2019-12-24 14:45:27'),
	('R51','2019-12-24 14:45:27','Item is Ineligible',41,'2019-12-24 14:45:27'),
	('R52','2019-12-24 14:53:22','Stop Payment on Item',42,'2019-12-24 14:53:22'),
	('R53','2019-12-24 14:53:22','Item & ACH Entry Presented for Payment',43,'2019-12-24 14:53:22'),
	('R54','2019-12-24 14:53:56','Not Currently Used',44,'2019-12-24 14:53:56'),
	('R61','2019-12-24 14:53:56','Used for Bank to Bank Communication',45,'2019-12-24 14:53:56');

/*!40000 ALTER TABLE `rcode` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table reassign_history
# ------------------------------------------------------------



# Dump of table reconciles
# ------------------------------------------------------------



# Dump of table role_has_permissions
# ------------------------------------------------------------



# Dump of table role_modules
# ------------------------------------------------------------


# ------------------------------------------------------------
LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;

INSERT INTO `settings` (`created_at`, `updated_at`, `default_payment`, `email`, `forceopay`, `hide`, `id`, `last_mob_notification_time`, `max_assign_per`, `portfolio_start_date`, `rate`, `keys`, `values`)
VALUES
	(NULL,'2020-08-27 17:00:10',1,'tfeinstein@vgusa.com,emailnotification22@gmail.com,gramirez@vgusa.com,fcatalano@vgusa.com,sdauman@vgusa.com,rfallah@vgusa.com,afrieman@vgusa.com',1,NULL,1,'2020-08-27 17:00:10',10.00,'2017-12-25',0.00,'',''),
	('2020-07-15 11:55:12','2020-07-17 12:22:26',0,NULL,NULL,0,5,NULL,100.00,NULL,0.00,'default_percentage_rule','{\"30\":\"1\",\"60\":\"1\",\"90\":\"1\",\"120\":\"1\",\"150\":\"1\"}'),
	('2022-05-23 11:55:12','2022-05-23 12:22:26',0,NULL,NULL,0,17,NULL,100.00,NULL,0.00,'admin_email_address','emailnotification22@gmail.com'),
	('2022-05-23 11:55:12','2022-05-23 12:22:26',0,NULL,NULL,0,16,NULL,100.00,NULL,0.00,'two_factor_required','0'),
	('2022-07-25 11:18:28','2022-07-25 11:29:58',0,NULL,NULL,0,18,NULL,100.00,NULL,0.00,'deduct_agent_fee_from_profit_only','0');

/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table statements
# ------------------------------------------------------------



# Dump of table sub_statuses
# ------------------------------------------------------------

LOCK TABLES `sub_statuses` WRITE;
/*!40000 ALTER TABLE `sub_statuses` DISABLE KEYS */;

INSERT INTO `sub_statuses` (`created_at`, `updated_at`, `id`, `name`)
VALUES
	('2017-11-10 12:54:33','2017-11-26 10:33:34',1,'Active Advance'),
	('2017-11-10 12:54:33','2017-11-26 10:33:46',2,'Payment Temporarily Suspended'),
	('2017-11-26 10:34:00','2017-11-26 10:34:00',4,'Default'),
	('2017-11-26 10:34:13','2017-11-26 10:34:13',5,'Collections'),
	(NULL,NULL,10,'Referred To Legal'),
	(NULL,NULL,11,'Advance Completed'),
	(NULL,NULL,12,'Merchant in collections/ see notes'),
	(NULL,NULL,13,'Other/ see notes'),
	('2017-12-27 04:51:25','2017-12-27 04:51:25',15,'Partial Payment'),
	('2018-03-27 10:05:58','2018-03-27 10:05:58',16,'Payment Modified'),
	('2018-11-18 22:04:55','2018-11-18 22:04:55',17,'Cancelled'),
	('2019-01-09 00:57:33','2019-01-09 00:57:33',18,'Settled'),
	('2019-01-09 00:58:05','2019-01-09 00:58:05',19,'Early Pay Discount'),
	('2019-03-25 09:41:53','2019-03-25 09:42:10',20,'Default+'),
	('2019-03-25 10:22:38','2019-03-25 10:22:38',22,'Default / Legal');

/*!40000 ALTER TABLE `sub_statuses` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table template
# ------------------------------------------------------------



# Dump of table transaction_types
# ------------------------------------------------------------

LOCK TABLES `transaction_types` WRITE;
/*!40000 ALTER TABLE `transaction_types` DISABLE KEYS */;

INSERT INTO `transaction_types` (`created_date`, `id`, `name`, `updated_date`)
VALUES
	(NULL,1,'ACH Works',NULL),
	(NULL,2,'Internal Payoff',NULL);

/*!40000 ALTER TABLE `transaction_types` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table us_states
# ------------------------------------------------------------

LOCK TABLES `us_states` WRITE;
/*!40000 ALTER TABLE `us_states` DISABLE KEYS */;

INSERT INTO `us_states` (`id`, `state`, `state_abbr`)
VALUES
	(1,'Alabama','AL'),
	(2,'Alaska','AK'),
	(3,'Arizona','AZ'),
	(4,'Arkansas','AR'),
	(5,'California','CA'),
	(6,'Colorado','CO'),
	(7,'Connecticut','CT'),
	(8,'Delaware','DE'),
	(9,'District of Columbia','DC'),
	(10,'Florida','FL'),
	(11,'Georgia','GA'),
	(12,'Hawaii','HI'),
	(13,'Idaho','ID'),
	(14,'Illinois','IL'),
	(15,'Indiana','IN'),
	(16,'Iowa','IA'),
	(17,'Kansas','KS'),
	(18,'Kentucky','KY'),
	(19,'Louisiana','LA'),
	(20,'Maine','ME'),
	(21,'Maryland','MD'),
	(22,'Massachusetts','MA'),
	(23,'Michigan','MI'),
	(24,'Minnesota','MN'),
	(25,'Mississippi','MS'),
	(26,'Missouri','MO'),
	(27,'Montana','MT'),
	(28,'Nebraska','NE'),
	(29,'Nevada','NV'),
	(30,'New Hampshire','NH'),
	(31,'New Jersey','NJ'),
	(32,'New Mexico','NM'),
	(33,'New York','NY'),
	(34,'North Carolina','NC'),
	(35,'North Dakota','ND'),
	(36,'Ohio','OH'),
	(37,'Oklahoma','OK'),
	(38,'Oregon','OR'),
	(39,'Pennsylvania','PA'),
	(40,'Rhode Island','RI'),
	(41,'South Carolina','SC'),
	(42,'South Dakota','SD'),
	(43,'Tennessee','TN'),
	(44,'Texas','TX'),
	(45,'Utah','UT'),
	(46,'Vermont','VT'),
	(47,'Virginia','VA'),
	(48,'Washington','WA'),
	(49,'West Virginia','WV'),
	(50,'Wisconsin','WI'),
	(51,'Wyoming','WY');

/*!40000 ALTER TABLE `us_states` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table user_details
# ------------------------------------------------------------



# Dump of table user_has_permissions
# ------------------------------------------------------------



# Dump of table user_has_roles
# ------------------------------------------------------------

LOCK TABLES `user_has_roles` WRITE;
/*!40000 ALTER TABLE `user_has_roles` DISABLE KEYS */;

INSERT INTO `user_has_roles` (`model_id`, `model_type`, `role_id`, `user_id`)
VALUES
	(1,'App\\User',1,1);

/*!40000 ALTER TABLE `user_has_roles` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table users
# ------------------------------------------------------------

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`active_status`, `api_token`, `auto_generation`, `brokerage`, `company`, `created_at`, `updated_at`, `creator_id`, `email`, `file_type`, `global_syndication`, `groupby_recurence`, `id`, `interest_generated_at`, `interest_rate`, `investor_type`, `lag_time`, `last_login_at`, `last_login_ip`, `liquidity_exclude`, `logo`, `management_fee`, `merchant_id_m`, `merchant_permission`, `name`, `notification_email`, `notification_recurence`, `password`, `remember_token`, `s_prepaid_status`, `underwriting_fee`, `underwriting_status`, `whole_portfolio`)
VALUES
	(1,NULL,0,0,89,'2017-11-10 12:54:33','2019-10-16 10:16:52',1,'admin@investor.portal',2,0.00,NULL,1,'2018-06-10',0.00,NULL,0,NULL,NULL,0,'',0.00,NULL,0,'admin',NULL,NULL,'$2y$10$k0w4SCNHmNoqV8iTCXHbue8kTRQH8Ubrvw1WkL/ZDldD5FlnCypTS','1OE3ZJMp3l7KdrAXw1I9umQTj9ISwBSssF94mAoZtmIr5ysVQxAz19Ov94zt',0,NULL,NULL,0),
(1,NULL,0,0,null,'2022-06-27 12:54:33','2022-06-27 10:16:52',1,'system@system.com',0,0.00,NULL,783,'',0.00,NULL,0,NULL,NULL,0,'',0.00,NULL,0,'System',NULL,NULL,'','',0,NULL,NULL,0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

# Dump of table wires
# ------------------------------------------------------------



# Dump of table wires_documents
# ------------------------------------------------------------



# Dump of table wires_merchant
# ------------------------------------------------------------




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
