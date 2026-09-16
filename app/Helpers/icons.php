<?php

function icon(string $name): string
{
    $paths = [
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'box' => '<path d="m12 3 9 5v8l-9 5-9-5V8l9-5Z M3 8l9 5 9-5 M12 13v8 M7.5 5.5l9 5v5"/>',
        'warehouse' => '<path d="M3 21V9l9-6 9 6v12 M7 21V11h10v10 M7 15h10 M7 18h10 M2 21h20"/>',
        'supplier' => '<path d="M3 6h11v11H3z M14 10h4l3 4v3h-7"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>',
        'in' => '<path d="M12 3v12 M7 10l5 5 5-5 M4 15v5h16v-5"/>',
        'out' => '<path d="M12 15V3 M7 8l5-5 5 5 M4 15v5h16v-5"/>',
        'report' => '<path d="M5 3h10l4 4v14H5z M14 3v5h5 M9 17v-4 M12 17v-7 M15 17v-2"/>',
        'users' => '<circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3 M16 5a3 3 0 0 1 0 6 M18 15a5 5 0 0 1 3 5"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
        'lock' => '<rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3 M12 14v3"/>',
        'logout' => '<path d="M9 4H4v16h5 M10 12h11 M17 8l4 4-4 4"/>',
        'menu' => '<path d="M4 6h16 M4 12h16 M4 18h16"/>',
        'close' => '<path d="m6 6 12 12 M6 18 18 6"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
        'plus' => '<path d="M12 5v14 M5 12h14"/>',
        'arrow' => '<path d="M5 12h14 M13 6l6 6-6 6"/>',
        'table' => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18 M3 14h18 M9 4v16"/>',
        'filter' => '<path d="M3 5h18l-7 8v6l-4 2v-8Z"/>',
        'category' => '<path d="m12 3 9 9-9 9-9-9Z"/><circle cx="12" cy="12" r="2"/>',
        'alert' => '<path d="m12 3 10 18H2L12 3Z M12 9v5 M12 17h.01"/>',
    ];
    return '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['box']) . '</svg>';
}
