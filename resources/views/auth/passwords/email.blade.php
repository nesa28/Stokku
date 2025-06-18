@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 p-8 bg-white rounded-lg shadow-xl text-center">
        <div>
            <h2 class="mt-6 text-2xl font-extrabold text-gray-900">
                {{ __('Reset Password') }}
            </h2>
        </div>

        @if (session('status'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                {{ session('status') }}
            </div>
        @endif

        <form class="mt-8 space-y-6" method="POST" action="{{ route('password.email') }}">
            @csrf

            {{-- Email Address Input --}}
            <div>
                <label for="email" class="sr-only">{{ __('Email Address') }}</label>
                <input id="email" name="email" type="email" autocomplete="email" required autofocus
                       class="relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm
                       @error('email') border-red-500 @enderror"
                       placeholder="{{ __('Email Address') }}"
                       value="{{ old('email') }}">
                @error('email')
                    <p class="mt-2 text-sm text-red-600" role="alert">
                        <strong>{{ $message }}</strong>
                    </p>
                @enderror
            </div>

            {{-- Submit Button --}}
            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    {{ __('Send Password Reset Link') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
