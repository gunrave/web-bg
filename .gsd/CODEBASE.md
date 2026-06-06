# Codebase Map

Generated: 2026-06-06T03:28:40Z | Files: 194 | Described: 0/194
<!-- gsd:codebase-meta {"generatedAt":"2026-06-06T03:28:40Z","fingerprint":"2aafa3510d5892f1ab668ac2de1934fb839f64a4","fileCount":194,"truncated":false} -->

### (root)/
- `.editorconfig`
- `.env.example`
- `.gitattributes`
- `.gitignore`
- `artisan`
- `composer.json`
- `package-lock.json`
- `package.json`
- `phpunit.xml`
- `README.md`
- `vite.config.js`

### .github/
- `.github/FUNDING.yml`
- `.github/pull_request_template.md`

### .github/agents/
- `.github/agents/gsd-codebase-mapper.agent.md`
- `.github/agents/gsd-debugger.agent.md`
- `.github/agents/gsd-executor.agent.md`
- `.github/agents/gsd-integration-checker.agent.md`
- `.github/agents/gsd-phase-researcher.agent.md`
- `.github/agents/gsd-plan-checker.agent.md`
- `.github/agents/gsd-planner.agent.md`
- `.github/agents/gsd-project-researcher.agent.md`
- `.github/agents/gsd-research-synthesizer.agent.md`
- `.github/agents/gsd-roadmapper.agent.md`
- `.github/agents/gsd-verifier.agent.md`

### .github/instructions/
- `.github/instructions/checkpoints.instructions.md`
- `.github/instructions/continuation-format.instructions.md`
- `.github/instructions/git-integration.instructions.md`
- `.github/instructions/model-profiles.instructions.md`
- `.github/instructions/planning-config.instructions.md`
- `.github/instructions/questioning.instructions.md`
- `.github/instructions/tdd.instructions.md`
- `.github/instructions/ui-brand.instructions.md`
- `.github/instructions/verification-patterns.instructions.md`

### .github/prompts/
- *(27 files: 27 .md)*

### .github/skills/complete-milestone/
- `.github/skills/complete-milestone/SKILL.md`

### .github/skills/diagnose-issues/
- `.github/skills/diagnose-issues/SKILL.md`

### .github/skills/discovery-phase/
- `.github/skills/discovery-phase/SKILL.md`

### .github/skills/discuss-phase/
- `.github/skills/discuss-phase/SKILL.md`

### .github/skills/execute-phase/
- `.github/skills/execute-phase/SKILL.md`

### .github/skills/execute-plan/
- `.github/skills/execute-plan/SKILL.md`

### .github/skills/list-phase-assumptions/
- `.github/skills/list-phase-assumptions/SKILL.md`

### .github/skills/map-codebase/
- `.github/skills/map-codebase/SKILL.md`

### .github/skills/resume-project/
- `.github/skills/resume-project/SKILL.md`

### .github/skills/transition/
- `.github/skills/transition/SKILL.md`

### .github/skills/verify-phase/
- `.github/skills/verify-phase/SKILL.md`

### .github/skills/verify-work/
- `.github/skills/verify-work/SKILL.md`

### app/Filament/Clusters/
- `app/Filament/Clusters/Settings.php`

### app/Filament/Imports/
- `app/Filament/Imports/GajiImporter.php`
- `app/Filament/Imports/PegawaiImporter.php`

### app/Filament/Resources/
- `app/Filament/Resources/GajiResource.php`
- `app/Filament/Resources/PegawaiResource.php`
- `app/Filament/Resources/PeriodeResource.php`
- `app/Filament/Resources/PotongResource.php`
- `app/Filament/Resources/TagihanResource.php`
- `app/Filament/Resources/TunkerResource.php`

### app/Filament/Resources/GajiResource/Pages/
- `app/Filament/Resources/GajiResource/Pages/CreateGaji.php`
- `app/Filament/Resources/GajiResource/Pages/EditGaji.php`
- `app/Filament/Resources/GajiResource/Pages/ListGajis.php`

### app/Filament/Resources/PegawaiResource/Pages/
- `app/Filament/Resources/PegawaiResource/Pages/CreatePegawai.php`
- `app/Filament/Resources/PegawaiResource/Pages/EditPegawai.php`
- `app/Filament/Resources/PegawaiResource/Pages/ListPegawais.php`

### app/Filament/Resources/PeriodeResource/Pages/
- `app/Filament/Resources/PeriodeResource/Pages/CreatePeriode.php`
- `app/Filament/Resources/PeriodeResource/Pages/EditPeriode.php`
- `app/Filament/Resources/PeriodeResource/Pages/ListPeriodes.php`

### app/Filament/Resources/PeriodeResource/RelationManagers/
- `app/Filament/Resources/PeriodeResource/RelationManagers/TagihanRelationManager.php`

### app/Filament/Resources/PeriodeTagihanResource/RelationManagers/
- `app/Filament/Resources/PeriodeTagihanResource/RelationManagers/HasManyThroughRelationManager.php`

