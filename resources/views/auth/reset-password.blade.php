<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-form.input 
                id="email" 
                class="block mt-1 w-full" 
                type="email" 
                name="email" 
                :value="old('email', $request->email)" 
                required 
                autofocus 
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
            <x-button variant="primary">
                {{ __('Reset Password') }}
            </x-button>
        </div>
    </form>
</x-guest-layout>
