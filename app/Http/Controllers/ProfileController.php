<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
            'remove_profile_image' => ['nullable', 'boolean'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->address = $validated['address'] ?? null;

        if ($request->boolean('remove_profile_image')) {
            $this->deleteProfileImage($user);
            $user->profile_image = null;
        }

        if ($request->hasFile('profile_image')) {
            $this->deleteProfileImage($user);

            $extension = $request->file('profile_image')->getClientOriginalExtension();
            $path = $request->file('profile_image')->storeAs(
                'profile-images',
                $user->id . '.' . strtolower($extension),
                'public'
            );

            $user->profile_image = $path;
        }

        $user->save();

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Profile updated.');
    }

    private function deleteProfileImage($user): void
    {
        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }
    }
}
