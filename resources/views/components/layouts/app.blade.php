<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <title>{{ $title ?? 'Cats' }}</title>
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="p-4 bg-transparent">

        {{ $slot }}

        {{-- Custom hover tooltips: any element with a data-tooltip attribute
             shows it after 500ms. Faster than the native title tooltip, and
             fixed-positioned so it is not clipped by scroll containers. --}}
        <div id="cats-tooltip" role="tooltip"></div>
        <style>
            #cats-tooltip {
                position: fixed;
                z-index: 9999;
                left: 0;
                top: 0;
                pointer-events: none;
                opacity: 0;
                transition: opacity 0.12s ease;
                background: rgba(28, 28, 30, 0.96);
                color: #fff;
                font-size: 11px;
                line-height: 1.2;
                padding: 4px 7px;
                border-radius: 6px;
                white-space: nowrap;
                max-width: 260px;
                overflow: hidden;
                text-overflow: ellipsis;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            }
            #cats-tooltip.is-visible { opacity: 1; }
        </style>
        <script>
            (function () {
                var tip = document.getElementById('cats-tooltip');
                if (!tip) return;
                var timer = null;
                var current = null;

                function place(el) {
                    var text = el.getAttribute('data-tooltip') || '';
                    if (!text) return;
                    tip.textContent = text;
                    var r = el.getBoundingClientRect();
                    var t = tip.getBoundingClientRect();
                    var left = r.left + r.width / 2 - t.width / 2;
                    var top = r.bottom + 6;
                    if (top + t.height > window.innerHeight - 4) {
                        top = r.top - t.height - 6;
                    }
                    left = Math.max(4, Math.min(left, window.innerWidth - t.width - 4));
                    tip.style.left = Math.round(left) + 'px';
                    tip.style.top = Math.round(Math.max(4, top)) + 'px';
                    tip.classList.add('is-visible');
                }
                function hide() {
                    if (timer) { clearTimeout(timer); timer = null; }
                    current = null;
                    tip.classList.remove('is-visible');
                }
                document.addEventListener('mouseover', function (e) {
                    var el = e.target.closest('[data-tooltip]');
                    if (!el || el === current) return;
                    hide();
                    current = el;
                    timer = setTimeout(function () {
                        if (current === el && el.isConnected) place(el);
                    }, 500);
                });
                document.addEventListener('mouseout', function (e) {
                    var el = e.target.closest('[data-tooltip]');
                    if (!el) return;
                    if (e.relatedTarget && el.contains(e.relatedTarget)) return;
                    hide();
                });
                window.addEventListener('scroll', hide, true);
                document.addEventListener('mousedown', hide, true);
            })();
        </script>

        @livewireScripts
    </body>
</html>