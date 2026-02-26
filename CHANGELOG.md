# Changelog

All notable changes to `statamic-hubspot` will be documented in this file.

## 2.0.1 (2026-02-26)

### Fixed
- Fix blueprint loading failure where `getBlueprint()` returned `null` because `Blueprint::find()` could not locate the YAML blueprint file. The blueprint is now built programmatically. (#2)

## 2.0.0 (2026-02-09)

### What's new
- Statamic 6 support
- Inertia.js pages replacing Blade views
- Vue 3 composition API components

### What's changed
- Requires Statamic 6 and Laravel 12
- Dropped support for Statamic 4/5 and Laravel 10/11

## 1.0.0 (2024-12-19)

### What's new
- Initial release with HubSpot integration
