SET @sName = 'gmo_fb_events';
SET @iTypeId = (SELECT `id` FROM `sys_options_types` WHERE `name` = @sName LIMIT 1);

DELETE FROM `sys_options` WHERE `name` LIKE 'gmo_fb_events_%';
DELETE FROM `sys_options_categories` WHERE `type_id` = @iTypeId;
DELETE FROM `sys_options_types` WHERE `id` = @iTypeId;
