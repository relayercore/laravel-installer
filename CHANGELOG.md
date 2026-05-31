# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-05-31

### Added
- **Config-driven step pipeline**: Installation steps are now defined in `config('installer.steps')`. Add, remove, or reorder steps without touching package source.
- **`on_admin_created` hook**: Closure-based callback for assigning roles/permissions after admin creation.
- **`environment_fields` config**: Host apps can inject extra `.env` form fields (checkbox, text) into the database setup screen.
- **Full localization (i18n)**: All UI strings extracted to `lang/en/installer.php`. Publish with `--tag=installer-lang`.
- **API-friendly middleware**: JSON requests to an uninstalled app receive a `503 JSON` response instead of an HTML redirect.
- **`StepManager::getPreviousStep()`**, **`isLastStep()`**, **`isFirstStep()`** helper methods.

### Changed
- Steps are now resolved through the Laravel container (enabling automatic dependency injection).
- `StepManager` is registered as a singleton.
- Admin role assignment removed from `CreateAdmin` step — use `on_admin_created` closure instead.
- `InstallerServiceProvider` boots steps from config instead of hardcoding them.

### Removed
- **BREAKING**: `SelectIndustry` step removed from the package (project-specific; move to your app).
- **BREAKING**: `admin_role` config key removed (replaced by `on_admin_created` closure).
- **BREAKING**: `custom_steps` config key removed (replaced by the `steps` array).
- **BREAKING**: `BOOKFLOW_MULTI_TENANT` env variable no longer written by default (use `environment_fields`).
- Verticals-specific seeder lookup in `RunMigrations` (use `config('installer.seeder')` directly).
- Hardcoded `'bookflow'` database default in installer state.

### Fixed
- Whitelisted dynamic hashed Livewire v3+ script and update paths (`/livewire-*[hash]*`) to prevent installer redirection and Alpine.js startup crashes.
- Replaced `echo "<script>..."` / `exit` hack with proper Livewire redirect in `mount()`.

## [1.0.0] - 2026-01-09

### Added
- Initial release
- 5-step installation wizard (Requirements, Permissions, Database, Admin, Complete)
- Server requirements checking (PHP version, extensions)
- Directory permissions validation
- Database connection testing with auto-creation
- Admin account creation with configurable model/role
- Livewire 3 powered UI with Tailwind CSS
- Configurable theme colors
- Support for Laravel 10 and 11
- Middleware to block app access until installation is complete
- Publishable config and views for customization
- Custom step support for extending the wizard

### Security
- SQL injection protection for database names
- Proper escaping of .env values with special characters
- Post-installation route blocking
