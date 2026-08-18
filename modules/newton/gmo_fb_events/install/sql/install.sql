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

SET @iCategoryId = (SELECT `id` FROM `sys_options_categories` WHERE `name` = 'Facebook Events Importer' LIMIT 1);
INSERT INTO `sys_options_categories` (`name`, `caption`, `module_id`, `order`)
SELECT 'Facebook Events Importer', '_gmo_fb_events', `id`, 0 FROM `sys_modules` WHERE `name` = 'gmo_fb_events' AND @iCategoryId IS NULL;
SET @iCategoryId = (SELECT `id` FROM `sys_options_categories` WHERE `name` = 'Facebook Events Importer' LIMIT 1);

INSERT IGNORE INTO `sys_options` (`category_id`, `name`, `caption`, `type`, `value`, `order`) VALUES
(@iCategoryId, 'gmo_fb_events_graph_version', 'Meta Graph API version', 'digit', '24.0', 10),
(@iCategoryId, 'gmo_fb_events_page_token', 'Facebook Page access token', 'password', '', 20),
(@iCategoryId, 'gmo_fb_events_author_profile_id', 'UNA author profile ID', 'digit', '0', 30),
(@iCategoryId, 'gmo_fb_events_category_id', 'UNA event category ID', 'digit', '0', 40),
(@iCategoryId, 'gmo_fb_events_timezone', 'Default timezone', 'text', 'America/New_York', 50);

