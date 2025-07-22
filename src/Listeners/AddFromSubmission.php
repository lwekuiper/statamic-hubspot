<?php

namespace Lwekuiper\StatamicHubspot\Listeners;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Lwekuiper\StatamicHubspot\Facades\FormConfig;
use Lwekuiper\StatamicHubspot\Facades\HubSpot;
use Statamic\Events\SubmissionCreated;
use Statamic\Facades\Addon;
use Statamic\Facades\Site;
use Statamic\Forms\Submission;

class AddFromSubmission
{
    private Collection $data;

    private Collection $config;

    public function __construct()
    {
        $this->data = collect();
        $this->config = collect();
    }

    public function getEmail(): string
    {
        return $this->data->get($this->config->get('email_field', 'email'));
    }

    public function hasFormConfig(Submission $submission): bool
    {
        $edition = Addon::get('lwekuiper/statamic-hubspot')->edition();

        $site = $edition === 'pro'
            ? Site::findByUrl(URL::previous()) ?? Site::default()
            : Site::default();

        if (! $formConfig = FormConfig::find($submission->form()->handle(), $site->handle())) {
            return false;
        }

        $this->data = collect($submission->data());

        $this->config = collect($formConfig->fileData());

        return true;
    }

    public function hasConsent(): bool
    {
        if (! $field = $this->config->get('consent_field')) {
            return true;
        }

        return filter_var(
            Arr::get(Arr::wrap($this->data->get($field, false)), 0, false),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public function handle(SubmissionCreated $event): void
    {
        if (! $this->hasFormConfig($event->submission)) {
            return;
        }

        if (! $this->hasConsent()) {
            return;
        }

        if (! $contact = $this->createOrUpdateContact()) {
            return;
        }
    }

    private function createOrUpdateContact(): ?array
    {
        $email = $this->getEmail();
        $contactData = $this->getContactData();

        return HubSpot::createOrUpdateContact($email, $contactData);
    }

    private function getContactData(): array
    {
        $contactProperties = $this->config->get('contact_properties', []);

        $data = [];

        foreach ($contactProperties as $mapping) {
            $statamicField = $mapping['statamic_field'];
            $hubspotField = $mapping['hubspot_field'];

            $value = $this->data->get($statamicField);

            if (is_array($value)) {
                $value = implode(', ', array_filter($value));
            }

            if ($value !== null && $value !== '') {
                $data[$hubspotField] = (string) $value;
            }
        }

        return $data;
    }
}
