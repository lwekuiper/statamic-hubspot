<?php

namespace Lwekuiper\StatamicHubspot\Tests\Feature;

use Lwekuiper\StatamicHubspot\Facades\FormConfig;
use Lwekuiper\StatamicHubspot\Tests\FakesRoles;
use Lwekuiper\StatamicHubspot\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class UpdateFormConfigTest extends TestCase
{
    use FakesRoles;
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_updates_a_form_config()
    {
        $this->setTestRoles(['test' => ['access cp', 'configure forms']]);
        $user = tap(User::make()->assignRole('test')->makeSuper())->save();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->consentField('consent');
        $formConfig->save();

        $this
            ->from('/here')
            ->actingAs($user)
            ->patchJson(cp_route('hubspot.update', ['form' => $form->handle()]), [
                'email_field' => 'subscriber_email',
                'consent_field' => 'accepts_marketing',
                'contact_properties' => [
                    ['statamic_field' => 'email', 'hubspot_field' => 'email'],
                    ['statamic_field' => 'name', 'hubspot_field' => 'firstname'],
                ],
            ])
            ->assertSuccessful();

        $this->assertCount(1, FormConfig::all());
        $formConfig = FormConfig::find('test_form', 'default');
        $this->assertEquals('subscriber_email', $formConfig->emailField());
        $this->assertEquals('accepts_marketing', $formConfig->consentField());
    }

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        $this->actingAs($user)
            ->patchJson(cp_route('hubspot.update', ['form' => $form->handle()]), ['email_field' => 'email'])
            ->assertUnauthorized();

        $this->assertCount(0, FormConfig::all());
    }

    #[Test]
    public function it_rejects_a_site_that_does_not_exist()
    {
        $this->setSites([
            'en' => ['url' => '/', 'locale' => 'en'],
            'nl' => ['url' => '/nl/', 'locale' => 'nl'],
        ]);
        $this->setProEdition();

        $user = tap(User::make()->makeSuper())->save();

        $form = tap(Form::make('test_form')->title('Test Form'))->save();

        $this->actingAs($user)
            ->patchJson(cp_route('hubspot.update', ['form' => $form->handle(), 'site' => '../forms']), ['email_field' => 'email'])
            ->assertNotFound();

        $this->assertCount(0, FormConfig::all());
    }
}
