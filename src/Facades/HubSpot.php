<?php

namespace Lwekuiper\StatamicHubspot\Facades;

use Illuminate\Support\Facades\Facade;
use Lwekuiper\StatamicHubspot\Connectors\HubSpotConnector;

class HubSpot extends Facade
{
    protected static function getFacadeAccessor()
    {
        return HubSpotConnector::class;
    }
}