### app/Filament/Resources/PotongResource/Pages/
- `app/Filament/Resources/PotongResource/Pages/ManagePotongs.php`

### app/Filament/Resources/TagihanResource/Pages/
- `app/Filament/Resources/TagihanResource/Pages/CreateTagihan.php`
- `app/Filament/Resources/TagihanResource/Pages/EditTagihan.php`
- `app/Filament/Resources/TagihanResource/Pages/ListTagihans.php`

### app/Filament/Resources/TagihanResource/RelationManagers/
- `app/Filament/Resources/TagihanResource/RelationManagers/PotonganRelationManager.php`

### app/Filament/Resources/TunkerResource/Pages/
- `app/Filament/Resources/TunkerResource/Pages/CreateTunker.php`
- `app/Filament/Resources/TunkerResource/Pages/EditTunker.php`
- `app/Filament/Resources/TunkerResource/Pages/ListTunkers.php`

### app/Http/Controllers/
- `app/Http/Controllers/Controller.php`

### app/Imports/
- `app/Imports/ImportGajis.php`
- `app/Imports/ImportTunkers.php`

### app/Models/
- `app/Models/Gaji.php`
- `app/Models/Pegawai.php`
- `app/Models/Penagih.php`
- `app/Models/periode_tagihan.php`
- `app/Models/Potong.php`
- `app/Models/Tagihan.php`
- `app/Models/Tunker.php`
- `app/Models/User.php`

### app/Providers/
- `app/Providers/AppServiceProvider.php`

### app/Providers/Filament/
- `app/Providers/Filament/AdminPanelProvider.php`
- `app/Providers/Filament/AppPanelProvider.php`

### bootstrap/
- `bootstrap/app.php`
- `bootstrap/providers.php`

### bootstrap/cache/
- `bootstrap/cache/.gitignore`

### config/
- `config/app.php`
- `config/auth.php`
- `config/cache.php`
- `config/database.php`
- `config/filesystems.php`
- `config/logging.php`
- `config/mail.php`
- `config/queue.php`
- `config/services.php`
- `config/session.php`

### database/
- `database/.gitignore`

### database/factories/
- `database/factories/UserFactory.php`

### database/migrations/
- *(21 files: 21 .php)*

### database/seeders/
- `database/seeders/DatabaseSeeder.php`

### public/
- `public/.htaccess`
- `public/index.php`
- `public/robots.txt`

### public/css/filament/filament/
- `public/css/filament/filament/app.css`

### public/css/filament/forms/
- `public/css/filament/forms/forms.css`

### public/css/filament/support/
- `public/css/filament/support/support.css`

### public/js/filament/filament/
- `public/js/filament/filament/app.js`
- `public/js/filament/filament/echo.js`

### public/js/filament/forms/components/
- `public/js/filament/forms/components/color-picker.js`
- `public/js/filament/forms/components/date-time-picker.js`
- `public/js/filament/forms/components/file-upload.js`
- `public/js/filament/forms/components/key-value.js`
- `public/js/filament/forms/components/markdown-editor.js`
- `public/js/filament/forms/components/rich-editor.js`
- `public/js/filament/forms/components/select.js`
- `public/js/filament/forms/components/tags-input.js`
- `public/js/filament/forms/components/textarea.js`

### public/js/filament/notifications/
- `public/js/filament/notifications/notifications.js`

### public/js/filament/support/
- `public/js/filament/support/async-alpine.js`
- `public/js/filament/support/support.js`

### public/js/filament/tables/components/
- `public/js/filament/tables/components/table.js`

### public/js/filament/widgets/components/
- `public/js/filament/widgets/components/chart.js`

### public/js/filament/widgets/components/stats-overview/stat/
- `public/js/filament/widgets/components/stats-overview/stat/chart.js`

### resources/css/
- `resources/css/app.css`

### resources/js/
- `resources/js/app.js`
- `resources/js/bootstrap.js`

### resources/views/
- `resources/views/welcome.blade.php`

### resources/views/filament/custom/
- `resources/views/filament/custom/upload-file.blade.php`

### routes/
- `routes/console.php`
- `routes/web.php`

### storage/app/
- `storage/app/.gitignore`

### storage/app/public/
- `storage/app/public/.gitignore`

### storage/framework/
- `storage/framework/.gitignore`

### storage/framework/cache/
- `storage/framework/cache/.gitignore`

### storage/framework/cache/data/
- `storage/framework/cache/data/.gitignore`

### storage/framework/sessions/
- `storage/framework/sessions/.gitignore`

### storage/framework/testing/
- `storage/framework/testing/.gitignore`

### storage/framework/views/
- `storage/framework/views/.gitignore`

### storage/logs/
- `storage/logs/.gitignore`

### tests/
- `tests/Pest.php`
- `tests/TestCase.php`

### tests/Feature/
- `tests/Feature/ExampleTest.php`

### tests/Unit/
- `tests/Unit/ExampleTest.php`
