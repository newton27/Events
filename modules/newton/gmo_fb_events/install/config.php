<?php

$aConfig = array(
    'type' => BX_DOL_MODULE_TYPE_MODULE,
    'name' => 'gmo_fb_events',
    'title' => 'UNA Facebook Importer',
    'note' => 'Creates UNA Events from shared Facebook event links without requiring the Facebook API.',
    'version' => '2.0.7',
    'vendor' => 'Newton',
    'compatible_with' => array('15.0.x'),
    'home_dir' => 'newton/gmo_fb_events/',
    'home_uri' => 'gmo_fb_events',
    'db_prefix' => 'gmo_fb_events_',
    'class_prefix' => 'GmoFbEvents',
    'language_category' => 'UNA Facebook Importer',
    'install' => array('execute_sql' => 1, 'update_languages' => 1, 'clear_db_cache' => 1),
    'uninstall' => array('execute_sql' => 1, 'update_languages' => 1, 'clear_db_cache' => 1),
    'enable' => array('execute_sql' => 1, 'clear_db_cache' => 1),
    'disable' => array('execute_sql' => 1, 'clear_db_cache' => 1),
    'dependencies' => array('bx_events' => '15.0.0'),
);
