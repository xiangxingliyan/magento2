<?php
return array (
  'backend' => 
  array (
    'frontName' => 'admin',
  ),
  'session' => 
  array (
    'save' => 'files',
  ),
  'db' => 
  array (
    'table_prefix' => '',
    'connection' => 
    array (
      'default' => 
      array (
        'host' => 'localhost',
        'dbname' => 'vladdin',
        'username' => 'root',
        'password' => '',
        'active' => '1',
      ),
    ),
  ),
  'resource' => 
  array (
    'default_setup' => 
    array (
      'connection' => 'default',
    ),
  ),
  'x-frame-options' => 'SAMEORIGIN',
  'MAGE_MODE' => 'developer',
  'cache_types' => 
  array (
    'config' => 1,
    'layout' => 1,
    'block_html' => 1,
    'view_files_fallback' => 0,
    'view_files_preprocessing' => 0,
    'collections' => 1,
    'db_ddl' => 1,
    'eav' => 1,
    'full_page' => 0,
    'translate' => 1,
    'config_integration' => 1,
    'config_webservice' => 1,
    'config_integration_api' => 1,
    'reflection' => 1,
    'customer_notification' => 1,
  ),
  'install' => 
  array (
    'date' => 'Sat, 14 Oct 2017 11:30:37 +0000',
  ),
  'cache' => 
  array (
    'frontend' => 
    array (
      'default' => 
      array (
        'backend' => 'Cm_Cache_Backend_Redis',
        'backend_options' => 
        array (
          'server' => '127.0.0.1',
          'port' => '6379',
          'persistent' => '',
          'database' => '12',
          'password' => '',
          'force_standalone' => '0',
          'connect_retries' => '1',
          'read_timeout' => '10',
          'automatic_cleaning_factor' => '0',
          'compress_data' => '1',
          'compress_tags' => '1',
          'compress_threshold' => '20480',
          'compression_lib' => 'gzip',
          'use_lua' => '0',
        ),
      ),
      'page_cache' => 
      array (
        'backend' => 'Cm_Cache_Backend_Redis',
        'backend_options' => 
        array (
          'server' => '127.0.0.1',
          'port' => '6379',
          'persistent' => '',
          'database' => '13',
          'password' => '',
          'force_standalone' => '0',
          'connect_retries' => '1',
          'lifetimelimit' => '57600',
          'compress_data' => '0',
        ),
      ),
    ),
  ),
);
