<?php

namespace App\Services;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Secretary;
use App\Models\Manager;
use App\Models\MedicalCenter;

class JwtTokenService
{
    /**
     * Validate a JWT token and return detailed information
     */
    public function validateToken($token)
    {
        try {
            JWTAuth::setToken($token);
            $payload = JWTAuth::getPayload();

            $guard = $payload->get('guard');
            $userId = $payload->get('sub');

            $result = [
                'valid' => true,
                'guard' => $guard,
                'user_id' => $userId,
                'has_guard' => !empty($guard),
                'has_user_id' => !empty($userId),
                'expires_at' => $payload->get('exp'),
                'issued_at' => $payload->get('iat'),
            ];

            // Check if user exists in database
            if ($userId) {
                $user = $this->findUserByGuardAndId($guard, $userId);
                $result['user_exists'] = $user !== null;
                $result['user_model'] = $user ? get_class($user) : null;
            } else {
                $result['user_exists'] = false;
                $result['user_model'] = null;
            }

            return $result;

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage(),
                'error_type' => get_class($e)
            ];
        }
    }

    /**
     * Find user by guard and ID
     */
    private function findUserByGuardAndId($guard, $userId)
    {
        switch ($guard) {
            case 'api':
            case 'custom-auth.jwt':
                return User::find($userId);
            case 'doctor-api':
                return Doctor::find($userId);
            case 'secretary-api':
                return Secretary::find($userId);
            case 'manager-api':
                return Manager::find($userId);
            case 'medical_center-api':
                return MedicalCenter::find($userId);
            default:
                return null;
        }
    }

    /**
     * Create a JWT token with proper validation
     */
    public function createToken($user, $guard)
    {
        try {
            // Validate that user exists and has an ID
            if (!$user || !$user->id) {
                Log::error('JWT token creation failed: Invalid user', [
                    'user' => $user ? get_class($user) : 'null',
                    'user_id' => $user ? $user->id : 'null',
                    'guard' => $guard
                ]);
                throw new \Exception('Invalid user for JWT token creation');
            }

            // Validate guard configuration
            if (!array_key_exists($guard, config('auth.guards'))) {
                Log::error('JWT token creation failed: Invalid guard', [
                    'guard' => $guard,
                    'available_guards' => array_keys(config('auth.guards'))
                ]);
                throw new \Exception('Invalid guard for JWT token creation');
            }

            // Create token with guard information
            $customClaims = ['guard' => $guard];
            $token = JWTAuth::claims($customClaims)->fromUser($user);

            Log::info('JWT token created successfully', [
                'user_id' => $user->id,
                'user_type' => get_class($user),
                'guard' => $guard
            ]);

            return $token;

        } catch (\Exception $e) {
            Log::error('JWT token creation failed', [
                'error' => $e->getMessage(),
                'user_id' => $user ? $user->id : 'null',
                'guard' => $guard
            ]);
            throw $e;
        }
    }

    /**
     * Invalidate a JWT token
     */
    public function invalidateToken($token)
    {
        try {
            JWTAuth::setToken($token);
            JWTAuth::invalidate($token);

            Log::info('JWT token invalidated successfully');
            return true;

        } catch (\Exception $e) {
            Log::error('JWT token invalidation failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Refresh a JWT token
     */
    public function refreshToken($token)
    {
        try {
            JWTAuth::setToken($token);
            $newToken = JWTAuth::refresh();

            Log::info('JWT token refreshed successfully');
            return $newToken;

        } catch (\Exception $e) {
            Log::error('JWT token refresh failed', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get user from token with validation
     */
    public function getUserFromToken($token)
    {
        try {
            $validation = $this->validateToken($token);

            if (!$validation['valid']) {
                return null;
            }

            if (!$validation['user_exists']) {
                Log::warning('JWT token user not found in database', $validation);
                return null;
            }

            return $this->findUserByGuardAndId($validation['guard'], $validation['user_id']);

        } catch (\Exception $e) {
            Log::error('Failed to get user from JWT token', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
