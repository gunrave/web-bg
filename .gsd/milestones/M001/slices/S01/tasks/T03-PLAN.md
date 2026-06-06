---
estimated_steps: 8
estimated_files: 3
skills_used: []
---

# T03: Upgrade JS dependencies and configure Vite 6

Why: Vite 5→6, laravel-vite-plugin, and axios need bumping. Tailwind CSS v4 requires the @tailwindcss/vite plugin which must be added to the Vite plugin chain before the laravel plugin.

Do:
1. Edit package.json devDependencies: set vite to "^6.0", axios to latest stable, laravel-vite-plugin to latest stable
2. Add tailwindcss "^4.0" and @tailwindcss/vite "^4.0" to devDependencies
3. Edit vite.config.js: add import tailwindcss from '@tailwindcss/vite' at top; add tailwindcss() to the plugins array BEFORE laravel() (Tailwind v4 must process CSS before Laravel's plugin handles it)
4. Run npm install
5. Verify installation by checking node_modules/tailwindcss exists

Done when: package.json has updated versions (Vite ^6.0, axios latest, laravel-vite-plugin latest, tailwindcss ^4.0, @tailwindcss/vite ^4.0), npm install succeeds, vite.config.js has @tailwindcss/vite plugin configured before laravel-vite-plugin.

## Inputs

- `package.json`
- `vite.config.js`

## Expected Output

- `package.json`
- `package-lock.json`
- `vite.config.js`

## Verification

test -f node_modules/tailwindcss/package.json
