{{--
    FILE:    resources/views/mandant/passkey/index.blade.php
    VERSION: 1.1.0

    DESCRIPTION:
      Passkey-Verwaltung für Mandanten — Liste aller registrierten Passkeys,
      Umbenennen, Löschen, Neuen Passkey registrieren.
      Standalone (kein Layout-Erbe), gleiches Strukturmuster wie mandant/dashboard.

    DATA FROM CONTROLLER:
      $passkeys — Collection<App\Models\UserDb\Passkey>

    ROUTES USED:
      GET  /mandant/passkeys                  — diese Seite (route('mandant.passkeys'))
      GET  /mandant/passkeys/register/options — Challenge holen (route('mandant.passkeys.options'))
      POST /mandant/passkeys/register         — Passkey speichern (route('mandant.passkeys.register'))
      PATCH /mandant/passkeys/{id}/rename     — Umbenennen (route('mandant.passkeys.rename'))
      DELETE /mandant/passkeys/{id}           — Löschen (route('mandant.passkeys.destroy'))
      GET  /mandant/dashboard                 — Zurück (route('mandant.dashboard'))
--}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Passkeys · Fotosite V8</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 antialiased"
      x-data="{ showNameModal: false, deviceName: '' }">

    @php $mandUname = \App\Models\UserDb\MandUser::find(session('_mand_id'))?->mand_uname ?? ''; @endphp

    {{-- ══════════════════════════════════════════════════════
         TOP BAR
    ══════════════════════════════════════════════════════ --}}
    <header class="sticky top-0 z-20 border-b border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-4xl px-6 h-14
                    flex items-center justify-between">

            {{-- Brand --}}
            <div class="flex items-center gap-3">
                <span class="text-[11px] font-mono tracking-widest
                             uppercase text-gray-400">
                    Fotosite&thinsp;V8
                </span>
                <span class="text-zinc-800 select-none">|</span>
                <span class="text-sm font-semibold tracking-widest
                             uppercase text-indigo-600">
                    Mandant
                </span>
                <span class="text-sm text-indigo-200">{{ $mandUname }}</span>
            </div>

            {{-- Logout --}}
            <div class="flex items-center">
                <form method="POST" action="{{ route('mandant.logout') }}">
                    @csrf
                    <button type="submit"
                            class="text-xs text-gray-400 hover:text-red-500
                                   transition-colors duration-150 tracking-wide">
                        Abmelden
                    </button>
                </form>
            </div>

        </div>
    </header>

    {{-- ══════════════════════════════════════════════════════
         MAIN
    ══════════════════════════════════════════════════════ --}}
    <main class="mx-auto max-w-4xl px-6 pt-14 pb-24">

        {{-- Zurück-Link --}}
        <div class="mt-6 mb-8">
            <a href="{{ route('mandant.dashboard') }}"
               class="inline-flex items-center gap-1.5 text-xs text-indigo-500
                      hover:text-indigo-700 transition-colors">
                <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg"
                     fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15.75 19.5 8.25 12l7.5-7.5"/>
                </svg>
                Dashboard
            </a>
        </div>

        {{-- Seitenüberschrift + Registrieren-Button --}}
        <div class="mb-8 flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold tracking-tight text-gray-800">
                    Meine Passkeys
                </h1>
                <p class="mt-1.5 text-sm text-zinc-600">
                    Registrierte Passkeys für passwortlosen Login.
                </p>
            </div>
            <button @click="showNameModal = true"
                    class="shrink-0 inline-flex items-center gap-2 rounded-lg
                           bg-indigo-600 px-4 py-2 text-sm font-medium text-white
                           hover:bg-indigo-700 active:bg-indigo-800
                           transition-colors duration-150 shadow-sm">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg"
                     fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Neuen Passkey registrieren
            </button>
        </div>

        {{-- Hinweis --}}
        <div class="text-sm text-gray-500 mb-4 bg-gray-50 rounded-lg p-3 space-y-2">
            <p><strong>Windows Hello (Edge / systemeigen):</strong>
            Der Passkey wird lokal auf diesem Gerät gespeichert.
            Pro Windows-Konto kann ein Passkey für ein Mitglied und zusätzlich ein Passkey
            für einen Galerist:in eingerichtet werden. Beim Login fragt Windows welcher
            Account verwendet werden soll. Verwenden Sie für die Windows-Anmeldung und
            für Fotosite dasselbe Authentifizierungsmerkmal (z.B. denselben
            Fingerabdruck).</p>
            <p><strong>Firefox:</strong>
            Der Passkey wird lokal in Firefox gespeichert und ist nur in diesem Browser
            verfügbar.</p>
            <p><strong>Chrome (Windows + Android):</strong>
            Der Passkey wird im Google Passwort-Manager gespeichert. Wenn Sie in Chrome
            mit Ihrem Google-Konto angemeldet sind, steht der Passkey auf allen Geräten
            mit demselben Google-Konto zur Verfügung (Windows + Android).</p>
            <p><strong>iPhone / iPad (Safari):</strong>
            Der Passkey wird im iCloud-Schlüsselbund gespeichert und steht auf allen
            Apple-Geräten mit derselben Apple-ID zur Verfügung.</p>
        </div>

        {{-- Flash: Status-Meldung --}}
        @if(session('status'))
            <div class="mb-6 rounded-lg border border-indigo-200
                        bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- Gerätename-Modal --}}
        <div x-show="showNameModal" x-cloak
             class="fixed inset-0 bg-black bg-opacity-50
                    flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-6 max-w-sm shadow-xl">
                <h3 class="font-semibold mb-3">Gerätename vergeben</h3>
                <p class="text-sm text-gray-500 mb-3">
                    Geben Sie diesem Gerät einen Namen
                    (z.B. "iPhone 15", "Windows Büro").
                </p>
                <input type="text" x-model="deviceName"
                       placeholder="Mein Gerät"
                       class="w-full border rounded-lg px-3 py-2 text-sm mb-4">
                <div class="flex gap-3 justify-end">
                    <button @click="showNameModal = false"
                            class="text-sm text-gray-500">
                        Abbrechen
                    </button>
                    <button @click="showNameModal = false; registerPasskey(deviceName)"
                            class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg">
                        Weiter
                    </button>
                </div>
            </div>
        </div>

        {{-- Passkey-Tabelle --}}
        @if($passkeys->isEmpty())
            <div class="rounded-xl border border-gray-100 bg-white px-6 py-12 text-center">
                <p class="text-sm text-gray-400">
                    Noch keine Passkeys registriert.
                </p>
            </div>
        @else
            <div class="rounded-xl border border-gray-100 bg-white overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="px-5 py-3 text-left text-xs font-semibold
                                       uppercase tracking-wide text-gray-400">
                                Gerätename
                            </th>
                            <th class="px-5 py-3 text-left text-xs font-semibold
                                       uppercase tracking-wide text-gray-400">
                                Registriert am
                            </th>
                            <th class="px-5 py-3 text-left text-xs font-semibold
                                       uppercase tracking-wide text-gray-400">
                                Zuletzt verwendet
                            </th>
                            <th class="px-5 py-3 text-right text-xs font-semibold
                                       uppercase tracking-wide text-gray-400">
                                Aktionen
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($passkeys as $passkey)
                        <tr x-data="{
                                editing: false,
                                name: @js($passkey->device_name ?? '')
                            }"
                            class="hover:bg-gray-50 transition-colors">

                            {{-- Gerätename --}}
                            <td class="px-5 py-3.5 font-medium text-gray-700">
                                <span x-show="!editing" x-text="name"
                                      class="block truncate max-w-[180px]"></span>
                                <input x-show="editing"
                                       x-model="name"
                                       x-ref="nameInput"
                                       @keydown.enter="saveRename({{ $passkey->pk_id }}, name); editing = false"
                                       @keydown.escape="editing = false"
                                       type="text"
                                       maxlength="100"
                                       class="w-full max-w-[180px] rounded border border-indigo-300
                                              px-2 py-1 text-sm focus:outline-none
                                              focus:ring-2 focus:ring-indigo-400" />
                            </td>

                            {{-- Registriert am --}}
                            <td class="px-5 py-3.5 text-gray-500 whitespace-nowrap">
                                {{ $passkey->created_at?->format('d.m.Y H:i') ?? '–' }}
                            </td>

                            {{-- Zuletzt verwendet --}}
                            <td class="px-5 py-3.5 text-gray-500 whitespace-nowrap">
                                {{ $passkey->last_used_at?->format('d.m.Y H:i') ?? 'Nie' }}
                            </td>

                            {{-- Aktionen --}}
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">

                                    {{-- Umbenennen / Speichern --}}
                                    <button x-show="!editing"
                                            @click="editing = true; $nextTick(() => $refs.nameInput.focus())"
                                            class="text-xs text-indigo-500 hover:text-indigo-700
                                                   font-medium transition-colors">
                                        Umbenennen
                                    </button>
                                    <button x-show="editing"
                                            @click="saveRename({{ $passkey->pk_id }}, name); editing = false"
                                            class="text-xs text-green-600 hover:text-green-800
                                                   font-medium transition-colors">
                                        Speichern
                                    </button>

                                    <span class="text-gray-200">|</span>

                                    {{-- Löschen --}}
                                    <form method="POST"
                                          action="{{ route('mandant.passkeys.destroy', $passkey->pk_id) }}"
                                          class="inline"
                                          onsubmit="return confirm('Passkey «{{ addslashes($passkey->device_name ?? 'Dieser Passkey') }}» wirklich löschen?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-xs text-red-400 hover:text-red-600
                                                       font-medium transition-colors">
                                            Löschen
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </main>

    {{-- ══════════════════════════════════════════════════════
         FOOTER
    ══════════════════════════════════════════════════════ --}}
    <footer class="fixed bottom-0 inset-x-0 border-t border-gray-200 bg-white shadow-sm">
        <div class="mx-auto max-w-4xl px-6 h-9
                    flex items-center justify-between">
            <span class="text-[10px] font-mono tracking-widest
                         uppercase text-gray-400">
                Fotosite V8 · Mandanten-Bereich
            </span>
            <span class="text-[10px] text-gray-400">
                Session aktiv
            </span>
        </div>
    </footer>

    {{-- ══════════════════════════════════════════════════════
         JAVASCRIPT — WebAuthn Registration
    ══════════════════════════════════════════════════════ --}}
    <script>
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

        async function registerPasskey(deviceName) {
            const name = (deviceName || '').trim() || 'Mein Gerät';
            try {
                const optRes = await fetch('{{ route("mandant.passkeys.options") }}');
                if (!optRes.ok) {
                    alert('Fehler beim Abrufen der Optionen.');
                    return;
                }
                const options = await optRes.json();

                // Challenge + user.id dekodieren
                options.challenge = base64urlToBuffer(options.challenge);
                options.user.id   = base64urlToBuffer(options.user.id);

                // pubKeyCredParams.alg ist bereits numerisch — kein Umbau nötig
                // excludeCredentials ggf. dekodieren
                if (options.excludeCredentials) {
                    options.excludeCredentials = options.excludeCredentials.map(c => ({
                        ...c,
                        id: base64urlToBuffer(c.id),
                    }));
                }

                // 2. Credential erstellen
                const credential = await navigator.credentials.create({ publicKey: options });

                // 3. An Server senden
                const regRes = await fetch('{{ route("mandant.passkeys.register") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        credential: {
                            id:    credential.id,
                            rawId: bufferToBase64url(credential.rawId),
                            type:  credential.type,
                            response: {
                                clientDataJSON:    bufferToBase64url(credential.response.clientDataJSON),
                                attestationObject: bufferToBase64url(credential.response.attestationObject),
                            },
                        },
                        device_name: name,
                    }),
                });

                const result = await regRes.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert('Fehler: ' + result.message);
                }
            } catch (e) {
                console.error('WebAuthn Fehler:', e.name, e.message, e);
                if (e.name === 'NotAllowedError') return;
                alert('Fehler: ' + e.name + ': ' + e.message);
            }
        }

        async function saveRename(id, name) {
            const res = await fetch(`/mandant/passkeys/${id}/rename`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ device_name: name }),
            });
            const result = await res.json();
            if (!result.success) {
                alert('Fehler beim Umbenennen.');
            }
        }
    </script>

</body>
</html>
