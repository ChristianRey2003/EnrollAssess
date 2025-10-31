# Redis Production Setup Guide

## Overview

This guide covers setting up Redis for the EnrollAssess system on Digital Ocean for optimal performance and reliability.

## Digital Ocean Managed Redis Setup

### 1. Create Redis Cluster

1. Log into Digital Ocean Control Panel
2. Navigate to Databases → Create Database
3. Choose Redis as database engine
4. Select configuration:
   - **Plan**: Basic ($15/month) or Professional ($30/month)
   - **Region**: Choose closest to your app server
   - **Version**: Redis 7.0 or latest
   - **VPC**: Same VPC as your app server

### 2. Configure Connection

After creation, you'll receive connection details:

```
Host: your-redis-cluster-do-user-123456-0.db.ondigitalocean.com
Port: 25061
Password: your-redis-password
Database: 0
```

### 3. Update Environment Configuration

Update your `.env` file with Redis settings:

```env
# Cache Configuration
CACHE_STORE=redis
CACHE_PREFIX=enrollassess-cache-

# Redis Configuration
REDIS_HOST=your-redis-cluster-do-user-123456-0.db.ondigitalocean.com
REDIS_PASSWORD=your-redis-password
REDIS_PORT=25061
REDIS_DB=0
REDIS_CACHE_CONNECTION=cache
REDIS_CACHE_PREFIX=enrollassess-cache-

# Session Configuration
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Queue Configuration
QUEUE_CONNECTION=redis
```

## Redis Configuration Files

### 1. Update config/database.php

Ensure Redis connection is properly configured:

```php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),
    'options' => [
        'cluster' => env('REDIS_CLUSTER', 'redis'),
        'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
    ],
    'default' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'username' => env('REDIS_USERNAME'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_DB', '0'),
    ],
    'cache' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'username' => env('REDIS_USERNAME'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_CACHE_DB', '1'),
    ],
],
```

### 2. Update config/cache.php

Ensure cache configuration uses Redis:

```php
'default' => env('CACHE_STORE', 'redis'),

'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
        'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'default'),
    ],
],
```

### 3. Update config/session.php

Configure sessions to use Redis:

```php
'driver' => env('SESSION_DRIVER', 'redis'),
'lifetime' => env('SESSION_LIFETIME', 120),
'expire_on_close' => false,
'encrypt' => false,
'files' => storage_path('framework/sessions'),
'connection' => env('SESSION_CONNECTION'),
'table' => 'sessions',
'store' => env('SESSION_STORE'),
'lottery' => [2, 100],
'cookie' => env('SESSION_COOKIE', Str::slug(env('APP_NAME', 'laravel'), '_').'_session'),
'path' => '/',
'domain' => env('SESSION_DOMAIN'),
'secure' => env('SESSION_SECURE_COOKIE', true),
'http_only' => true,
'same_site' => 'lax',
```

## Performance Optimization

### 1. Redis Memory Configuration

For Digital Ocean Managed Redis, configure memory policies:

```bash
# Set max memory policy to allkeys-lru for cache eviction
CONFIG SET maxmemory-policy allkeys-lru

# Set max memory to 80% of available memory
CONFIG SET maxmemory 256mb
```

### 2. Connection Pooling

Configure connection pooling in your application:

```php
// In config/database.php
'redis' => [
    'options' => [
        'parameters' => [
            'persistent' => true,
            'timeout' => 30,
            'read_timeout' => 30,
            'retry_interval' => 100,
        ],
    ],
],
```

### 3. Cache Warming

Use the provided artisan command to warm up cache:

```bash
php artisan cache:warm
```

## Monitoring and Maintenance

### 1. Redis Monitoring

Digital Ocean provides built-in monitoring:
- Memory usage
- Connection count
- Commands per second
- Hit ratio

### 2. Health Checks

Use the system health command:

```bash
php artisan system:health
```

### 3. Cache Statistics

Check cache performance:

```bash
php artisan tinker
>>> app(\App\Services\CacheService::class)->getStatistics()
```

## Troubleshooting

### Common Issues

1. **Connection Refused**
   - Check firewall rules
   - Verify VPC configuration
   - Confirm connection details

2. **Authentication Failed**
   - Verify Redis password
   - Check username configuration

3. **Memory Issues**
   - Monitor memory usage
   - Adjust eviction policy
   - Scale up Redis plan if needed

### Debug Commands

```bash
# Test Redis connection
php artisan tinker
>>> Redis::ping()

# Check cache driver
php artisan config:show cache

# Clear all cache
php artisan cache:clear

# View Redis info
php artisan tinker
>>> Redis::info()
```

## Security Considerations

1. **Network Security**
   - Use VPC for private networking
   - Enable SSL/TLS connections
   - Restrict access to app servers only

2. **Authentication**
   - Use strong passwords
   - Rotate passwords regularly
   - Enable AUTH command

3. **Data Protection**
   - Enable Redis AUTH
   - Use SSL for data in transit
   - Regular backups

## Backup and Recovery

### 1. Automated Backups

Digital Ocean provides automated backups:
- Daily snapshots
- Point-in-time recovery
- 7-day retention (Basic plan)
- 30-day retention (Professional plan)

### 2. Manual Backups

```bash
# Create manual backup
php artisan backup:run

# Restore from backup
php artisan backup:restore
```

## Scaling Considerations

### 1. Vertical Scaling

- Upgrade Redis plan for more memory
- Increase CPU resources
- Monitor performance metrics

### 2. Horizontal Scaling

- Use Redis Cluster for high availability
- Implement read replicas
- Consider Redis Sentinel for failover

## Cost Optimization

1. **Right-size Resources**
   - Monitor actual usage
   - Scale down during low traffic
   - Use appropriate plan tier

2. **Cache Efficiency**
   - Optimize cache keys
   - Set appropriate TTL values
   - Monitor hit ratios

3. **Data Retention**
   - Clean up expired keys
   - Use appropriate eviction policies
   - Regular maintenance

## Next Steps

1. Deploy with Redis configuration
2. Monitor performance metrics
3. Optimize based on usage patterns
4. Set up alerts for critical issues
5. Plan for scaling as needed


