SET @sName = 'gmo_fb_events';

SET @iTypeOrder = (SELECT MAX(`order`) FROM `sys_options_types` WHERE `group` = 'modules');
INSERT INTO `sys_options_types` (`group`, `name`, `caption`, `icon`, `order`) VALUES
('modules', @sName, '_gmo_fb_events', 'calendar-days', IFNULL(@iTypeOrder, 0) + 1);
SET @iTypeId = LAST_INSERT_ID();

INSERT INTO `sys_options_categories` (`type_id`, `name`, `caption`, `order`) VALUES
(@iTypeId, @sName, '_gmo_fb_events', 10);
SET @iCategoryId = LAST_INSERT_ID();

INSERT IGNORE INTO `sys_options` (`name`, `value`, `category_id`, `caption`, `type`, `extra`, `check`, `check_error`, `order`) VALUES
('gmo_fb_events_graph_version', '24.0', @iCategoryId, 'Meta Graph API version', 'digit', '', '', '', 10),
('gmo_fb_events_page_token', '', @iCategoryId, 'Facebook Page access token', 'password', '', '', '', 20),
('gmo_fb_events_author_profile_id', '0', @iCategoryId, 'UNA author profile ID', 'digit', '', '', '', 30),
('gmo_fb_events_category_id', '0', @iCategoryId, 'UNA event category ID', 'digit', '', '', '', 40),
('gmo_fb_events_timezone', 'America/New_York', @iCategoryId, 'Default timezone', 'text', '', '', '', 50);
