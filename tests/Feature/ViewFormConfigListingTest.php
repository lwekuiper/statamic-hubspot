<?php

namespace Lwekuiper\StatamicHubspot\Tests\Feature;

use Lwekuiper\StatamicHubspot\Facades\FormConfig;
use Lwekuiper\StatamicHubspot\Tests\FakesRoles;
use Lwekuiper\StatamicHubspot\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Support\Arr;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class ViewFormConfigListingTest extends TestCase
{
    use FakesRoles;
    use PreventsSavingStacheItemsToDisk;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('statamic.forms.forms', __DIR__.'/../__fixtures__/dev-null/resources/forms');
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = tap(User::make()->assignRole('test'))->save();

        tap(Form::make('test'))->save();
        $this->assertCount(1, Form::all());

        $this->actingAs($user)
            ->get(cp_route('hubspot.index'))
            ->assertUnauthorized();

        $this->assertCount(1, Form::all());
    }

    #[Test]
    public function it_lists_form_configs()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = User::make()->assignRole('test')->save();

        $this->assertCount(0, Form::all());

        $form_one = tap(Form::make('form_one')->title('Form One'))->save();
        $form_two = tap(Form::make('form_two')->title('Form Two'))->save();

        $formConfig = tap(FormConfig::make()->form($form_one)->locale('default'));
        $formConfig->emailField('email')->consentField('consent');
        $formConfig->save();

        $this->actingAs($user)
            ->get(cp_route('hubspot.index'))
            ->assertOk()
            ->assertViewHas('formConfigs', fn ($formConfigs) => $formConfigs->count() === 2)
            ->assertViewHas('formConfigs', function ($formConfigs) {
                return Arr::get($formConfigs, '0.title') === 'Form One'
                    && Arr::get($formConfigs, '0.edit_url') === url('/cp/hubspot/form_one/edit')
                    && Arr::get($formConfigs, '1.title') === 'Form Two'
                    && Arr::get($formConfigs, '1.edit_url') === url('/cp/hubspot/form_two/edit');
            });
    }

    #[Test]
    public function it_lists_form_configs_with_multi_site()
    {
        $this->setProEdition();

        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en', 'name' => 'English'],
            'nl' => ['url' => 'http://localhost/nl/', 'locale' => 'nl', 'name' => 'Dutch'],
        ]);

        Site::setSelected('nl');

        $this->setTestRoles(['test' => [
            'access cp',
            'access en site',
            'access nl site',
            'configure forms',
        ]]);
        $user = User::make()->assignRole('test')->save();

        $form_one = tap(Form::make('form_one')->title('Form One'))->save();
        $form_two = tap(Form::make('form_two')->title('Form Two'))->save();

        $formConfig = tap(FormConfig::make()->form($form_one)->locale('nl'));
        $formConfig->emailField('email')->consentField('consent');
        $formConfig->save();

        $this->actingAs($user)
            ->get(cp_route('hubspot.index'))
            ->assertOk()
            ->assertViewHas('formConfigs', fn ($formConfigs) => $formConfigs->count() === 2)
            ->assertViewHas('formConfigs', function ($formConfigs) {
                return Arr::get($formConfigs, '0.title') === 'Form One'
                    && Arr::get($formConfigs, '0.edit_url') === url('/cp/hubspot/form_one/edit?site=nl')
                    && Arr::get($formConfigs, '1.title') === 'Form Two'
                    && Arr::get($formConfigs, '1.edit_url') === url('/cp/hubspot/form_two/edit?site=nl');
            });
    }
}
