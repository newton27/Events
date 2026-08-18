DROP TABLE IF EXISTS `gmo_fb_events_imports`;
DELETE FROM `sys_options` WHERE `name` LIKE 'gmo_fb_events_%';
DELETE FROM `sys_options_categories` WHERE `name` = 'Facebook Events Importer';

