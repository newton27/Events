CREATE TABLE IF NOT EXISTS `gmo_fb_events_imports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `facebook_event_id` varchar(32) NOT NULL,
  `source_url` varchar(2048) NOT NULL,
  `una_event_id` int(10) unsigned NOT NULL DEFAULT '0',
  `payload_hash` char(64) NOT NULL DEFAULT '',
  `status` enum('previewed','imported','failed','skipped') NOT NULL DEFAULT 'previewed',
  `message` text NOT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `facebook_event_id` (`facebook_event_id`),
  KEY `una_event_id` (`una_event_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
