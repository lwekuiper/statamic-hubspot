<?php

namespace Lwekuiper\StatamicHubspot\Fieldtypes;

use Statamic\Fields\Fieldtype;

class HubSpotContactProperties extends Fieldtype
{
    protected $component = 'hubspot_contact_properties';

    public static function handle()
    {
        return 'hubspot_contact_properties';
    }
}
