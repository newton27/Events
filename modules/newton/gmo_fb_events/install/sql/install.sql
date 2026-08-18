SET @sName = 'gmo_fb_events';

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

-- Studio page and dashboard widget.
INSERT INTO `sys_std_pages` (`index`, `name`, `header`, `caption`, `icon`) VALUES
(3, @sName, '_gmo_fb_events', '_gmo_fb_events', 'gmo_fb_events@modules/newton/gmo_fb_events/|std-icon.svg');
SET @iPageId = LAST_INSERT_ID();

SET @iParentPageId = (SELECT `id` FROM `sys_std_pages` WHERE `name` = 'home');
SET @iParentPageOrder = (SELECT MAX(`order`) FROM `sys_std_pages_widgets` WHERE `page_id` = @iParentPageId);
INSERT INTO `sys_std_widgets` (`page_id`, `module`, `url`, `click`, `icon`, `caption`, `cnt_notices`, `cnt_actions`) VALUES
(@iPageId, @sName, '{url_studio}module.php?name=gmo_fb_events', '', 'gmo_fb_events@modules/newton/gmo_fb_events/|std-icon.svg', '_gmo_fb_events', '', 'a:4:{s:6:"module";s:6:"system";s:6:"method";s:11:"get_actions";s:6:"params";a:0:{}s:5:"class";s:18:"TemplStudioModules";}');
INSERT INTO `sys_std_pages_widgets` (`page_id`, `widget_id`, `order`) VALUES
(@iParentPageId, LAST_INSERT_ID(), IF(ISNULL(@iParentPageOrder), 1, @iParentPageOrder + 1));
