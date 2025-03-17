<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $emailSent = false;
    public bool $emailFailed = false;

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        
        // Fire the Registered event - this will trigger the email verification
        // through the SendEmailVerificationNotification listener
        try {
            event(new Registered($user));
            $this->emailSent = true;
            Session::flash('status', 'verification-link-sent');
        } catch (\Exception $e) {
            $this->emailFailed = true;
            Session::flash('email-error', 'Failed to send verification email. Please try again.');
        }

        Auth::login($user);

        $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    }
    
    /**
     * Resend verification email
     */
    public function resendVerificationEmail(): void
    {
        if (!Auth::user()) {
            return;
        }
        
        try {
            Auth::user()->sendEmailVerificationNotification();
            $this->emailSent = true;
            $this->emailFailed = false;
            Session::flash('status', 'verification-link-sent');
        } catch (\Exception $e) {
            $this->emailFailed = true;
            Session::flash('email-error', 'Failed to send verification email. Please try again.');
        }
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />
    
    <!-- Email Error Message -->
    @if(session('email-error') || $emailFailed)
    <div class="rounded-md bg-red-50 p-4 dark:bg-red-900/50">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400 dark:text-red-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('email-error') ?? 'Failed to send verification email.' }}</h3>
                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                    <p>{{ __('Did not receive email?') }} 
                        <button type="button" wire:click="resendVerificationEmail" class="font-medium text-red-600 underline dark:text-red-400 hover:text-red-500">
                            {{ __('Click here to resend') }}
                        </button>
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <form wire:submit="register" class="flex flex-col gap-6">
        <!-- Name -->
        <flux:input
            wire:model="name"
            :label="__('Name')"
            type="text"
            required
            autofocus
            autocomplete="name"
            :placeholder="__('Full name')"
        />

        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Email address')"
            type="email"
            required
            autocomplete="email"
            placeholder="email@example.com"
        />

        <!-- Password -->
        <flux:input
            wire:model="password"
            :label="__('Password')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Password')"
        />

        <!-- Confirm Password -->
        <flux:input
            wire:model="password_confirmation"
            :label="__('Confirm password')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Confirm password')"
        />

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Create account') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 text-center text-sm text-zinc-600 dark:text-zinc-400">
        {{ __('Already have an account?') }}
        <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
    </div>
</div>
