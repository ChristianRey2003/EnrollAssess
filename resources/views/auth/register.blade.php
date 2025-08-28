<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-form.input 
                id="name" 
                class="block mt-1 w-full" 
                type="text" 
                name="name" 
                :value="old('name')" 
                required 
                autofocus 
                autocomplete="name" 
                label="{{ __('Name') }}"
                error="{{ $errors->first('name') }}"
            />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-form.input 
                id="email" 
                class="block mt-1 w-full" 
                type="email" 
                name="email" 
                :value="old('email')" 
                required 
                autocomplete="username" 
                label="{{ __('Email') }}"
                error="{{ $errors->first('email') }}"
            />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-form.input 
                id="password" 
                class="block mt-1 w-full"
                type="password"
                name="password"
                required 
                autocomplete="new-password" 
                label="{{ __('Password') }}"
                error="{{ $errors->first('password') }}"
            />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-form.input 
                id="password_confirmation" 
                class="block mt-1 w-full"
                type="password"
                name="password_confirmation" 
                required 
                autocomplete="new-password" 
                label="{{ __('Confirm Password') }}"
                error="{{ $errors->first('password_confirmation') }}"
            />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-button variant="primary" class="ms-4">
                {{ __('Register') }}
            </x-button>
        </div>
    </form>
</x-guest-layout>
