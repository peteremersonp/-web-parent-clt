{{-- Barra de instalación PWA (móviles) --}}
<div id="install-banner" class="hidden fixed bottom-0 inset-x-0 z-50 print:hidden">
    <div class="mx-auto max-w-7xl flex items-center justify-between gap-3 bg-slate-900 border-t border-slate-700 px-4 py-3 shadow-2xl sm:rounded-t-xl">
        <div class="flex items-center gap-3 min-w-0">
            <img src="/icons/icon-192.png" alt="ParentCLT" class="h-9 w-9 rounded-lg shrink-0">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-white">ParentCLT</p>
                <p class="text-xs text-slate-400 truncate">Instala la app en tu teléfono</p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <button id="install-accept" type="button"
                    class="rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                Instalar aplicación
            </button>
            <button id="install-close" type="button" aria-label="Cerrar"
                    class="p-2 text-slate-400 hover:text-white">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    var banner = document.getElementById('install-banner');
    if (!banner) return;

    var standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    var ua = navigator.userAgent || '';
    var isIOS = /iphone|ipad|ipod/i.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    var isAndroid = /android/i.test(ua);

    if (standalone || (!isIOS && !isAndroid)) return;

    // Respetar el cierre manual durante 7 días
    var hiddenUntil = Number(localStorage.installBannerHidden || 0);
    if (hiddenUntil && Date.now() < hiddenUntil) return;

    var accept = document.getElementById('install-accept');
    var close = document.getElementById('install-close');
    var deferred = null;

    function show() { banner.classList.remove('hidden'); }

    if (isIOS) {
        // Safari no dispara beforeinstallprompt: instrucciones manuales.
        accept.addEventListener('click', function () {
            alert('En iPhone/iPad:\n1. Toca el botón Compartir (el cuadrado con la flecha hacia arriba).\n2. Elige "Añadir a pantalla de inicio".\n3. Confirma con "Añadir".');
        });
        show();
    } else {
        window.addEventListener('beforeinstallprompt', function (e) {
            e.preventDefault();
            deferred = e;
            show();
        });

        accept.addEventListener('click', function () {
            if (deferred) {
                deferred.prompt();
                deferred.userChoice.finally(function () { deferred = null; });
                banner.classList.add('hidden');
            } else {
                alert('Para instalar:\n1. Abre el menú del navegador (⋮ arriba a la derecha).\n2. Elige "Instalar aplicación" o "Añadir a pantalla de inicio".');
            }
        });
    }

    window.addEventListener('appinstalled', function () {
        banner.classList.add('hidden');
    });

    close.addEventListener('click', function () {
        banner.classList.add('hidden');
        localStorage.installBannerHidden = Date.now() + 7 * 24 * 3600 * 1000;
    });
})();
</script>
