# Subscribe forms to HubSpot

This package provides an easy way to integrate HubSpot with Statamic forms and allows for multi-site configurations with the Pro edition.

## Features

This addon allows you to:

### Lite edition
- Configure Statamic forms to subscribe to a HubSpot list.

### Pro edition
- Adds multi-site functionality.
- For site networks where the same form needs to subscribe to a different HubSpot list depending on the current site.

## Requirements

* PHP 8.2+
* Laravel 10.0+
* Statamic 4.0+

## How to Install

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or run the following command from your project root:

``` bash
composer require lwekuiper/statamic-hubspot
```

The package will automatically register itself.

## Configuration

Set your HubSpot Access Token in your `.env` file.

```yaml
HUBSPOT_ACCESS_TOKEN=your-private-app-token-here
```

## Pro edition

> **Note**
> The Pro edition of this addon requires Statamic Pro to enable multi-site capabilities.

After purchasing the pro edition, you can enable it by setting the edition of the addon to `'pro'` in the `config/statamic/editions.php` file of your project:

```php
    'addons' => [
        'lwekuiper/statamic-hubspot' => 'pro'
    ],
```

## How to Use

Create your Statamic [forms](https://statamic.dev/forms#content) as usual and add the HubSpot configuration.
