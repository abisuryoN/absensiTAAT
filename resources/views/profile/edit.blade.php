<x-app-layout>
    <x-slot name="header">
        <h2 class="fw-semibold fs-4 text-dark">
            <i class="bi bi-person-circle me-2 text-primary"></i>{{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid px-4">
            <div class="row g-4">
                <div class="col-12">
                    <div class="profile-section">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="col-12">
                    <div class="profile-section">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <div class="col-12">
                    <div class="profile-section">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>