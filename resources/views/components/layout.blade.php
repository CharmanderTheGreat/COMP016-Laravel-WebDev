@props(['title' => 'Attendance System'])
<!doctype html>
<html lang="en" data-theme="pup">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#800000">
    <title>{{ $title }} · COMP 016 Attendance</title>

    {{-- Favicon: the PUP logo if public/images/pup-logo.png exists, otherwise a small inline check-mark icon --}}
    @if (file_exists(public_path('images/pup-logo.png')))
        <link rel="icon" type="image/png" href="/images/pup-logo.png">
    @else
        <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23800000'/%3E%3Cpath d='M9 17l5 5 9-11' fill='none' stroke='%23F2B705' stroke-width='3.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E">
    @endif

    {{-- Fonts (falls back to system fonts if blocked) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    {{-- Tailwind (browser build) + daisyUI via CDN.
         Versions are pinned on purpose so a CDN update can never change the look of the live demo.
         Kept on CDN (no Vite build step) so the Railway deploy does not depend on npm. --}}
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5.7.50/daisyui.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4.3.5/dist/index.global.js"></script>

    {{-- ============================ BRAND COLOURS ============================
         Only TWO colours to edit: maroon and gold. Every other shade (dark/light
         variants used by the buttons, header, cards, avatars...) is derived from
         these two, so the whole system always stays in the logo's colours. --}}
    <style>
        :root {
            --brand-maroon: #800000;
            --brand-gold: #f2b705;
        }
    </style>

    {{-- Brand colours as Tailwind utilities: bg-pup-700, text-gold-500, ... --}}
    <style type="text/tailwindcss">
        @theme {
            --font-sans: 'Inter', ui-sans-serif, system-ui, sans-serif;
            --font-display: 'Playfair Display', Georgia, serif;

            --color-pup-700: var(--brand-maroon);
            --color-pup-900: color-mix(in oklab, var(--brand-maroon) 45%, black);
            --color-pup-800: color-mix(in oklab, var(--brand-maroon) 75%, black);
            --color-pup-600: color-mix(in oklab, var(--brand-maroon) 85%, white);
            --color-pup-200: color-mix(in oklab, var(--brand-maroon) 25%, white);
            --color-pup-100: color-mix(in oklab, var(--brand-maroon) 12%, white);
            --color-pup-50: color-mix(in oklab, var(--brand-maroon) 6%, white);

            --color-gold-400: var(--brand-gold);
            --color-gold-500: color-mix(in oklab, var(--brand-gold) 85%, black);
            --color-gold-300: color-mix(in oklab, var(--brand-gold) 65%, white);
            --color-gold-100: color-mix(in oklab, var(--brand-gold) 20%, white);

            --color-paper: #faf6f1;
            --color-ink: #2b1d1d;

            /* same values as the daisyUI theme below, so utilities like
               border-base-300 / bg-base-200 / text-error work with the CDN build */
            --color-base-200: #faf6f1;
            --color-base-300: #ece3d8;
            --color-error: #d92d20;
        }
    </style>

    {{-- daisyUI theme "pup" (maroon + gold). A custom data-theme also stops the
         browser's dark mode from switching the palette. --}}
    <style>
        [data-theme="pup"] {
            color-scheme: light;
            --color-base-100: #ffffff;
            --color-base-200: #faf6f1;
            --color-base-300: #ece3d8;
            --color-base-content: #2b1d1d;
            --color-primary: var(--brand-maroon);
            --color-primary-content: #ffffff;
            --color-secondary: var(--brand-gold);
            --color-secondary-content: color-mix(in oklab, var(--brand-maroon) 45%, black);
            --color-accent: color-mix(in oklab, var(--brand-gold) 85%, black);
            --color-accent-content: color-mix(in oklab, var(--brand-maroon) 45%, black);
            --color-neutral: color-mix(in oklab, var(--brand-maroon) 45%, black);
            --color-neutral-content: #faf6f1;
            --color-info: #2563a8;
            --color-info-content: #ffffff;
            --color-success: #2f7d4f;
            --color-success-content: #ffffff;
            --color-warning: #b27b00;
            --color-warning-content: #ffffff;
            --color-error: #d92d20;
            --color-error-content: #ffffff;
            --radius-selector: 1rem;
            --radius-field: 0.5rem;
            --radius-box: 0.9rem;
            --size-selector: 0.25rem;
            --size-field: 0.25rem;
            --border: 1px;
            --depth: 0;
            --noise: 0;
        }

        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; background: #faf6f1; color: #2b1d1d; }
        h1, h2, .font-display { font-family: 'Playfair Display', Georgia, serif; }

        /* status radio buttons on the "record attendance" page */
        .status-pick input.btn[value="present"]:checked { background: #2f7d4f; border-color: #2f7d4f; color: #fff; }
        .status-pick input.btn[value="late"]:checked    { background: #d99f00; border-color: #d99f00; color: #3f0000; }
        .status-pick input.btn[value="absent"]:checked  { background: #d92d20; border-color: #d92d20; color: #fff; }
        .status-pick input.btn[value="excused"]:checked { background: #2563a8; border-color: #2563a8; color: #fff; }
    </style>
</head>
<body class="min-h-screen antialiased">
    {{ $slot }}
</body>
</html>
