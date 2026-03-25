@php
    $secondary = ltrim((string) config('theme.colors.secondary'), '#');
    $secondaryRgb = strlen($secondary) === 6
        ? implode(', ', [
            hexdec(substr($secondary, 0, 2)),
            hexdec(substr($secondary, 2, 2)),
            hexdec(substr($secondary, 4, 2)),
        ])
        : '20, 33, 61';
@endphp
<style>
    :root {
        --color-primary: {{ config('theme.colors.primary') }};
        --color-secondary: {{ config('theme.colors.secondary') }};
        --color-secondary-rgb: {{ $secondaryRgb }};
        --color-tertiary: {{ config('theme.colors.tertiary') }};
        --color-background: {{ config('theme.colors.background') }};
        --color-surface: {{ config('theme.colors.surface') }};
        --color-text: {{ config('theme.colors.text') }};
        --color-muted-text: {{ config('theme.colors.muted_text') }};

        --font-family-base: {{ config('theme.typography.font_family') }};
        --font-size-base: {{ config('theme.typography.base_size') }};
        --font-size-title: {{ config('theme.typography.title_size') }};
    }
</style>
