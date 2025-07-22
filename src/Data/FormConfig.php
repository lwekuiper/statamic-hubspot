<?php

namespace Lwekuiper\StatamicHubspot\Data;

use Lwekuiper\StatamicHubspot\Facades;
use Statamic\Contracts\Data\Localization;
use Statamic\Contracts\Forms\Form;
use Statamic\Data\ExistsAsFile;
use Statamic\Facades\Form as FormFacade;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class FormConfig implements Localization
{
    use ExistsAsFile;
    use FluentlyGetsAndSets;

    protected $form;

    protected $locale;

    protected $handle;

    protected $emailField;

    protected $consentField;

    protected $contactProperties = [];

    public function form($form = null)
    {
        return $this->fluentlyGetOrSet('form')
            ->getter(function ($form) {
                return $form instanceof Form ? $form : FormFacade::find($form);
            })
            ->args(func_get_args());
    }

    public function locale($locale = null)
    {
        return $this->fluentlyGetOrSet('locale')->args(func_get_args());
    }

    public function id()
    {
        return $this->handle().'::'.$this->locale();
    }

    public function handle()
    {
        return $this->form instanceof Form ? $this->form->handle() : $this->form;
    }

    public function title()
    {
        return $this->form()->title();
    }

    public function emailField($emailField = null)
    {
        return $this->fluentlyGetOrSet('emailField')->args(func_get_args());
    }

    public function consentField($consentField = null)
    {
        return $this->fluentlyGetOrSet('consentField')->args(func_get_args());
    }

    public function contactProperties($contactProperties = null)
    {
        return $this->fluentlyGetOrSet('contactProperties')->args(func_get_args());
    }

    public function exists()
    {
        return file_exists($this->path());
    }

    public function path()
    {
        return vsprintf('%s/%s%s.%s', [
            rtrim(Stache::store('form-configs')->directory(), '/'),
            Site::multiEnabled() ? $this->locale().'/' : '',
            $this->handle(),
            'yaml',
        ]);
    }

    public function editUrl()
    {
        return $this->cpUrl('hubspot.edit');
    }

    public function updateUrl()
    {
        return $this->cpUrl('hubspot.update');
    }

    public function deleteUrl()
    {
        return $this->cpUrl('hubspot.destroy');
    }

    protected function cpUrl($route)
    {
        $params = [$this->handle()];

        if (Site::multiEnabled()) {
            $params['site'] = $this->locale();
        }

        return cp_route($route, $params);
    }

    public function save()
    {
        return Facades\FormConfig::save($this);
    }

    public function delete()
    {
        return Facades\FormConfig::delete($this);
    }

    public function site()
    {
        return Site::get($this->locale());
    }

    public function fileData()
    {
        return [
            'email_field' => $this->emailField(),
            'consent_field' => $this->consentField(),
            'contact_properties' => $this->contactProperties(),
        ];
    }
}
