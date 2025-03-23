-- Table des conversations
CREATE TABLE IF NOT EXISTS `chat_conversation` (
  `chat_conversationid` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(191) NOT NULL,
  `context_type` enum('lead','project','user','custom') NOT NULL DEFAULT 'lead',
  `context_id` int(11) DEFAULT NULL,
  `title` varchar(191) DEFAULT NULL,
  `status` enum('active','closed','archived') NOT NULL DEFAULT 'active',
  `timestamp` int(11) NOT NULL DEFAULT 0,
  `timestamp_update` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`chat_conversationid`),
  UNIQUE KEY `slug` (`slug`),
  KEY `context_type_context_id` (`context_type`,`context_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des messages
CREATE TABLE IF NOT EXISTS `chat_message` (
  `chat_messageid` int(11) NOT NULL AUTO_INCREMENT,
  `chat_conversationid` int(11) NOT NULL,
  `sender_type` enum('user','lead','professional') NOT NULL DEFAULT 'user',
  `sender_id` int(11) NOT NULL,
  `recipient_type` enum('user','lead','professional','all') NOT NULL DEFAULT 'all',
  `recipient_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `timestamp` int(11) NOT NULL DEFAULT 0,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_timestamp` int(11) DEFAULT NULL,
  `delivery_status` enum('sent','delivered','failed') NOT NULL DEFAULT 'sent',
  `delivery_timestamp` int(11) DEFAULT NULL,
  `delivery_attempts` int(11) NOT NULL DEFAULT 1,
  `delivery_error` text DEFAULT NULL,
  PRIMARY KEY (`chat_messageid`),
  KEY `chat_conversationid` (`chat_conversationid`),
  KEY `sender_type_sender_id` (`sender_type`,`sender_id`),
  KEY `recipient_type_recipient_id` (`recipient_type`,`recipient_id`),
  KEY `timestamp` (`timestamp`),
  KEY `is_read` (`is_read`),
  KEY `delivery_status` (`delivery_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des participants
CREATE TABLE IF NOT EXISTS `chat_participant` (
  `chat_participantid` int(11) NOT NULL AUTO_INCREMENT,
  `chat_conversationid` int(11) NOT NULL,
  `participant_type` enum('user','lead','professional') NOT NULL DEFAULT 'user',
  `participant_id` int(11) NOT NULL,
  `join_timestamp` int(11) NOT NULL DEFAULT 0,
  `last_read_messageid` int(11) DEFAULT NULL,
  `status` enum('active','inactive','left') NOT NULL DEFAULT 'active',
  `notification_preference` enum('all','mentions','none') NOT NULL DEFAULT 'all',
  PRIMARY KEY (`chat_participantid`),
  UNIQUE KEY `conversation_participant` (`chat_conversationid`,`participant_type`,`participant_id`),
  KEY `participant_type_participant_id` (`participant_type`,`participant_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des pièces jointes
CREATE TABLE IF NOT EXISTS `chat_attachment` (
  `chat_attachmentid` int(11) NOT NULL AUTO_INCREMENT,
  `chat_messageid` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `timestamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`chat_attachmentid`),
  KEY `chat_messageid` (`chat_messageid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des notifications
CREATE TABLE IF NOT EXISTS `chat_notification` (
  `chat_notificationid` int(11) NOT NULL AUTO_INCREMENT,
  `chat_participantid` int(11) NOT NULL,
  `chat_messageid` int(11) NOT NULL,
  `notification_type` enum('new_message','mention','file_shared','participant_joined','participant_left') NOT NULL DEFAULT 'new_message',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `timestamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`chat_notificationid`),
  KEY `chat_participantid` (`chat_participantid`),
  KEY `chat_messageid` (`chat_messageid`),
  KEY `is_read` (`is_read`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contraintes de clé étrangère
ALTER TABLE `chat_message`
  ADD CONSTRAINT `fk_message_conversation` FOREIGN KEY (`chat_conversationid`) REFERENCES `chat_conversation` (`chat_conversationid`) ON DELETE CASCADE;

ALTER TABLE `chat_participant`
  ADD CONSTRAINT `fk_participant_conversation` FOREIGN KEY (`chat_conversationid`) REFERENCES `chat_conversation` (`chat_conversationid`) ON DELETE CASCADE;

ALTER TABLE `chat_attachment`
  ADD CONSTRAINT `fk_attachment_message` FOREIGN KEY (`chat_messageid`) REFERENCES `chat_message` (`chat_messageid`) ON DELETE CASCADE;

ALTER TABLE `chat_notification`
  ADD CONSTRAINT `fk_notification_participant` FOREIGN KEY (`chat_participantid`) REFERENCES `chat_participant` (`chat_participantid`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notification_message` FOREIGN KEY (`chat_messageid`) REFERENCES `chat_message` (`chat_messageid`) ON DELETE CASCADE;
