---
estimated_steps: 12
estimated_files: 1
skills_used: []
---

# T04: Configure Tailwind CSS v4

Why: This is the highest-risk piece of the upgrade. Tailwind v4 uses CSS-first configuration (no tailwind.config.js). The empty resources/css/app.css must be set up with proper v4 theme directives. Filament v4 relies on Tailwind v4.1+ for its CSS. The custom Blade view at resources/views/filament/custom/upload-file.blade.php uses Tailwind utility classes (flex, justify-between, font-bold, text-3xl, shadow, border, rounded, bg-blue-500, etc.) that must be covered by @source so they don't get purged.

Do:
1. Rewrite resources/css/app.css with Tailwind v4 setup:
   - @import "tailwindcss" — imports Tailwind v4 base, components, utilities
   - @source directive covering resources/views/filament/custom/upload-file.blade.php and any other custom views that use Tailwind utility classes outside Filament's compiled CSS
   - @theme block if any custom colors, fonts, or breakpoints are needed (likely minimal — Filament handles its own theme)
2. Run vite build
3. If vite build fails with CSS errors, fix them:
   - Common issues: unknown @apply directives (v4 discourages @apply), invalid @theme syntax, missing @source paths for custom views
   - Tailwind v4 no longer supports @apply for arbitrary classes by default; if Filament or the Blade view uses @apply, check compatibility
4. If vite build succeeds but the admin panel renders without styles in the browser, check: @source paths are correct and cover all custom Blade views, the compiled CSS includes Filament's expected classes

Done when: vite build exits with code 0, no CSS compilation errors or warnings.

## Inputs

- `vite.config.js`
- `resources/css/app.css`

## Expected Output

- `resources/css/app.css`

## Verification

vite build
