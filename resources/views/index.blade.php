@extends('statamic::layout')

@section('title', __('HubSpot'))

@section('content')

    @unless($formConfigs->isEmpty())

        <hubspot-listing
            create-form-url="{{ cp_route('forms.create') }}"
            :initial-form-configs="{{ json_encode($formConfigs) }}"
            :initial-localizations="{{ empty($localizations) ? '{}' : json_encode($localizations) }}"
            initial-site="{{ empty($locale) ? '' : $locale }}"
        ></hubspot-listing>

    @else

        @include('statamic::partials.empty-state', [
            'title' => __('HubSpot'),
            'description' => 'Forms are used to collect information from visitors and synchronize the data with HubSpot when there is a new submission.',
            'svg' => 'empty/form',
            'button_text' => __('Create Form'),
            'button_url' => cp_route('forms.create'),
        ])

    @endunless

@endsection
