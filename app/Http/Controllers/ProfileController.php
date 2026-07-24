<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Buglinjo\LaravelWebp\Facades\WebP;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Upload / ganti foto profil user (AJAX, returns JSON).
     */
    public function updatePhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
        ], [
            'photo.required' => 'Pilih foto terlebih dahulu.',
            'photo.image'    => 'File harus berupa gambar.',
            'photo.mimes'    => 'Format yang diizinkan: JPG, JPEG, PNG, WEBP.',
            'photo.max'      => 'Ukuran foto maksimal 1 MB.',
        ]);

        $user = $request->user();
        $oldPhoto = $user->profile_photo;

        try {
            DB::beginTransaction();

            // Generate path tujuan
            $uuid     = Str::uuid()->toString();
            $destPath = "profile-photos/{$uuid}.webp";
            $fullPath = storage_path("app/public/{$destPath}");

            // Pastikan direktori ada
            Storage::disk('public')->makeDirectory('profile-photos');

            // Konversi ke WebP menggunakan php-gd
            $webpImage = WebP::make($request->file('photo'))
                ->quality(80);
            $webpImage->save($fullPath);

            // Update database
            $user->profile_photo = $destPath;
            $user->save();

            DB::commit();

            // Hapus foto lama setelah commit
            if ($oldPhoto && Storage::disk('public')->exists($oldPhoto)) {
                Storage::disk('public')->delete($oldPhoto);
            }

            return response()->json([
                'success' => true,
                'url'     => $user->profile_photo_url,
                'message' => 'Foto profil berhasil diperbarui.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            // Hapus file baru jika sudah tersimpan tapi DB gagal
            if (Storage::disk('public')->exists($destPath ?? '')) {
                Storage::disk('public')->delete($destPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengkonversi foto. Pastikan ekstensi GD aktif. (' . $e->getMessage() . ')',
            ], 422);
        }
    }

    /**
     * Hapus foto profil user, kembali ke avatar default (AJAX, returns JSON).
     */
    public function destroyPhoto(Request $request): JsonResponse
    {
        $user     = $request->user();
        $oldPhoto = $user->profile_photo;

        try {
            DB::beginTransaction();

            $user->profile_photo = null;
            $user->save();

            DB::commit();

            // Hapus file setelah commit berhasil
            if ($oldPhoto && Storage::disk('public')->exists($oldPhoto)) {
                Storage::disk('public')->delete($oldPhoto);
            }

            return response()->json([
                'success' => true,
                'url'     => $user->profile_photo_url,
                'message' => 'Foto profil berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus foto profil.',
            ], 500);
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
