<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\Auth\AuthTokenData;
use App\Data\Auth\LoginOtpData;
use App\Data\Auth\LoginPasswordData;
use App\Data\Auth\RegisterData;
use App\Data\Auth\SendOtpData;
use App\Data\Auth\SendOtpResponseData;
use App\Data\Auth\UserData;
use App\Http\Requests\Auth\LoginOtpRequest;
use App\Http\Requests\Auth\LoginPasswordRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Exceptions\InvalidCredentialsException;
use App\Services\AuthService;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Throwable;

#[OA\Tag(name: 'Auth', description: 'Authentication: OTP, password login, logout')]
final readonly class AuthController extends Controller
{
    public function __construct(
        private OtpService $otpService,
        private AuthService $authService,
    ) {}

    /** @throws Throwable */
    #[OA\Post(
        path: '/api/auth/send-otp',
        summary: 'Send OTP code to email',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com')]
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Verification code sent'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 429, description: 'Rate limit'),
        ]
    )]
    public function sendOtp(SendOtpRequest $request): SendOtpResponseData
    {
        $data = SendOtpData::from($request->validated());
        $this->otpService->send($data->email);
        return new SendOtpResponseData(message: 'Verification code sent.');
    }

    /** @throws Throwable */
    #[OA\Post(
        path: '/api/auth/login-otp',
        summary: 'Login via OTP code',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['identifier', 'code'],
                properties: [
                    new OA\Property(property: 'identifier', type: 'string', example: 'user@example.com'),
                    new OA\Property(property: 'code', type: 'string', example: '123456'),
                ]
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Token issued'),
            new OA\Response(response: 400, description: 'Invalid or expired OTP'),
        ]
    )]
    public function loginOtp(LoginOtpRequest $request): AuthTokenData
    {
        $data = LoginOtpData::from($request->validated());
        return $this->authService->loginWithOtpAndCreateToken($data->identifier, $data->code);
    }

    /** @throws InvalidCredentialsException|Throwable */
    #[OA\Post(
        path: '/api/auth/login-password',
        summary: 'Login via login and password',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['login_or_email', 'password'],
                properties: [
                    new OA\Property(property: 'login_or_email', type: 'string', example: 'john-doe or user@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                ]
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Token issued'),
            new OA\Response(response: 401, description: 'Invalid credentials'),
        ]
    )]
    public function loginPassword(LoginPasswordRequest $request): AuthTokenData
    {
        $data = LoginPasswordData::from($request->validated());
        return $this->authService->loginWithPassword($data->loginOrEmail, $data->password);
    }

    /** @throws Throwable */
    #[OA\Post(
        path: '/api/auth/register',
        summary: 'Register with email, login and password',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'login', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'login', type: 'string', example: 'john-doe'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
                ]
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 201, description: 'User created, token issued'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = RegisterData::from($request->validated());
        $authToken = $this->authService->registerUser($data->email, $data->login, $data->password);
        return response()->json($authToken->toArray(), 201);
    }

    #[OA\Get(
        path: '/api/auth/check-login',
        summary: 'Check if login is available (unique)',
        tags: ['Auth'],
        parameters: [new OA\Parameter(name: 'login', in: 'query', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: 'Returns { "available": true|false }'),
        ]
    )]
    public function checkLogin(Request $request): JsonResponse
    {
        $login = $request->query('login', '');
        $available = is_string($login) && $login !== '' && $this->authService->isLoginAvailable($login);
        return response()->json(['available' => $available]);
    }

    #[OA\Get(
        path: '/api/auth/me',
        summary: 'Get current user',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Current user data'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function me(Request $request): UserData
    {
        return UserData::fromModel($request->user());
    }

    #[OA\Post(
        path: '/api/auth/logout',
        summary: 'Logout (revoke current token)',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Logged out'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }
}
