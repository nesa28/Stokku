@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 p-8 bg-white rounded-lg shadow-xl text-center">
        <div>
            <h2 class="mt-6 text-2xl font-extrabold text-gray-900">
                {{ __('Confirm Password') }}
            </h2>
        </div>

        <p class="text-gray-700">
            {{ __('Please confirm your password before continuing.') }}
        </p>

        <form class="mt-8 space-y-6" method="POST" action="{{ route('password.confirm') }}">
            @csrf

            {{-- Password Input --}}
            <div>
                <label for="password" class="sr-only">{{ __('Password') }}</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required
                       class="relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm
                       @error('password') border-red-500 @enderror"
                       placeholder="{{ __('Password') }}">
                @error('password')
                    <p class="mt-2 text-sm text-red-600" role="alert">
                        <strong>{{ $message }}</strong>
                    </p>
                @enderror
            </div>

            {{-- Submit Button & Forgot Password Link --}}
            <div class="flex items-center justify-between mt-6"> {{-- Adjusted margin-top --}}
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    {{ __('Confirm Password') }}
                </button>
            </div>

            @if (Route::has('password.request'))
                <div class="text-sm mt-4"> {{-- Added margin-top --}}
                    <a class="font-medium text-blue-600 hover:text-blue-500" href="{{ route('password.request') }}">
                        {{ __('Forgot Your Password?') }}
                    </a>
                </div>
            @endif
        </form>
    </div>
</div>
@endsection
