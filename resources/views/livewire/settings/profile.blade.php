<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public string $email = '';
    public ?string $gender = '';
    public ?int $age = null;
    public bool $isGithubUser = false;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->gender = $user->gender ?? '';
        $this->age = $user->age ?? null;
        $this->isGithubUser = !empty($user->github_id);
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
            'gender' => ['nullable', 'string', 'in:male,female,other,prefer_not_to_say'],
            'age' => ['nullable', 'integer', 'min:1', 'max:120'],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
    
    /**
     * Send verification email for GitHub users
     * Modified to send verification email instead of immediately verifying
     */
    public function verifyGithubUserEmail(): void
    {
        $user = Auth::user();
        
        if ($user->hasVerifiedEmail() || empty($user->github_id)) {
            return;
        }
        
        // Send verification email instead of immediately verifying
        $user->sendEmailVerificationNotification();
        
        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your profile information')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && auth()->user()->email_verified_at === null)
                    <div>
                        @if ($isGithubUser)
                            <flux:text class="mt-4">
                                {{ __('Your GitHub email address is unverified.') }}

                                <flux:link class="text-sm cursor-pointer" wire:click.prevent="verifyGithubUserEmail">
                                    {{ __('Click here to send a verification email.') }}
                                </flux:link>
                            </flux:text>
                        @else
                            <flux:text class="mt-4">
                                {{ __('Your email address is unverified.') }}

                                <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                    {{ __('Click here to re-send the verification email.') }}
                                </flux:link>
                            </flux:text>
                        @endif

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @elseif (auth()->user()->email_verified_at !== null)
                    <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                        {{ __('Email verified on ') }} {{ auth()->user()->email_verified_at->format('M d, Y') }}
                    </flux:text>
                @endif
            </div>
            
            <div>
                <flux:label for="gender" :value="__('Gender')" />
                <flux:select id="gender" wire:model="gender" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    <option value="">{{ __('Select Gender') }}</option>
                    <option value="male">{{ __('Male') }}</option>
                    <option value="female">{{ __('Female') }}</option>
                </flux:select>
            </div>
            
            <div>
                <flux:input wire:model="age" :label="__('Age')" type="number" min="1" max="120" />
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
