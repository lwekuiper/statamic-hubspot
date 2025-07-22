<?php

namespace Lwekuiper\StatamicHubspot\Tests\Listeners;

use Illuminate\Support\Facades\Event;
use Lwekuiper\StatamicHubspot\Facades\FormConfig;
use Lwekuiper\StatamicHubspot\Listeners\AddFromSubmission;
use Lwekuiper\StatamicHubspot\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Statamic\Events\SubmissionCreated;
use Statamic\Facades\Form;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class AddFromSubmissionTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_should_handle_submission_created_event()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();

        $event = new SubmissionCreated($submission);

        $this->mock(AddFromSubmission::class)->shouldReceive('handle')->with($event)->once();

        Event::dispatch($event);
    }

    #[Test]
    public function it_returns_true_when_consent_field_is_not_configured()
    {
        $listener = new AddFromSubmission;

        $hasConsent = $listener->hasConsent();

        $this->assertTrue($hasConsent);
    }

    #[Test]
    public function it_returns_false_when_configured_consent_field_is_false()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();
        $submission->data(['consent' => false]);

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->consentField('consent');
        $formConfig->save();

        $listener = new AddFromSubmission;
        $listener->hasFormConfig($submission);

        $hasConsent = $listener->hasConsent();

        $this->assertFalse($hasConsent);
    }

    #[Test]
    public function it_returns_true_when_configured_consent_field_is_true()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();
        $submission->data(['consent' => true]);

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->consentField('consent');
        $formConfig->save();

        $listener = new AddFromSubmission;
        $listener->hasFormConfig($submission);

        $hasConsent = $listener->hasConsent();

        $this->assertTrue($hasConsent);
    }

    #[Test]
    public function it_returns_false_when_form_config_is_missing()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();

        $listener = new AddFromSubmission($submission->data());

        $hasFormConfig = $listener->hasFormConfig($submission);

        $this->assertFalse($hasFormConfig);
    }

    #[Test]
    public function it_returns_true_when_form_config_is_present()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->consentField('consent');
        $formConfig->save();

        $listener = new AddFromSubmission;

        $hasFormConfig = $listener->hasFormConfig($submission);

        $this->assertTrue($hasFormConfig);
    }

    #[Test]
    public function it_correctly_uses_email_field_from_config()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();

        $submission->data([
            'custom_email_field' => 'john@example.com',
        ]);

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('custom_email_field');
        $formConfig->save();

        $listener = new AddFromSubmission;
        $listener->hasFormConfig($submission);

        $email = $listener->getEmail();

        $this->assertEquals('john@example.com', $email);
    }

    #[Test]
    public function it_correctly_prepares_merge_data_for_sync_contact()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();

        $submission->data([
            'email' => 'john@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'custom_field' => 'Custom Value',
        ]);

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->consentField('consent');
        $formConfig->contactProperties([
            ['statamic_field' => 'email', 'hubspot_field' => 'email'],
            ['statamic_field' => 'first_name', 'hubspot_field' => 'firstname'],
            ['statamic_field' => 'last_name', 'hubspot_field' => 'lastname'],
            ['statamic_field' => 'custom_field', 'hubspot_field' => 'custom_property'],
        ]);
        $formConfig->save();

        $listener = new AddFromSubmission;
        $listener->hasFormConfig($submission);

        $reflectionMethod = new ReflectionMethod(AddFromSubmission::class, 'getContactData');
        $reflectionMethod->setAccessible(true);
        $contactData = $reflectionMethod->invoke($listener);

        $this->assertEquals([
            'email' => 'john@example.com',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'custom_property' => 'Custom Value',
        ], $contactData);
    }

    #[Test]
    public function it_handles_array_fields()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();

        $submission->data([
            'email' => 'john@example.com',
            'interests' => ['Sports', 'Music', 'Reading'],
            'skills' => ['PHP', '', 'JavaScript', null, 'Laravel'],
            'empty_array' => [],
            'null_values_only' => [null, '', null],
            'mixed_empty' => ['Valid', '', null, 'Also Valid'],
            'empty_string' => '',
            'null_value' => null,
        ]);

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email');
        $formConfig->contactProperties([
            ['statamic_field' => 'email', 'hubspot_field' => 'email'],
            ['statamic_field' => 'first_name', 'hubspot_field' => 'firstName'],
            ['statamic_field' => 'last_name', 'hubspot_field' => 'lastName'],
            ['statamic_field' => 'interests', 'hubspot_field' => 'interests'],
            ['statamic_field' => 'skills', 'hubspot_field' => 'skills'],
            ['statamic_field' => 'empty_array', 'hubspot_field' => 'empty_field'],
            ['statamic_field' => 'null_values_only', 'hubspot_field' => 'null_field'],
            ['statamic_field' => 'mixed_empty', 'hubspot_field' => 'mixed_field'],
            ['statamic_field' => 'empty_string', 'hubspot_field' => 'empty_string_field'],
            ['statamic_field' => 'null_value', 'hubspot_field' => 'null_field'],
        ]);
        $formConfig->save();

        $listener = new AddFromSubmission;
        $listener->hasFormConfig($submission);

        $reflectionMethod = new ReflectionMethod(AddFromSubmission::class, 'getContactData');
        $reflectionMethod->setAccessible(true);
        $contactData = $reflectionMethod->invoke($listener);

        $this->assertEquals([
            'email' => 'john@example.com',
            'interests' => 'Sports, Music, Reading',
            'skills' => 'PHP, JavaScript, Laravel',
            'mixed_field' => 'Valid, Also Valid',
        ], $contactData);
    }
}
