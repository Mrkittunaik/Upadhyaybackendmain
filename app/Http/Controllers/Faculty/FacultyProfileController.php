<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateFacultyProfileRequest;
use App\Http\Resources\FacultyProfileResource;
use App\Models\FacultyProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacultyProfileController extends Controller
{
    private const PROFILE_FIELDS = [
        'designation', 'department', 'college', 'qualification',
        'experience_years', 'bio', 'location', 'city',
    ];

    public function show(Request $request): JsonResponse
    {
        return $this->ok($this->resource($this->profileFor($request->user())));
    }

    public function update(UpdateFacultyProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $profile = $this->profileFor($user);

        DB::transaction(function () use ($user, $profile, $data) {
            $userFields = array_intersect_key($data, array_flip(['name', 'phone']));
            if ($userFields) {
                $user->update($userFields);
            }

            $profile->update(array_intersect_key($data, array_flip(self::PROFILE_FIELDS)));

            // Arrays, when sent, fully replace the stored list.
            if (array_key_exists('education', $data)) {
                $profile->education()->delete();
                $profile->education()->createMany($data['education']);
            }
            if (array_key_exists('experience', $data)) {
                $profile->experience()->delete();
                $profile->experience()->createMany($data['experience']);
            }
            if (array_key_exists('skills', $data)) {
                $profile->skills()->delete();
                $skills = collect($data['skills'])->map(fn ($s) => trim($s))->filter()->unique(fn ($s) => mb_strtolower($s));
                $profile->skills()->createMany($skills->map(fn ($s) => ['skill' => $s])->values()->all());
            }
        });

        return $this->ok($this->resource($profile->fresh()), 'Profile updated');
    }

    private function profileFor(User $user): FacultyProfile
    {
        return FacultyProfile::firstOrCreate(['user_id' => $user->id]);
    }

    private function resource(FacultyProfile $profile): FacultyProfileResource
    {
        return new FacultyProfileResource($profile->load('education', 'experience', 'skills'));
    }
}
