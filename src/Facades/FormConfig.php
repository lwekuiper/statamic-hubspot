<?php

namespace Lwekuiper\StatamicHubspot\Facades;

use Illuminate\Support\Facades\Facade;
use Lwekuiper\StatamicHubspot\Stache\FormConfigRepository;

class FormConfig extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FormConfigRepository::class;
    }
}
