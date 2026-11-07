<?php

namespace Tests\Feature;

use App\Models\PetugasPiket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_guru_piket_profile_name_updates_the_active_piket_session(): void
    {
        Role::firstOrCreate(['name' => 'guru_piket', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->assignRole('guru_piket');
        $oldPetugas = PetugasPiket::create(['nama_lengkap' => 'Nama Lama']);

        $response = $this
            ->actingAs($user)
            ->withSession([
                'piket_petugas_id' => $oldPetugas->id,
                'piket_nama_lengkap' => 'Nama Lama',
            ])
            ->patch('/profile', [
                'name' => 'Nama Baru',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertSessionHas('piket_nama_lengkap', 'Nama Baru')
            ->assertSessionHas('piket_petugas_id');

        $newPetugasId = $response->getSession()->get('piket_petugas_id');
        $this->assertNotSame($oldPetugas->id, $newPetugasId);
        $this->assertSame('Nama Lama', $oldPetugas->fresh()->nama_lengkap);
        $this->assertSame('Nama Baru', PetugasPiket::findOrFail($newPetugasId)->nama_lengkap);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertTrue($user->fresh()->trashed());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
