<?php

namespace Lwekuiper\StatamicHubspot\Http\Controllers;

use Illuminate\Http\Request;
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

        return view('statamic-hubspot::index', $viewData);
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

        return view('statamic-hubspot::edit', $viewData);
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
        return Blueprint::find('statamic-hubspot::config');
    }
}
