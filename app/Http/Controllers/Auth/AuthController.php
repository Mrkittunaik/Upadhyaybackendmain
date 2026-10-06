<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(private CompanyService $companies) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'role' => $data['role'],
                'status' => UserStatus::Active,
            ]);

            if ($user->role === UserRole::Faculty) {
                $user->facultyProfile()->create([]);
            }

            if ($user->role === UserRole::CompanyMain) {
                $this->companies->createFor($user, array_merge($data, ['name' => $data['company_name']]));
            }

            return $user;
        });

        return $this->created($this->payload($user), 'Registration successful');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', strtolower($request->string('email')->toString()))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return $this->fail('Invalid email or password', 401);
        }
        if ($user->status !== UserStatus::Active) {
            return $this->fail('Your account is not active', 403);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return $this->ok($this->payload($user), 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->ok(null, 'Logged out');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->ok(new UserResource($this->loadForRole($request->user())));
    }

    private function payload(User $user): array
    {
        return [
            'user' => new UserResource($this->loadForRole($user)),
            'token' => $user->createToken('api')->plainTextToken,
            'token_type' => 'Bearer',
        ];
    }

    private function loadForRole(User $user): User
    {
        return match ($user->role) {
            UserRole::Faculty => $user->load('facultyProfile.education', 'facultyProfile.experience', 'facultyProfile.skills'),
            UserRole::CompanyMain => $user->load('company.profile'),
            UserRole::CompanyBranch => $user->load('branch.company.profile'),
            default => $user,
        };
    }
}
