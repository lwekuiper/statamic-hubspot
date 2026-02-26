<?php

namespace Lwekuiper\StatamicHubspot\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Lwekuiper\StatamicHubspot\Facades\FormConfig;
use Statamic\Facades\Addon;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form as FormFacade;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint as BlueprintContract;
use Statamic\Forms\Form;
use Statamic\Http\Controllers\CP\CpController;

class FormConfigController extends CpController
{
    public function index(Request $request)
    {
        $user = User::current();
        abort_unless($user->isSuper() || $user->hasPermission('configure forms'), 401);

        [$site, $edition] = $this->getAddonContext($request);

        $urlParams = $edition === 'pro' ? ['site' => $site] : [];

        $forms = FormFacade::all()
            ->mapWithKeys(fn ($form) => [
                $form->handle() => [
                    'title' => $form->title(),
                    'edit_url' => cp_route('hubspot.edit', ['form' => $form->handle(), ...$urlParams]),
                ],
            ]);

        $formConfigs = FormConfig::whereLocale($site)
            ->mapWithKeys(fn ($formConfig) => [
                $formConfig->handle() => [
                    'active' => $formConfig->exists(),
                    'delete_url' => $formConfig->deleteUrl(),
                ],
            ]);

        $viewData = [
            'formConfigs' => $forms->mergeRecursive($formConfigs)->values(),
        ];

        if ($edition === 'pro') {
            $viewData = array_merge($viewData, [
                'locale' => $site,
                'localizations' => Site::all()->map(fn ($localization) => [
                    'handle' => $localization->handle(),
                    'name' => $localization->name(),
                    'active' => $localization->handle() === $site,
                    'url' => cp_route('hubspot.index', ['site' => $localization->handle()]),
                ])->values()->all(),
            ]);
        }

        if ($request->wantsJson()) {
            return $viewData;
        }

        if ($viewData['formConfigs']->isEmpty()) {
            return Inertia::render('hubspot::Empty', [
                'createUrl' => cp_route('forms.create'),
            ]);
        }

        return Inertia::render('hubspot::Index', [
            'createFormUrl' => cp_route('forms.create'),
            'formConfigs' => $viewData['formConfigs'],
            'localizations' => $viewData['localizations'] ?? [],
            'site' => $viewData['locale'] ?? '',
        ]);
    }

    public function edit(Request $request, Form $form)
    {
        [$site, $edition] = $this->getAddonContext($request);

        $blueprint = $this->getBlueprint();
        $fields = $blueprint->fields();

        if ($formConfig = FormConfig::find($form->handle(), $site)) {
            $fields = $fields->addValues($formConfig->fileData());
        }

        $fields = $fields->preProcess();

        $viewData = [
            'title' => $form->title(),
            'action' => cp_route('hubspot.update', ['form' => $form->handle(), 'site' => $site]),
            'deleteUrl' => $formConfig?->deleteUrl(),
            'listingUrl' => cp_route('hubspot.index', ['site' => $site]),
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
        ];

        if ($edition === 'pro') {
            $viewData = array_merge($viewData, [
                'locale' => $site,
                'localizations' => Site::all()->map(fn ($localization) => [
                    'handle' => $localization->handle(),
                    'name' => $localization->name(),
                    'active' => $localization->handle() === $site,
                    'url' => cp_route('hubspot.edit', ['form' => $form->handle(), 'site' => $localization->handle()]),
                ])->values()->all(),
            ]);
        }

        if ($request->wantsJson()) {
            return $viewData;
        }

        return Inertia::render('hubspot::Edit', [
            'title' => $viewData['title'],
            'action' => $viewData['action'],
            'deleteUrl' => $viewData['deleteUrl'],
            'listingUrl' => $viewData['listingUrl'],
            'blueprint' => $viewData['blueprint'],
            'values' => $viewData['values'],
            'meta' => $viewData['meta'],
            'localizations' => $viewData['localizations'] ?? [],
            'site' => $viewData['locale'] ?? '',
        ]);
    }

    public function update(Request $request, Form $form)
    {
        [$site] = $this->getAddonContext($request);

        $blueprint = $this->getBlueprint();
        $fields = $blueprint->fields()->addValues($request->all());
        $fields->validate();

        $values = $fields->process()->values()->all();

        if (! $formConfig = FormConfig::find($form->handle(), $site)) {
            $formConfig = FormConfig::make()->form($form)->locale($site);
        }

        $formConfig = $formConfig
            ->emailField($values['email_field'])
            ->consentField($values['consent_field'] ?? null)
            ->contactProperties($values['contact_properties'] ?? []);

        $formConfig->save();

        return response()->json(['message' => __('Configuration saved')]);
    }

    public function destroy(Request $request, Form $form)
    {
        [$site] = $this->getAddonContext($request);

        if (! $formConfig = FormConfig::find($form->handle(), $site)) {
            return $this->pageNotFound();
        }

        $formConfig->delete();

        return response('', 204);
    }

    /**
     * Get the site and edition based on the request.
     */
    private function getAddonContext(Request $request): array
    {
        $edition = Addon::get('lwekuiper/statamic-hubspot')->edition();

        $site = $edition === 'pro'
            ? $request->site ?? Site::selected()->handle()
            : Site::default()->handle();

        return [$site, $edition];
    }

    /**
     * Get the blueprint.
     */
    private function getBlueprint(): BlueprintContract
    {
        return Blueprint::make()->setContents([
            'tabs' => [
                'general' => [
                    'display' => 'General',
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'email_field',
                                    'field' => [
                                        'display' => 'Email Field',
                                        'instructions' => 'The form field that contains the email of the subscriber.',
                                        'type' => 'statamic_form_fields',
                                        'validate' => 'required',
                                        'width' => 50,
                                    ],
                                ],
                                [
                                    'handle' => 'consent_field',
                                    'field' => [
                                        'display' => 'Consent Field',
                                        'instructions' => 'The form field that contains the consent of the subscriber.',
                                        'type' => 'statamic_form_fields',
                                        'width' => 50,
                                    ],
                                ],
                                [
                                    'handle' => 'contact_properties',
                                    'field' => [
                                        'display' => 'Contact Properties',
                                        'instructions' => 'Add the form fields you want to map to HubSpot contact properties.',
                                        'type' => 'grid',
                                        'mode' => 'table',
                                        'listable' => 'hidden',
                                        'fullscreen' => false,
                                        'add_row' => 'Add Contact Property',
                                        'fields' => [
                                            [
                                                'handle' => 'statamic_field',
                                                'field' => [
                                                    'display' => 'Form Field',
                                                    'type' => 'statamic_form_fields',
                                                ],
                                            ],
                                            [
                                                'handle' => 'hubspot_field',
                                                'field' => [
                                                    'display' => 'HubSpot Property',
                                                    'type' => 'hubspot_contact_properties',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
