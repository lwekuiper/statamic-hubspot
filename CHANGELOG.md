# Changelog

All notable changes to `statamic-hubspot` will be documented in this file.

## 2.1.1 (2026-11-22)

### What's changed
- Minimum Statamic version raised to 6.10. Version 2.1.0 calls `registerSerializableClasses`, which does not exist before 6.10, so every request failed on Statamic 6.5 to 6.9.

### Fixed
- Every HubSpot control panel route, including the form field and contact property endpoints, now requires the `configure forms` permission. Before, only the listing page checked it.
- Control panel requests with an unknown `site` handle are rejected. The handle is used in the form config file path, so it is no longer possible to write a config outside the HubSpot folder.
- Form submissions with an empty email field are skipped, and a failed HubSpot request no longer stops the submission. The error is reported to your log, the visitor sees the normal success response and the notification emails are still sent.

## 2.1.0 (2026-05-28)

### What's new
- Laravel 13 support

### What's changed
- Minimum PHP version raised to 8.3 (required by Laravel 13)
- Minimum Statamic version raised to 6.5 (first version with Laravel 13 support)

### Fixed
- FormConfig entries no longer fail to deserialize from Laravel 13's hardened cache (allowlist registered via Statamic's `registerSerializableClasses` API).

## 2.0.2 (2026-02-28)

### Fixed
- Namespace Stache store handle from `form-configs` to `hubspot-form-configs` to avoid conflicts when multiple form-config addons are installed. Run `php please stache:clear` after upgrading.

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
