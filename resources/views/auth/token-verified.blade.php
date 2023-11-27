<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a>
                <x-application-logo class="w-auto h-20 fill-current text-gray-500" />
            </a>
        </x-slot>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form>
            @csrf

            <!-- Email Address -->
            <div style="text-align: center">
                <x-input-label style="margin-top: 35px;">¡El token ya ha sido usado y/o ha expirado!</x-input-label>
            </div>

            <div class="flex items-center justify-end mt-4">
                <div style="margin-right: 10px;">
                    <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                        {{ __('Atrás') }}
                    </a>
                </div>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>
