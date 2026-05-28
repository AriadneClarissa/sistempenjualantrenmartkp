<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Resend Verification Email') }}
                </x-primary-button>
            </div>
        </form>

        <form id="logoutForm" method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="button" id="logoutTrigger" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>

    <div id="logoutConfirmModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40">
        <div class="w-full max-w-sm rounded-xl bg-white p-5 shadow-lg">
            <h3 class="text-base font-semibold text-gray-900">Konfirmasi Keluar</h3>
            <p class="mt-2 text-sm text-gray-600">Yakin ingin keluar dari akun?</p>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" id="logoutCancel" class="rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="button" id="logoutConfirm" class="rounded-md bg-red-600 px-3 py-2 text-sm text-white hover:bg-red-700">Ya, Keluar</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const logoutForm = document.getElementById('logoutForm');
            const trigger = document.getElementById('logoutTrigger');
            const modal = document.getElementById('logoutConfirmModal');
            const cancelBtn = document.getElementById('logoutCancel');
            const confirmBtn = document.getElementById('logoutConfirm');

            if (!logoutForm || !trigger || !modal) return;

            const openModal = function () {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            };

            const closeModal = function () {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            };

            trigger.addEventListener('click', openModal);
            if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
            if (confirmBtn) confirmBtn.addEventListener('click', function () {
                logoutForm.submit();
            });

            modal.addEventListener('click', function (e) {
                if (e.target === modal) closeModal();
            });
        });
    </script>
</x-guest-layout>
