<?php

return array(
    // Filenames and format
    'filename' => '_ide_helper',
    'format' => 'php',
    'meta_filename' => '.phpstorm.meta.php',

    // Generate Fluent helpers
    'include_fluent' => true,

    // Write magic methods
    'write_model_magic_where' => true,

    // The helper files to include
    'include_helpers' => false,
    'helper_files' => array(
        base_path() . '/vendor/laravel/framework/src/Illuminate/Support/helpers.php',
    ),

    // The model locations to include
    'model_locations' => array(
        'app',
    ),

    // Extra classes that aren't extended, but called with magic functions
    'extra' => array(
        'Eloquent' => array('Illuminate\Database\Eloquent\Builder', 'Illuminate\Database\Query\Builder'),
        'Session' => array('Illuminate\Session\Store'),
    ),
    'magic' => array(
        'Log' => array(
            'debug' => 'Monolog\Logger::addDebug',
            'info' => 'Monolog\Logger::addInfo',
            'notice' => 'Monolog\Logger::addNotice',
            'warning' => 'Monolog\Logger::addWarning',
            'error' => 'Monolog\Logger::addError',
            'critical' => 'Monolog\Logger::addCritical',
            'alert' => 'Monolog\Logger::addAlert',
            'emergency' => 'Monolog\Logger::addEmergency',
        )
    ),

    // Interface implementations
    'interfaces' => array(
        //
    ),

    // Mapping for custom database types
    'custom_db_types' => array(
        //
    ),

    // Enable support for camel cased models
    'model_camel_case_properties' => false,

    // Property casts
    'type_overrides' => array(
        'integer' => 'int',
        'boolean' => 'bool',
    ),
);
