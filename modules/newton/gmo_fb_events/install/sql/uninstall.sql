SET @sName = 'gmo_fb_events';
SET @iTypeId = (SELECT `id` FROM `sys_options_types` WHERE `name` = @sName LIMIT 1);

DELETE FROM `sys_std_pages_widgets`
WHERE `widget_id` IN (SELECT `id` FROM `sys_std_widgets` WHERE `module` = @sName);
DELETE FROM `sys_std_widgets` WHERE `module` = @sName;
DELETE FROM `sys_std_pages` WHERE `name` = @sName;

DELETE FROM `sys_options` WHERE `name` LIKE 'gmo_fb_events_%';
DELETE FROM `sys_options_categories` WHERE `type_id` = @iTypeId;
DELETE FROM `sys_options_types` WHERE `id` = @iTypeId;

DROP TABLE IF EXISTS `gmo_fb_events_imports`;
