# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
