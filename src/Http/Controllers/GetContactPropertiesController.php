<?php

namespace Lwekuiper\StatamicHubspot\Http\Controllers;

use Lwekuiper\StatamicHubspot\Facades\HubSpot;
use Statamic\Http\Controllers\Controller;
use Statamic\Support\Arr;

class GetContactPropertiesController extends Controller
{
    public function __invoke(): array
    {
        return collect(Arr::get(HubSpot::getContactProperties(), 'results', []))
            ->map(fn ($mergeField) => ['id' => $mergeField['name'], 'label' => $mergeField['label']])
            ->values()
            ->all();
    }
}
