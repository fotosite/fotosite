{{--
    FILE:    resources/views/customer/auth/login.blade.php
    VERSION: 1.0.0
    AUTHOR:  Martin Wagner
    DATE:    2026-06-08

    DESCRIPTION:
      Mitglieder-Login — zwei Tabs: "Mitglied" (registrierter Login per
      Passkey oder E-Mail + Passwort) und "Gast" (anonymer Login per
      Kurzzeit-Kennwort). Standalone, kein Layout-Erbe. Accent-Farbe: indigo.

    DATA FROM CONTROLLER:
      $custTab — string, aktiver Tab beim Seitenaufruf ('reg' | 'anon');
                 Default 'reg'

    ROUTES USED:
      POST customer.login.handle          — Registrierter Login (E-Mail + PW)
      POST customer.login.anon            — Gast-Login (Kurzzeit-Kennwort)
      GET  customer.login.passkey.options — Passkey-Challenge abholen (JSON)
      POST customer.login.passkey         — Passkey-Assertion absenden (JSON)
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Anmelden · Fotogalerie</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        function b64uDecode(s) {
            s = s.replace(/-/g, '+').replace(/_/g, '/');
            while (s.length % 4) s += '=';
            return Uint8Array.from(atob(s), c => c.charCodeAt(0));
        }
        function b64uEncode(buf) {
            let s = '';
            for (const b of new Uint8Array(buf)) s += String.fromCharCode(b);
            return btoa(s).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('custLogin', () => ({
                tab:            @json($custTab),
                passkeyError:   '',
                passkeyLoading: false,

                async doPasskeyLogin() {
                    this.passkeyLoading = true;
                    this.passkeyError   = '';
                    try {
                        const opts = await fetch(@json(route('customer.login.passkey.options')))
                            .then(r => r.json());

                        const cred = await navigator.credentials.get({
                            publicKey: {
                                challenge:        b64uDecode(opts.challenge),
                                rpId:             opts.rpId,
                                allowCredentials: (opts.allowCredentials ?? []).map(c => ({
                                    type: c.type,
                                    id:   b64uDecode(c.id),
                                })),
                                userVerification: opts.userVerification ?? 'required',
                                timeout:          opts.timeout ?? 60000,
                            },
                        });

                        const res = await fetch(@json(route('customer.login.passkey')), {
                            method:  'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            },
                            body: JSON.stringify({
                                id:     cred.id,
                                rawId:  b64uEncode(cred.rawId),
                                type:   cred.type,
                                response: {
                                    authenticatorData: b64uEncode(cred.response.authenticatorData),
                                    clientDataJSON:    b64uEncode(cred.response.clientDataJSON),
                                    signature:         b64uEncode(cred.response.signature),
                                    userHandle: cred.response.userHandle
                                        ? b64uEncode(cred.response.userHandle)
                                        : null,
                                },
                            }),
                        }).then(r => r.json());

                        if (res.success) {
                            window.location.href = res.redirect;
                        } else {
                            this.passkeyError = res.message || 'Passkey-Anmeldung fehlgeschlagen.';
                        }
                    } catch (e) {
                        this.passkeyError = (e.name === 'NotAllowedError')
                            ? 'Passkey-Abfrage abgebrochen.'
                            : (e.message || 'Unbekannter Fehler.');
                    } finally {
                        this.passkeyLoading = false;
                    }
                },
            }));
        });
    </script>
</head>

<body class="min-h-screen bg-gray-100 font-sans antialiased">

<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-sm bg-white rounded-xl shadow-md px-8 py-8"
         x-data="custLogin">

        {{-- ── Kopfzeile ──────────────────────────────────── --}}
        <div class="mb-6">
            <p class="text-[11px] font-mono tracking-widest uppercase text-gray-400 mb-1">
                Fotogalerie
            </p>
            <h1 class="text-xl font-semibold text-gray-800">Anmelden</h1>
        </div>

        {{-- ── Tab-Leiste ──────────────────────────────────── --}}
        <div class="flex border-b border-gray-200 mb-6">
            <button type="button"
                    @click="tab = 'reg'"
                    :class="tab === 'reg'
                        ? 'border-b-2 border-indigo-600 text-indigo-600'
                        : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700'"
                    class="flex-1 pb-3 text-sm font-medium text-center
                           focus:outline-none transition-colors">
                Mitglied
            </button>
            <button type="button"
                    @click="tab = 'anon'"
                    :class="tab === 'anon'
                        ? 'border-b-2 border-indigo-600 text-indigo-600'
                        : 'border-b-2 border-transparent text-gray-500 hover:text-gray-700'"
                    class="flex-1 pb-3 text-sm font-medium text-center
                           focus:outline-none transition-colors">
                Gast
            </button>
        </div>

        {{-- ══════════════════════════════════════════════════
             TAB: MITGLIED
        ══════════════════════════════════════════════════ --}}
        <div x-show="tab === 'reg'">

            {{-- Fehler: Zugangsdaten --}}
            @error('credentials')
                <div class="mb-5 rounded-lg border border-red-200
                            bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $message }}
                </div>
            @enderror

            {{-- Passkey-Fehler --}}
            <p x-show="passkeyError" x-text="passkeyError" x-cloak
               class="mb-3 text-sm text-red-600 text-center"></p>

            {{-- Passkey-Button --}}
            <button type="button"
                    @click="doPasskeyLogin()"
                    :disabled="passkeyLoading"
                    class="w-full flex items-center justify-center gap-2
                           rounded-lg border-2 border-indigo-600
                           px-4 py-3 md:py-2
                           text-sm font-semibold text-indigo-600
                           hover:bg-indigo-50 transition-colors
                           disabled:opacity-50 disabled:cursor-not-allowed
                           focus:outline-none focus:ring-2
                           focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg"
                     fill="none" viewBox="0 0 24 24"
                     stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029
                             5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25
                             v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1
                             .43-1.563A6 6 0 0 1 21.75 8.25Z"/>
                </svg>
                <span x-text="passkeyLoading ? 'Bitte warten …' : 'Mit Passkey anmelden'">
                    Mit Passkey anmelden
                </span>
            </button>

            {{-- Trenner --}}
            <div class="relative my-5">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="bg-white px-3 text-xs text-gray-400">oder</span>
                </div>
            </div>

            {{-- Formular: E-Mail + Passwort --}}
            <form method="POST"
                  action="{{ route('customer.login.handle') }}"
                  autocomplete="off">
                @csrf

                <div class="space-y-4">

                    <div>
                        <label for="cust_email"
                               class="block text-sm font-medium text-gray-700 mb-1">
                            E-Mail-Adresse
                        </label>
                        <input id="cust_email" name="cust_email"
                               type="email" required
                               value="{{ old('cust_email') }}"
                               autocomplete="email"
                               class="w-full rounded-lg border px-3 py-2.5 md:py-2
                                      text-sm text-gray-800 shadow-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400
                                      @error('cust_email') border-red-400 bg-red-50
                                      @else border-gray-300 @enderror">
                        @error('cust_email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password"
                               class="block text-sm font-medium text-gray-700 mb-1">
                            Passwort
                        </label>
                        <input id="password" name="password"
                               type="password" required
                               autocomplete="current-password"
                               class="w-full rounded-lg border px-3 py-2.5 md:py-2
                                      text-sm text-gray-800 shadow-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-400
                                      @error('password') border-red-400 bg-red-50
                                      @else border-gray-300 @enderror">
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <div class="mt-5">
                    <button type="submit"
                            class="w-full flex justify-center rounded-lg
                                   bg-indigo-600 px-4 py-3 md:py-2
                                   text-sm font-semibold text-white
                                   hover:bg-indigo-700 transition-colors
                                   focus:outline-none focus:ring-2
                                   focus:ring-indigo-500 focus:ring-offset-2">
                        Anmelden
                    </button>
                </div>

            </form>

        </div>{{-- /tab reg --}}

        {{-- ══════════════════════════════════════════════════
             TAB: GAST
        ══════════════════════════════════════════════════ --}}
        <div x-show="tab === 'anon'">

            <p class="mb-5 text-sm text-gray-500 leading-relaxed">
                Du hast von einem Galeristen ein Kurzzeit-Kennwort erhalten?
                Gib es hier ein.
            </p>

            <form method="POST"
                  action="{{ route('customer.login.anon') }}"
                  autocomplete="off">
                @csrf

                <div>
                    <label for="anon_password"
                           class="block text-sm font-medium text-gray-700 mb-1">
                        Kurzzeit-Kennwort
                    </label>
                    <input id="anon_password" name="password"
                           type="password" required minlength="8"
                           autocomplete="off"
                           class="w-full rounded-lg border px-3 py-2.5 md:py-2
                                  text-sm text-gray-800 shadow-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-400
                                  @error('password') border-red-400 bg-red-50
                                  @else border-gray-300 @enderror">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-5">
                    <button type="submit"
                            class="w-full flex justify-center rounded-lg
                                   bg-indigo-600 px-4 py-3 md:py-2
                                   text-sm font-semibold text-white
                                   hover:bg-indigo-700 transition-colors
                                   focus:outline-none focus:ring-2
                                   focus:ring-indigo-500 focus:ring-offset-2">
                        Anmelden
                    </button>
                </div>

            </form>

        </div>{{-- /tab anon --}}

    </div>
</div>

</body>
</html>
