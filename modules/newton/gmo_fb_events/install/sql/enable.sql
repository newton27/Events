SET @sName = 'gmo_fb_events';

SET @iTypeOrder = (SELECT MAX(`order`) FROM `sys_options_types` WHERE `group` = 'modules');
INSERT INTO `sys_options_types` (`group`, `name`, `caption`, `icon`, `order`) VALUES
('modules', @sName, '_gmo_fb_events', 'gmo_fb_events@modules/newton/gmo_fb_events/|studio-icon.svg', IFNULL(@iTypeOrder, 0) + 1);
SET @iTypeId = LAST_INSERT_ID();

INSERT INTO `sys_options_categories` (`type_id`, `name`, `caption`, `order`) VALUES
(@iTypeId, @sName, '_gmo_fb_events', 10);
SET @iCategoryId = LAST_INSERT_ID();

INSERT IGNORE INTO `sys_options` (`name`, `value`, `category_id`, `caption`, `type`, `extra`, `check`, `check_error`, `order`) VALUES
('gmo_fb_events_author_profile_id', '0', @iCategoryId, '_gmo_fb_events_option_author_profile_id', 'digit', '', '', '', 10),
('gmo_fb_events_category_id', '0', @iCategoryId, '_gmo_fb_events_option_category_id', 'digit', '', '', '', 20),
('gmo_fb_events_timezone', 'America/New_York', @iCategoryId, '_gmo_fb_events_option_timezone', 'text', '', '', '', 30);
