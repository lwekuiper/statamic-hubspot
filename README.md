# Statamic HubSpot Integration

[![Latest Version](https://img.shields.io/packagist/v/lwekuiper/statamic-hubspot.svg?style=flat-square)](https://packagist.org/packages/lwekuiper/statamic-hubspot)
[![Total Downloads](https://img.shields.io/packagist/dt/lwekuiper/statamic-hubspot.svg?style=flat-square)](https://packagist.org/packages/lwekuiper/statamic-hubspot)

A powerful Statamic addon that seamlessly integrates your forms with HubSpot, featuring automatic contact synchronization, contact property mapping, and multi-site support.

> Have an idea? We'd love to hear it! Please [open a feature request](https://github.com/lwekuiper/statamic-hubspot/issues/new?labels=enhancement) on GitHub.

## Features

### Lite Edition
- **Form Integration**: Connect any Statamic form to HubSpot
- **Contact Sync**: Automatically create or update contacts in HubSpot
- **Contact Properties**: Map form fields to HubSpot contact properties
- **Consent Management**: Built-in GDPR compliance with consent field support

### Pro Edition
- **Multi-Site Support**: Configure different HubSpot settings per site
- **Site-Specific Routing**: Route form submissions based on the current site
- **Localized Configurations**: Manage separate configurations for each locale

## Requirements

- **PHP**: 8.3 or higher
- **Laravel**: 12.0 or higher
- **Statamic**: 6.0 or higher
- **HubSpot Account**: With a Private App access token

> **Note**: For Statamic 4.x and 5.x support, use version 1.x of this addon.

## Installation

### Via Statamic Control Panel
1. Navigate to **Tools > Addons** in your Statamic control panel
2. Search for "HubSpot"
3. Click **Install**

### Via Composer
```bash
composer require lwekuiper/statamic-hubspot
```

The package will automatically register itself.

## Configuration

### 1. HubSpot API Setup

Add your HubSpot access token to your `.env` file:

```env
HUBSPOT_ACCESS_TOKEN=your-private-app-token-here
```

> **Tip**: You can create a Private App in your HubSpot account under **Settings > Integrations > Private Apps**.

### 2. Publish Configuration (Optional)

To customize the addon settings, publish the configuration file:

```bash
php artisan vendor:publish --tag=statamic-hubspot-config
```

This creates `config/statamic/hubspot.php` where you can modify default settings.

## Pro Edition

> **Pro Features Available**
> Unlock multi-site capabilities with the Pro edition. Requires **Statamic Pro**.

### Upgrading to Pro

After purchasing the Pro edition, enable it in your `config/statamic/editions.php`:

```php
'addons' => [
    'lwekuiper/statamic-hubspot' => 'pro',
],
```

### Pro Benefits
- **Multi-Site Management**: Different HubSpot configurations per site
- **Site-Specific Routing**: Route submissions based on the current site
- **Enhanced Flexibility**: Perfect for agencies managing multiple client sites

## Documentation

For the full usage guide — including form setup, field mapping, consent management, multi-site configuration, troubleshooting, and more — see [DOCUMENTATION.md](DOCUMENTATION.md).

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This addon requires a license for use in production. You may use it without a license while developing locally.

## Support

- **Documentation**: [DOCUMENTATION.md](DOCUMENTATION.md)
- **Issues**: [GitHub Issues](https://github.com/lwekuiper/statamic-hubspot/issues)
- **Discussions**: [GitHub Discussions](https://github.com/lwekuiper/statamic-hubspot/discussions)

## Disclaimer

This addon is a third-party integration and is **not** affiliated with, endorsed by, or officially connected to HubSpot, Inc. "HubSpot" is a registered trademark of HubSpot, Inc. All product names, logos, and brands are property of their respective owners.

---

Made with love for the Statamic community
