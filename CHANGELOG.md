# Changelog

## 2.0.0 (2025-07-18)

### Breaking Changes
- **Complete rewrite**: Converted addon from ActiveCampaign to HubSpot integration
- **Namespace change**: Changed from `Lwekuiper\StatamicActivecampaign` to `Lwekuiper\StatamicHubspot`
- **Configuration change**: Updated config structure to use HubSpot API
- **Field mapping change**: Replaced merge fields with HubSpot contact properties
- **Removed tags**: HubSpot integration focuses on contact properties instead of tags

### Added
- HubSpot API integration using HubSpot's REST API
- Contact properties mapping instead of merge fields
- HubSpot list integration for adding contacts to lists
- Updated UI components for HubSpot branding

### Changed
- API connector completely rewritten for HubSpot
- Configuration now uses single API key instead of API key + URL
- Form configuration now uses `contact_properties` instead of `merge_fields`
- Routes updated from `/activecampaign/` to `/hubspot/`
- Admin interface updated with HubSpot branding

### Removed
- ActiveCampaign API integration
- Tag functionality (not applicable to HubSpot)
- Merge fields (replaced with contact properties)

## 1.0.0 (2024-12-19)

Initial release with HubSpot integration.

All notable changes to `statamic-hubspot` will be documented in this file.

### What's new
- HubSpot contact integration for Statamic forms! 🎉
