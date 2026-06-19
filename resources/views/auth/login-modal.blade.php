{{--
    FILE:    resources/views/auth/login-modal.blade.php
    VERSION: 1.4.0
    DATE:    2026-06-19

    DESCRIPTION:
      Startseite / Login-Modal der Fotogalerie.
      Rendert zwei Seiten über Alpine.js page-State:
        page === 'cust' — Customer-Login (Tabs: Kurzzeit-Passwort / Mitglied)
        page === 'mand' — Mandanten-Login

    ROUTES USED:
      POST customer.login.anon            — Gast-Login (Kurzzeit-Kennwort)
      POST customer.login.handle          — Mitglied-Login (E-Mail + PW)
      GET  customer.login.passkey.options — Cust-Passkey-Challenge (JS)
      POST customer.login.passkey         — Cust-Passkey-Assertion (JS)
      POST /mandant/login                 — Mandant-Login (E-Mail + PW)
      GET  mandant.login.passkey.options  — Mand-Passkey-Challenge (JS)
      POST mandant.login.passkey          — Mand-Passkey-Assertion (JS)
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Anmelden</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>[x-cloak]{display:none!important}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-900 flex items-center justify-center px-4">

    {{--
        Alpine-State wird aus old()-Werten vorbelegt, damit nach einem
        Formular-Submit mit Validierungsfehler die richtige Seite / der
        richtige Tab direkt sichtbar ist (kein Zurückspringen zum Default).
    --}}
    <div x-data="{ page: '{{ session('login_page', 'cust') }}', custTab: '{{ session('cust_tab', 'anon') }}' }"
         class="w-full max-w-md bg-white rounded-2xl shadow-2xl px-8 py-10">

        {{-- Flash: abgelaufene Session --}}
        @if (session('session'))
            <div class="mb-5 rounded-lg bg-orange-50 border border-orange-200 px-4 py-3 text-sm text-orange-800">
                {{ session('session') }}
            </div>
        @endif

        {{-- Flash: allgemeine Fehlermeldung (Middleware, Redirect->with('error')) --}}
        @if (session('error'))
            <div class="mb-5 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- Flash: Status (nach Logout, Registrierung etc.) --}}
        @if (session('status'))
            <div class="mb-5 rounded-lg bg-indigo-50 border border-indigo-200 px-4 py-3 text-sm text-indigo-700">
                {{ session('status') }}
            </div>
        @endif


        {{-- ═══════════════════════════════════════════════════════════
             SEITE 1 — Customer Login
        ═══════════════════════════════════════════════════════════ --}}
        <div x-show="page === 'cust'" x-cloak>

            <h2 class="text-xl font-semibold text-gray-800 mb-5">Anmelden</h2>

            {{-- Tab Bar --}}
            <div class="flex border-b border-gray-200 mb-6">
                <button type="button"
                        @click="custTab = 'anon'"
                        :class="custTab === 'anon'
                            ? 'border-b-2 border-indigo-600 text-indigo-600'
                            : 'text-gray-400 hover:text-gray-600'"
                        class="mr-6 pb-2 text-sm font-medium transition-colors">
                    Kurzzeit-Passwort
                </button>
                <button type="button"
                        @click="custTab = 'reg'"
                        :class="custTab === 'reg'
                            ? 'border-b-2 border-indigo-600 text-indigo-600'
                            : 'text-gray-400 hover:text-gray-600'"
                        class="pb-2 text-sm font-medium transition-colors">
                    Mitglied
                </button>
            </div>


            {{-- Tab: Kurzzeit-Passwort --}}
            <div x-show="custTab === 'anon'" x-cloak>
                <form method="POST" action="{{ route('customer.login.anon') }}">
                    @csrf

                    @error('password')
                        <div class="mb-3 text-sm text-red-600">{{ $message }}</div>
                    @enderror

                    <div>
                        <label for="anon_password"
                               class="block text-sm font-medium text-gray-700 mb-1">
                            Passwort
                        </label>
                        <input id="anon_password"
                               type="password"
                               name="password"
                               class="block w-full rounded-lg border-gray-300 shadow-sm
                                      focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                               required autofocus>
                    </div>

                    <div class="mt-5">
                        <button type="submit"
                                class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm
                                       font-semibold text-white hover:bg-indigo-700
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500
                                       focus:ring-offset-2 transition-colors">
                            Anmelden
                        </button>
                    </div>
                </form>
            </div>


            {{-- Tab: Mitglied --}}
            <div x-show="custTab === 'reg'" x-cloak>

                {{-- Cust Passkey-Button (zunächst versteckt) --}}
                <button id="cust-passkey-btn"
                        type="button"
                        onclick="loginWithCustPasskey()"
                        class="hidden w-full rounded-lg border border-indigo-300 bg-indigo-50
                               px-4 py-2.5 text-sm font-semibold text-indigo-700
                               hover:bg-indigo-100 focus:outline-none focus:ring-2
                               focus:ring-indigo-500 focus:ring-offset-2 transition-colors
                               flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg"
                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5
                                 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1
                                 .43-1.563A6 6 0 0 1 21.75 8.25Z"/>
                    </svg>
                    Mit Passkey anmelden
                </button>

                {{-- Trennlinie --}}
                <div id="cust-passkey-divider" class="hidden relative my-5">
                    <div class="absolute inset-0 flex items-center">
                        <hr class="w-full border-gray-200">
                    </div>
                    <div class="relative flex justify-center">
                        <span class="bg-white px-3 text-xs text-gray-400">oder</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('customer.login.handle') }}">
                    @csrf

                    @error('credentials')
                        <div class="mb-3 text-sm text-red-600">{{ $message }}</div>
                    @enderror
                    @error('password')
                        <div class="mb-3 text-sm text-red-600">{{ $message }}</div>
                    @enderror

                    <div>
                        <label for="cust_email"
                               class="block text-sm font-medium text-gray-700 mb-1">
                            E-Mail-Adresse
                        </label>
                        <input id="cust_email"
                               type="email"
                               name="cust_email"
                               value="{{ old('cust_email') }}"
                               autocomplete="username webauthn"
                               class="block w-full rounded-lg border-gray-300 shadow-sm
                                      focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                               required>
                    </div>

                    <div class="mt-4">
                        <label for="cust_password"
                               class="block text-sm font-medium text-gray-700 mb-1">
                            Passwort
                        </label>
                        <input id="cust_password"
                               type="password"
                               name="password"
                               class="block w-full rounded-lg border-gray-300 shadow-sm
                                      focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                               required>
                    </div>

                    <div class="mt-1 text-right">
                        <a href="{{ route('customer.password.reset.request') }}"
                           class="text-xs text-indigo-600 hover:underline">
                            Passwort vergessen?
                        </a>
                    </div>

                    <div class="mt-5">
                        <button type="submit"
                                class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm
                                       font-semibold text-white hover:bg-indigo-700
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500
                                       focus:ring-offset-2 transition-colors">
                            Anmelden
                        </button>
                    </div>
                </form>
            </div>


            {{-- Link: Mandanten-Login --}}
            <div class="mt-7 text-center">
                <button type="button"
                        @click="page = 'mand'"
                        class="text-xs text-gray-400 hover:text-gray-500 transition-colors">
                    Galeristen-Login
                </button>
            </div>

        </div>


        {{-- ═══════════════════════════════════════════════════════════
             SEITE 2 — Mandant Login
        ═══════════════════════════════════════════════════════════ --}}
        <div x-show="page === 'mand'" x-cloak>

            <h2 class="text-xl font-semibold text-gray-800 mb-6">Galeristen-Anmeldung</h2>

            {{-- Passkey-Button (zunächst versteckt; wird per JS eingeblendet) --}}
            <button id="passkey-btn"
                    type="button"
                    onclick="loginWithPasskey()"
                    class="hidden w-full rounded-lg border border-indigo-300 bg-indigo-50
                           px-4 py-2.5 text-sm font-semibold text-indigo-700
                           hover:bg-indigo-100 focus:outline-none focus:ring-2
                           focus:ring-indigo-500 focus:ring-offset-2 transition-colors
                           flex items-center justify-center gap-2">
                <svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg"
                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5
                             17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1
                             .43-1.563A6 6 0 0 1 21.75 8.25Z"/>
                </svg>
                Mit Passkey anmelden
            </button>

            {{-- Trennlinie --}}
            <div id="passkey-divider" class="hidden relative my-5">
                <div class="absolute inset-0 flex items-center">
                    <hr class="w-full border-gray-200">
                </div>
                <div class="relative flex justify-center">
                    <span class="bg-white px-3 text-xs text-gray-400">oder</span>
                </div>
            </div>

            <form method="POST" action="/mandant/login">
                @csrf
                <input type="hidden" name="_form" value="mand">

                @error('mand_email')
                    <div class="mb-3 text-sm text-red-600">{{ $message }}</div>
                @enderror
                @error('password')
                    <div class="mb-3 text-sm text-red-600">{{ $message }}</div>
                @enderror
                @error('credentials')
                    <div class="mb-3 text-sm text-red-600">{{ $message }}</div>
                @enderror

                <div>
                    <label for="mand_email"
                           class="block text-sm font-medium text-gray-700 mb-1">
                        E-Mail-Adresse
                    </label>
                    <input id="mand_email"
                           type="email"
                           name="mand_email"
                           value="{{ old('mand_email') }}"
                           placeholder="ihre@email.de"
                           autocomplete="username webauthn"
                           class="block w-full rounded-lg border-gray-300 shadow-sm
                                  focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                           required autofocus>
                </div>

                <div class="mt-4">
                    <label for="mand_password"
                           class="block text-sm font-medium text-gray-700 mb-1">
                        Passwort
                    </label>
                    <input id="mand_password"
                           type="password"
                           name="password"
                           class="block w-full rounded-lg border-gray-300 shadow-sm
                                  focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                           required>
                </div>

                <div class="mt-1 text-right">
                    <a href="{{ route('mandant.password.reset.request') }}"
                       class="text-xs text-indigo-600 hover:underline">
                        Passwort vergessen?
                    </a>
                </div>

                <div class="mt-5">
                    <button type="submit"
                            class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm
                                   font-semibold text-white hover:bg-indigo-700
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500
                                   focus:ring-offset-2 transition-colors">
                        Anmelden
                    </button>
                </div>
            </form>

            <div class="mt-5">
                <button type="button"
                        @click="page = 'cust'"
                        class="text-xs text-gray-400 hover:text-gray-600 transition-colors">
                    ← Zurück
                </button>
            </div>

        </div>

    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function base64urlToBuffer(base64url) {
            const base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
            const padded = base64.padEnd(base64.length + (4 - base64.length % 4) % 4, '=');
            const binary = atob(padded);
            const bytes  = new Uint8Array(binary.length);
            for (let i = 0; i < binary.length; i++) {
                bytes[i] = binary.charCodeAt(i);
            }
            return bytes.buffer;
        }

        function bufferToBase64url(buffer) {
            const bytes  = new Uint8Array(buffer);
            let   binary = '';
            for (let i = 0; i < bytes.byteLength; i++) {
                binary += String.fromCharCode(bytes[i]);
            }
            return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
        }

        // Passkey-Buttons einblenden wenn Platform Authenticator verfügbar
        document.addEventListener('DOMContentLoaded', async () => {
            if (window.PublicKeyCredential) {
                try {
                    const available = await PublicKeyCredential
                        .isUserVerifyingPlatformAuthenticatorAvailable();
                    if (available) {
                        // Mand-Passkey-Button
                        document.getElementById('passkey-btn').classList.remove('hidden');
                        document.getElementById('passkey-divider').classList.remove('hidden');
                        // Cust-Passkey-Button
                        document.getElementById('cust-passkey-btn')?.classList.remove('hidden');
                        document.getElementById('cust-passkey-divider')?.classList.remove('hidden');
                    }
                } catch (_) { /* Kein WebAuthn-Support — Buttons bleiben versteckt */ }
            }
        });

        async function loginWithCustPasskey() {
            try {
                // 1. Options holen
                const optRes = await fetch(
                    '{{ route("customer.login.passkey.options") }}',
                    { headers: { 'X-CSRF-TOKEN': csrfToken } }
                );
                if (!optRes.ok) {
                    alert('Fehler beim Abrufen der Passkey-Optionen.');
                    return;
                }
                const options = await optRes.json();

                options.challenge = base64urlToBuffer(options.challenge);

                if (options.allowCredentials && options.allowCredentials.length > 0) {
                    options.allowCredentials = options.allowCredentials.map(c => ({
                        ...c,
                        id: base64urlToBuffer(c.id),
                    }));
                }

                // 2. Assertion durchführen
                const credential = await navigator.credentials.get({ publicKey: options });

                // 3. An Server senden
                const res = await fetch('{{ route("customer.login.passkey") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        id:    credential.id,
                        rawId: bufferToBase64url(credential.rawId),
                        type:  credential.type,
                        response: {
                            clientDataJSON:    bufferToBase64url(credential.response.clientDataJSON),
                            authenticatorData: bufferToBase64url(credential.response.authenticatorData),
                            signature:         bufferToBase64url(credential.response.signature),
                            userHandle: credential.response.userHandle
                                ? bufferToBase64url(credential.response.userHandle)
                                : null,
                        },
                    }),
                });

                const result = await res.json();
                if (result.success) {
                    window.location.href = result.redirect;
                } else {
                    alert('Passkey-Anmeldung fehlgeschlagen: ' + result.message);
                }
            } catch (err) {
                if (err.name === 'NotAllowedError') return;
                alert('Fehler: ' + err.message);
            }
        }

        async function loginWithPasskey() {
            try {
                // 1. Options holen
                const optRes = await fetch(
                    '{{ route("mandant.login.passkey.options") }}',
                    { headers: { 'X-CSRF-TOKEN': csrfToken } }
                );
                if (!optRes.ok) {
                    alert('Fehler beim Abrufen der Passkey-Optionen.');
                    return;
                }
                const options = await optRes.json();

                // Challenge dekodieren
                options.challenge = base64urlToBuffer(options.challenge);

                // allowCredentials ggf. dekodieren (leer beim discoverable flow)
                if (options.allowCredentials && options.allowCredentials.length > 0) {
                    options.allowCredentials = options.allowCredentials.map(c => ({
                        ...c,
                        id: base64urlToBuffer(c.id),
                    }));
                }

                // 2. Assertion durchführen
                const credential = await navigator.credentials.get({ publicKey: options });

                // 3. An Server senden
                const res = await fetch('{{ route("mandant.login.passkey") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        id:    credential.id,
                        rawId: bufferToBase64url(credential.rawId),
                        type:  credential.type,
                        response: {
                            clientDataJSON:    bufferToBase64url(credential.response.clientDataJSON),
                            authenticatorData: bufferToBase64url(credential.response.authenticatorData),
                            signature:         bufferToBase64url(credential.response.signature),
                            userHandle: credential.response.userHandle
                                ? bufferToBase64url(credential.response.userHandle)
                                : null,
                        },
                    }),
                });

                const result = await res.json();
                if (result.success) {
                    window.location.href = result.redirect;
                } else {
                    alert('Passkey-Anmeldung fehlgeschlagen: ' + result.message);
                }
            } catch (err) {
                if (err.name === 'NotAllowedError') {
                    return; // Nutzer hat abgebrochen — kein Fehler anzeigen
                }
                alert('Fehler: ' + err.message);
            }
        }
    </script>
</body>
</html>
