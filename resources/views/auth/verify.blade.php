@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 p-8 bg-white rounded-lg shadow-xl text-center"> {{-- Centered text for cleaner look --}}
        <div>
            <h2 class="mt-6 text-2xl font-extrabold text-gray-900">
                {{ __('Verify Your Email Address') }}
            </h2>
        </div>

        <div class="mt-8 space-y-6">
            @if (session('resent'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    {{ __('A fresh verification link has been sent to your email address.') }}
                </div>
            @endif

            <p class="text-gray-700">
                {{ __('Before proceeding, please check your email for a verification link.') }}
            </p>
            <p class="text-gray-700">
                {{ __('If you did not receive the email') }},
                <form class="inline" method="POST" action="{{ route('verification.resend') }}">
                    @csrf
                    <button type="submit"
                            class="font-medium text-blue-600 hover:text-blue-500 hover:underline focus:outline-none focus:underline">
                        {{ __('click here to request another') }}
                    </button>.
                </form>
            </p>
        </div>
    </div>
</div>
@endsection
