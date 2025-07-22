@extends('statamic::layout')

@section('title', __('Edit HubSpot'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('hubspot.index'),
        'title' => 'HubSpot'
    ])

    <hubspot-publish-form
        title="{{ $title }}"
        initial-action="{{ $action }}"
        initial-delete-url="{{ $deleteUrl }}"
        initial-listing-url="{{ $listingUrl }}"
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-meta='{{ empty($meta) ? '{}' : json_encode($meta) }}'
        :initial-values='{{ empty($values) ? '{}' : json_encode($values) }}'
        :initial-localizations="{{ empty($localizations) ? '{}' : json_encode($localizations) }}"
        initial-site="{{ empty($locale) ? '' : $locale }}"
    ></hubspot-publish-form>

@stop
