<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Acceder a Piso Cuatro')" :description="__('Ingresa tus credenciales para continuar')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
            @csrf

            <!-- Email Address -->
            <div class="space-y-2">
                <label class="text-xs font-medium uppercase tracking-wider text-muted">{{ __('Email') }}</label>
                <input
                    name="email"
                    type="email"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="usuario@example.com"
                    value="{{ old('email') }}"
                    class="input-base"
                />
                @error('email')
                    <p class="text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="space-y-2">
                <label class="text-xs font-medium uppercase tracking-wider text-muted">{{ __('Contraseña') }}</label>
                <input
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="input-base"
                />
                @error('password')
                    <p class="text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me -->
            <label class="flex items-center gap-2 text-sm text-muted cursor-pointer hover:text-[var(--piso-fg)]">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} class="rounded border-zinc-700 bg-zinc-900 text-amber-500 focus:ring-amber-400/50">
                <span>{{ __('Recuérdame') }}</span>
            </label>

            <button type="submit" class="btn-primary w-full justify-center py-3">
                {{ __('Acceder') }}
            </button>
        </form>

        @if (Route::has('password.request'))
            <div class="text-center">
                <a href="{{ route('password.request') }}" wire:navigate class="text-sm text-amber-400 hover:text-amber-300">
                    {{ __('¿Olvidaste tu contraseña?') }}
                </a>
            </div>
        @endif

        <div class="text-center text-sm text-zinc-400">
            <span>{{ __('¿Necesitas una cuenta?') }}</span>
            <a href="{{ route('register') }}" wire:navigate class="text-amber-400 hover:text-amber-300">
                {{ __('Regístrate aquí') }}
            </a>
        </div>
    </div>
</x-layouts::auth>
