<?php

namespace Lwekuiper\StatamicHubspot\Http\Controllers;

use Lwekuiper\StatamicHubspot\Facades\HubSpot;
use Statamic\Facades\User;
use Statamic\Http\Controllers\Controller;
use Statamic\Support\Arr;

class GetContactPropertiesController extends Controller
{
    public function __invoke(): array
    {
        $user = User::current();
        abort_unless($user->isSuper() || $user->hasPermission('configure forms'), 401);

        return collect(Arr::get(HubSpot::getContactProperties(), 'results', []))
            ->map(fn ($mergeField) => ['id' => $mergeField['name'], 'label' => $mergeField['label']])
            ->values()
            ->all();
    }
}
