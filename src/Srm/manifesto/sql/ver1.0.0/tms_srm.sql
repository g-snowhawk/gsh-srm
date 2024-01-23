CREATE TABLE IF NOT EXISTS `table::bank` (
  `bank_code` varchar(4) NOT NULL,
  `branch_code` varchar(3) NOT NULL,
  `account_number` varchar(7) NOT NULL,
  `userkey` int unsigned NOT NULL,
  `bank` varchar(255) NOT NULL,
  `branch` varchar(255) NOT NULL,
  `account_type` varchar(255) NOT NULL,
  `account_holder` varchar(255) NOT NULL,
  `item_code` varchar(4) DEFAULT NULL,
  `create_date` datetime NOT NULL,
  `modify_date` datetime DEFAULT NULL,
  PRIMARY KEY (`bank_code`,`branch_code`,`account_number`,`userkey`),
  KEY `table::bank_ibfk_1` (`userkey`),
  CONSTRAINT `table::bank_ibfk_1` FOREIGN KEY (`userkey`) REFERENCES `table::user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `table::group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `userkey` int unsigned NOT NULL,
  `label` varchar(32) NOT NULL,
  `users` text NOT NULL,
  `create_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modify_date` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `table::group_ibfk_1` (`userkey`),
  CONSTRAINT `table::group_ibfk_1` FOREIGN KEY (`userkey`) REFERENCES `table::user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `table::receipt_template` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `userkey` int unsigned NOT NULL,
  `title` varchar(50) NOT NULL,
  `line` tinyint(3) unsigned NOT NULL DEFAULT '10',
  `priority` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `pdf_mapper` text NOT NULL,
  `mail_template` text NOT NULL,
  `create_date` datetime NOT NULL,
  `modify_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `table::receipt_template_ibfk_1` (`userkey`),
  CONSTRAINT `table::receipt_template_ibfk_1` FOREIGN KEY (`userkey`) REFERENCES `table::user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `table::receipt_to` (
  `id` varchar(32) NOT NULL,
  `userkey` int unsigned NOT NULL,
  `aliasto` int unsigned DEFAULT NULL,
  `company` varchar(255) NOT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `fullname_rubi` varchar(255) DEFAULT NULL,
  `zipcode` varchar(8) DEFAULT NULL,
  `address1` varchar(255) DEFAULT NULL,
  `address2` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `division` text,
  `modified_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `table::receipt_to_ibfk_1` (`userkey`),
  KEY `table::receipt_to_ibfk_2` (`aliasto`),
  CONSTRAINT `table::receipt_to_ibfk_1` FOREIGN KEY (`userkey`) REFERENCES `table::user` (`id`),
  CONSTRAINT `table::receipt_to_ibfk_2` FOREIGN KEY (`aliasto`) REFERENCES `table::user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `table::receipt` (
  `issue_date` date NOT NULL,
  `receipt_number` int unsigned NOT NULL,
  `userkey` int unsigned NOT NULL,
  `templatekey` int unsigned NOT NULL,
  `draft` enum('0','1') NOT NULL DEFAULT '1',
  `client_id` varchar(32) NOT NULL,
  `shared` int unsigned DEFAULT NULL,
  `subject` varchar(66) DEFAULT NULL,
  `bank_id` varchar(15) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `receipt` date DEFAULT NULL,
  `billing_date` date DEFAULT NULL,
  `term` varchar(50) DEFAULT NULL,
  `valid` varchar(50) DEFAULT NULL,
  `sales` varchar(1) DEFAULT NULL,
  `delivery` varchar(255) DEFAULT NULL,
  `payment` varchar(255) DEFAULT NULL,
  `payment_terms` varchar(255) DEFAULT NULL,
  `additional_1_item` varchar(50) DEFAULT NULL,
  `additional_1_price` int DEFAULT NULL,
  `additional_2_item` varchar(50) DEFAULT NULL,
  `additional_2_price` int DEFAULT NULL,
  `unavailable` enum('0','1') NOT NULL DEFAULT '0',
  `unavailable_reason` text,
  PRIMARY KEY (`issue_date`,`receipt_number`,`userkey`,`templatekey`,`draft`),
  KEY `table::receipt_ibfk_1` (`userkey`),
  KEY `table::receipt_ibfk_2` (`templatekey`),
  KEY `table::receipt_ibfk_3` (`client_id`),
  KEY `table::receipt_ibfk_4` (`receipt_number`),
  CONSTRAINT `table::receipt_ibfk_1` FOREIGN KEY (`userkey`) REFERENCES `table::user` (`id`),
  CONSTRAINT `table::receipt_ibfk_2` FOREIGN KEY (`templatekey`) REFERENCES `table::receipt_template` (`id`)
  CONSTRAINT `table::receipt_ibfk_3` FOREIGN KEY (`client_id`) REFERENCES `table::receipt_to` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `table::receipt_detail` (
  `issue_date` date NOT NULL,
  `receipt_number` int unsigned NOT NULL,
  `userkey` int unsigned NOT NULL,
  `templatekey` int unsigned NOT NULL,
  `draft` enum('0','1') NOT NULL DEFAULT '1',
  `page_number` tinyint(3) unsigned NOT NULL,
  `line_number` tinyint(3) unsigned NOT NULL,
  `content` varchar(66) DEFAULT NULL,
  `price` int DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `tax_rate` decimal(3,2) DEFAULT NULL,
  `unit` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`issue_date`,`receipt_number`,`userkey`,`templatekey`,`draft`,`page_number`,`line_number`),
  KEY `table::receipt_detail_ibfk_1` (`userkey`),
  KEY `table::receipt_detail_ibfk_2` (`templatekey`),
  KEY `table::receipt_detail_ibfk_3` (`issue_date`,`receipt_number`,`userkey`,`templatekey`,`draft`),
  CONSTRAINT `table::receipt_detail_ibfk_1` FOREIGN KEY (`userkey`) REFERENCES `table::user` (`id`),
  CONSTRAINT `table::receipt_detail_ibfk_2` FOREIGN KEY (`templatekey`) REFERENCES `table::receipt_template` (`id`),
  CONSTRAINT `table::receipt_detail_ibfk_3` FOREIGN KEY (`issue_date`, `receipt_number`, `userkey`, `templatekey`, `draft`) REFERENCES `table::receipt` (`issue_date`, `receipt_number`, `userkey`, `templatekey`, `draft`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `table::receipt_note` (
  `issue_date` date NOT NULL,
  `receipt_number` int unsigned NOT NULL,
  `userkey` int unsigned NOT NULL,
  `templatekey` int unsigned NOT NULL,
  `draft` enum('0','1') NOT NULL DEFAULT '1',
  `page_number` tinyint(3) unsigned NOT NULL,
  `content` text,
  PRIMARY KEY (`issue_date`,`receipt_number`,`userkey`,`templatekey`,`draft`,`page_number`),
  KEY `table::receipt_note_ibfk_1` (`userkey`),
  KEY `table::receipt_note_ibfk_2` (`templatekey`),
  KEY `table::receipt_note_ibfk_3` (`issue_date`,`receipt_number`,`userkey`,`templatekey`,`draft`),
  CONSTRAINT `table::receipt_note_ibfk_1` FOREIGN KEY (`userkey`) REFERENCES `table::user` (`id`),
  CONSTRAINT `table::receipt_note_ibfk_2` FOREIGN KEY (`templatekey`) REFERENCES `table::receipt_template` (`id`),
  CONSTRAINT `table:;receipt_note_ibfk_3` FOREIGN KEY (`issue_date`, `receipt_number`, `userkey`, `templatekey`, `draft`) REFERENCES `table::receipt` (`issue_date`, `receipt_number`, `userkey`, `templatekey`, `draft`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `table::receipt_mail_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `issue_date` date NOT NULL,
  `receipt_number` int unsigned NOT NULL,
  `userkey` int unsigned NOT NULL,
  `templatekey` int unsigned NOT NULL,
  `logtime` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `table:;receipt_mail_log_ibfk_1` FOREIGN KEY (`issue_date`, `receipt_number`, `userkey`, `templatekey`) REFERENCES `table::receipt` (`issue_date`, `receipt_number`, `userkey`, `templatekey`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `table::tax_rates` (
  `effective_date` date NOT NULL,
  `area_code` varchar(32),
  `tax_rate` decimal(3,2) NOT NULL,
  `reduced_tax_rate` decimal(3,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`effective_date`,`area_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `table::tax_rates` (`effective_date`,`area_code`,`tax_rate`,`reduced_tax_rate`) VALUES
('1989-04-01','ja','0.03','0.00'),
('1997-04-01','ja','0.05','0.00'),
('2014-04-01','ja','0.08','0.00'),
('2019-10-01','ja','0.10','0.08');
