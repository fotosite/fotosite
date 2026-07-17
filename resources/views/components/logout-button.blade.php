@props(['userType'])

@php
    $userId = $userType === 'mand' ? session('_mand_id') : session('_cust_id');

    $hasTrustedDevice = $userId
        ? \App\Models\SessionDb\TrustedDevice::where('user_type', $userType)
            ->where('user_id', $userId)
            ->where('expires_at', '>', now())
            ->exists()
        : false;

    $logoutRoute = $userType === 'mand' ? route('mandant.logout') : route('customer.logout');
@endphp

<div class="flex items-center" x-data="{ showConfirm: false }">
    <form method="POST" action="{{ $logoutRoute }}"
          @submit.prevent="{{ $hasTrustedDevice ? 'showConfirm = true' : '$el.submit()' }}">
        @csrf
        <button type="submit"
                class="min-h-11 py-2 px-3 text-sm text-gray-400 hover:text-red-500
                       transition-colors duration-150 tracking-wide select-none">
            Abmelden
        </button>
    </form>

    @if($hasTrustedDevice)
    <div x-show="showConfirm" x-cloak
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 max-w-sm mx-4 shadow-lg" @click.outside="showConfirm = false">
            <p class="text-sm text-gray-700 mb-5">
                Dieses Gerät ist als sicher gespeichert. Möchtest du dies löschen?
            </p>
            <div class="flex gap-3 justify-end">
                <button type="button" @click="showConfirm = false"
                        class="text-sm text-gray-500 hover:text-gray-700 select-none">
                    Zurück
                </button>
                <form method="POST" action="{{ $logoutRoute }}">
                    @csrf
                    <input type="hidden" name="delete_trusted_device" value="1">
                    <button type="submit"
                            class="text-sm text-red-600 hover:text-red-800 font-medium select-none">
                        Abmelden mit Löschen
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
