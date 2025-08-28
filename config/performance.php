<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for performance optimization including caching,
    | query optimization, and monitoring settings.
    |
    */

    'cache' => [
        /*
        |--------------------------------------------------------------------------
        | Cache TTL (Time To Live) Settings
        |--------------------------------------------------------------------------
        |
        | Default cache expiration times in seconds for different types of data.
        |
        */
        'ttl' => [
            'very_short' => 300,    // 5 minutes - frequently changing data
            'short' => 900,         // 15 minutes - moderately changing data
            'medium' => 3600,       // 1 hour - semi-static data
            'long' => 86400,        // 24 hours - mostly static data
            'very_long' => 604800,  // 1 week - rarely changing data
        ],

        /*
        |--------------------------------------------------------------------------
        | Cache Prefixes
        |--------------------------------------------------------------------------
        |
        | Prefixes used for organizing cache keys by functionality.
        |
        */
        'prefixes' => [
            'stats' => 'stats',
            'applicant' => 'applicant',
            'exam' => 'exam',
            'question' => 'question',
            'user' => 'user',
            'interview' => 'interview',
            'analytics' => 'analytics',
        ],

        /*
        |--------------------------------------------------------------------------
        | Auto Cache Warmup
        |--------------------------------------------------------------------------
        |
        | Enable automatic cache warming for frequently accessed data.
        |
        */
        'auto_warmup' => env('CACHE_AUTO_WARMUP', true),

        /*
        |--------------------------------------------------------------------------
        | Cache Invalidation Strategy
        |--------------------------------------------------------------------------
        |
        | Strategy for cache invalidation when data changes.
        | Options: 'aggressive', 'conservative', 'manual'
        |
        */
        'invalidation_strategy' => env('CACHE_INVALIDATION_STRATEGY', 'conservative'),
    ],

    'database' => [
        /*
        |--------------------------------------------------------------------------
        | Query Optimization
        |--------------------------------------------------------------------------
        |
        | Settings for database query optimization.
        |
        */
        'optimization' => [
            // Enable/disable query logging for performance monitoring
            'enable_query_log' => env('DB_ENABLE_QUERY_LOG', false),
            
            // Maximum number of queries to log
            'max_query_log_size' => env('DB_MAX_QUERY_LOG_SIZE', 100),
            
            // Log slow queries (in milliseconds)
            'slow_query_threshold' => env('DB_SLOW_QUERY_THRESHOLD', 1000),
            
            // Enable eager loading optimization
            'enable_eager_loading' => env('DB_ENABLE_EAGER_LOADING', true),
        ],

        /*
        |--------------------------------------------------------------------------
        | Connection Pool Settings
        |--------------------------------------------------------------------------
        |
        | Database connection pooling configuration.
        |
        */
        'pool' => [
            // Maximum number of database connections
            'max_connections' => env('DB_MAX_CONNECTIONS', 10),
            
            // Connection timeout in seconds
            'timeout' => env('DB_CONNECTION_TIMEOUT', 30),
        ],
    ],

    'monitoring' => [
        /*
        |--------------------------------------------------------------------------
        | Performance Monitoring
        |--------------------------------------------------------------------------
        |
        | Settings for system performance monitoring.
        |
        */
        'enabled' => env('PERFORMANCE_MONITORING_ENABLED', true),
        
        /*
        |--------------------------------------------------------------------------
        | Metrics Collection
        |--------------------------------------------------------------------------
        |
        | What metrics to collect for performance analysis.
        |
        */
        'collect_metrics' => [
            'database_queries' => true,
            'cache_hit_rate' => true,
            'memory_usage' => true,
            'response_times' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | Alert Thresholds
        |--------------------------------------------------------------------------
        |
        | Thresholds for performance alerts.
        |
        */
        'thresholds' => [
            'max_response_time' => 2000, // milliseconds
            'max_memory_usage' => 512,   // MB
            'min_cache_hit_rate' => 80,  // percentage
            'max_database_connections' => 8,
        ],

        /*
        |--------------------------------------------------------------------------
        | Reporting
        |--------------------------------------------------------------------------
        |
        | Performance reporting configuration.
        |
        */
        'reporting' => [
            // How often to generate performance reports
            'report_frequency' => env('PERFORMANCE_REPORT_FREQUENCY', 'daily'),
            
            // Email addresses to send reports to
            'report_recipients' => env('PERFORMANCE_REPORT_RECIPIENTS', ''),
            
            // Enable automatic performance reports
            'auto_reports' => env('PERFORMANCE_AUTO_REPORTS', false),
        ],
    ],

    'optimization' => [
        /*
        |--------------------------------------------------------------------------
        | Pagination Optimization
        |--------------------------------------------------------------------------
        |
        | Settings for optimizing paginated queries.
        |
        */
        'pagination' => [
            // Default page size
            'default_per_page' => env('PAGINATION_DEFAULT_PER_PAGE', 15),
            
            // Maximum page size allowed
            'max_per_page' => env('PAGINATION_MAX_PER_PAGE', 100),
            
            // Enable cursor-based pagination for large datasets
            'enable_cursor_pagination' => env('PAGINATION_ENABLE_CURSOR', false),
        ],

        /*
        |--------------------------------------------------------------------------
        | Search Optimization
        |--------------------------------------------------------------------------
        |
        | Settings for optimizing search functionality.
        |
        */
        'search' => [
            // Minimum search term length
            'min_search_length' => env('SEARCH_MIN_LENGTH', 2),
            
            // Enable full-text search
            'enable_full_text_search' => env('SEARCH_ENABLE_FULL_TEXT', false),
            
            // Search result cache duration (seconds)
            'cache_duration' => env('SEARCH_CACHE_DURATION', 300),
        ],

        /*
        |--------------------------------------------------------------------------
        | Asset Optimization
        |--------------------------------------------------------------------------
        |
        | Settings for optimizing static assets.
        |
        */
        'assets' => [
            // Enable asset compression
            'enable_compression' => env('ASSETS_ENABLE_COMPRESSION', true),
            
            // Enable browser caching
            'enable_browser_cache' => env('ASSETS_ENABLE_BROWSER_CACHE', true),
            
            // Asset cache duration (seconds)
            'cache_duration' => env('ASSETS_CACHE_DURATION', 31536000), // 1 year
        ],
    ],
];
