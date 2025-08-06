# JWT Authentication Troubleshooting Guide

## Overview

This document provides solutions for the JWT authentication issues you're experiencing:

1. **JWT token contains user ID that doesn't exist in database: null**
2. **Search request from unauthenticated user**

## Root Causes Identified

### 1. Null User ID in JWT Tokens

**Problem**: JWT tokens are being created or decoded with null user IDs.

**Possible Causes**:
- User was deleted from database after token creation
- Token creation failed but returned a token anyway
- Database transaction issues during user creation
- Guard configuration mismatch

### 2. Unauthenticated Search Requests

**Problem**: Users are making search requests without valid JWT tokens.

**Note**: This is actually expected behavior for public search functionality, but the logs are being generated.

## Solutions Implemented

### 1. Enhanced JWT Middleware

The `JwtMiddleware` has been improved with:

- **Better Error Handling**: More detailed logging for debugging
- **Null User ID Detection**: Explicit checks for null user IDs
- **Guard Validation**: Proper guard configuration validation
- **User Existence Verification**: Double-checking user exists in database

### 2. JWT Token Service

A new `JwtTokenService` class provides:

- **Token Validation**: Comprehensive token validation with detailed results
- **Safe Token Creation**: Validates user and guard before creating tokens
- **User Lookup**: Proper user lookup by guard and ID
- **Error Logging**: Detailed logging for debugging

### 3. Improved Search Controller

The `SearchController` now:

- **Better Token Validation**: Uses the JWT service for validation
- **Graceful Failure Handling**: Continues with unauthenticated access when tokens fail
- **Detailed Logging**: More informative log messages

### 4. Enhanced Auth Controller

The `AuthController` now:

- **Safe Token Creation**: Uses the JWT service for token creation
- **Better Error Handling**: Proper error handling during login
- **Improved Logout**: Uses the JWT service for token invalidation

## Debugging Tools

### 1. Artisan Command

Run this command to check for invalid tokens:

```bash
php artisan jwt:cleanup-invalid-tokens --dry-run
```

This will:
- Check for users with null IDs
- Look for orphaned tokens (users that no longer exist)
- Provide recommendations for cleanup

### 2. Debug API Endpoint

Use this endpoint to validate JWT tokens:

```bash
POST /api/debug/jwt-validate
Content-Type: application/json

{
    "token": "your-jwt-token-here"
}
```

This will return detailed information about the token including:
- Token validity
- User ID and guard
- Whether user exists in database
- Token expiration information

## Immediate Actions to Take

### 1. Check Current Token Issues

```bash
# Run the cleanup command to see what issues exist
php artisan jwt:cleanup-invalid-tokens --dry-run
```

### 2. Monitor Logs

Watch for these new log messages:

- `JWT middleware: Token payload extracted` - Shows token contents
- `JWT middleware: User not found in database` - Indicates deleted users
- `JWT middleware: Missing required token information` - Indicates malformed tokens

### 3. Test Token Validation

Use the debug endpoint to validate existing tokens:

```bash
curl -X POST http://your-domain/api/debug/jwt-validate \
  -H "Content-Type: application/json" \
  -d '{"token":"your-token-here"}'
```

## Prevention Measures

### 1. User Deletion Cleanup

When deleting users, ensure you:

```php
// In your user deletion logic
public function deleteUser($userId)
{
    $user = User::find($userId);
    
    if ($user) {
        // Invalidate all tokens for this user
        $jwtService = new JwtTokenService();
        
        // You might want to store active tokens in a separate table
        // and invalidate them here
        
        $user->delete();
    }
}
```

### 2. Token Creation Validation

Always validate before creating tokens:

```php
$jwtService = new JwtTokenService();

try {
    $token = $jwtService->createToken($user, $guard);
} catch (\Exception $e) {
    // Handle the error appropriately
    Log::error('Token creation failed', [
        'user_id' => $user->id,
        'error' => $e->getMessage()
    ]);
}
```

### 3. Regular Token Cleanup

Set up a scheduled task to clean up invalid tokens:

```php
// In your App\Console\Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('jwt:cleanup-invalid-tokens')
             ->daily()
             ->withoutOverlapping();
}
```

## Configuration Recommendations

### 1. JWT Configuration

Ensure your `config/jwt.php` has proper settings:

```php
'ttl' => env('JWT_TTL', 60), // Token lifetime in minutes
'refresh_ttl' => env('JWT_REFRESH_TTL', 20160), // Refresh token lifetime
'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),
'blacklist_grace_period' => env('JWT_BLACKLIST_GRACE_PERIOD', 0),
```

### 2. Environment Variables

Add these to your `.env` file:

```env
JWT_SECRET=your-secret-key
JWT_TTL=60
JWT_REFRESH_TTL=20160
JWT_BLACKLIST_ENABLED=true
JWT_BLACKLIST_GRACE_PERIOD=0
```

## Monitoring and Alerts

### 1. Log Monitoring

Monitor these log patterns:

```bash
# Check for null user ID issues
grep "JWT token contains user ID that doesn't exist in database: null" storage/logs/laravel.log

# Check for authentication failures
grep "JWT authentication failed" storage/logs/laravel.log

# Check for successful authentications
grep "JWT middleware: User authenticated successfully" storage/logs/laravel.log
```

### 2. Metrics to Track

- Number of failed JWT authentications
- Number of null user ID tokens
- Number of unauthenticated search requests
- Token creation success rate

## Expected Behavior After Fixes

### 1. Reduced Error Logs

You should see fewer:
- `JWT token contains user ID that doesn't exist in database: null`
- `JWT authentication failed in search`

### 2. Better Error Messages

Instead of generic errors, you'll see:
- `JWT middleware: User not found in database`
- `JWT middleware: Missing required token information`
- `JWT middleware: Token payload extracted`

### 3. Graceful Degradation

The search functionality will:
- Work for authenticated users (with user-specific features)
- Work for unauthenticated users (with public features)
- Log appropriate messages for each case

## Testing the Fixes

### 1. Test Token Creation

```bash
# Test login and token creation
curl -X POST http://your-domain/api/auth/login-register \
  -H "Content-Type: application/json" \
  -d '{"mobile":"09123456789"}'

# Then test the token
curl -X POST http://your-domain/api/debug/jwt-validate \
  -H "Content-Type: application/json" \
  -d '{"token":"the-token-from-login"}'
```

### 2. Test Search Functionality

```bash
# Test authenticated search
curl -X GET "http://your-domain/api/v2/search?search_text=test" \
  -H "Authorization: Bearer your-token"

# Test unauthenticated search
curl -X GET "http://your-domain/api/v2/search?search_text=test"
```

## Support and Maintenance

### 1. Regular Maintenance

- Run the cleanup command weekly
- Monitor logs for new patterns
- Update JWT configuration as needed

### 2. Performance Considerations

- The enhanced logging may slightly impact performance
- Consider reducing log level in production
- Monitor token validation performance

### 3. Security Considerations

- Remove the debug endpoint in production
- Regularly rotate JWT secrets
- Monitor for token abuse

## Conclusion

These improvements should significantly reduce the JWT authentication issues you're experiencing. The enhanced logging will help you identify and resolve any remaining issues quickly.

For ongoing support, monitor the logs and use the provided debugging tools to maintain a healthy JWT authentication system. 