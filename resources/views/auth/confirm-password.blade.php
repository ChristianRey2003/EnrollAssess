<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div class="mt-4">
            <x-form.input 
                id="password" 
                class="block mt-1 w-full" 
                type="password" 
                name="password" 
                required 
                autocomplete="current-password" 
                label="{{ __('Password') }}"
                error="{{ $errors->first('password') }}"
            />
        </div>

        <div class="flex justify-end mt-4">
            <x-button variant="primary">
                {{ __('Confirm') }}
            </x-button>
        </div>
    </form>
</x-guest-layout>
