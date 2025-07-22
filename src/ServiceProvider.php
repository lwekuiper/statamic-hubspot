<?php

namespace Lwekuiper\StatamicHubspot;

use Lwekuiper\StatamicHubspot\Connectors\HubSpotConnector;
use Lwekuiper\StatamicHubspot\Fieldtypes\HubSpotContactProperties;
use Lwekuiper\StatamicHubspot\Fieldtypes\StatamicFormFields;
use Lwekuiper\StatamicHubspot\Listeners\AddFromSubmission;
use Lwekuiper\StatamicHubspot\Stache\FormConfigRepository;
use Lwekuiper\StatamicHubspot\Stache\FormConfigStore;
use Statamic\Events\SubmissionCreated;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Form;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Stache\Stache;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $fieldtypes = [
        HubspotContactProperties::class,
        StatamicFormFields::class,
    ];

    protected $listen = [
        SubmissionCreated::class => [AddFromSubmission::class],
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $vite = [
        'input' => [
            'resources/js/addon.js',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    public function register()
    {
        $this->app->singleton(FormConfigRepository::class, function () {
            return new FormConfigRepository($this->app['stache']);
        });

        $this->app->singleton(HubSpotConnector::class, function () {
            return new HubSpotConnector;
        });

        $this->publishes([
            __DIR__.'/../config/hubspot.php' => config_path('statamic/hubspot.php'),
        ], 'statamic-hubspot-config');
    }

    public function bootAddon()
    {
        Nav::extend(function ($nav) {
            $nav->create('HubSpot')
                ->section('Tools')
                ->route('hubspot.index')
                ->can('index', Form::class)
                ->icon('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 23 24"><path fill="currentColor" d="M17.66 7.90222V5.04423C18.0389 4.86772 18.3597 4.58757 18.5851 4.23639C18.8105 3.88521 18.9312 3.47745 18.9331 3.06057V2.99493C18.9331 1.77946 17.9445 0.794111 16.725 0.794111H16.6592C16.0735 0.794111 15.5119 1.02598 15.0978 1.43872C14.6837 1.85145 14.4511 2.41124 14.4511 2.99493V3.06057C14.453 3.47745 14.5737 3.88521 14.7991 4.23639C15.0245 4.58757 15.3453 4.86772 15.7242 5.04423V7.90222C14.6352 8.06843 13.6095 8.51848 12.7511 9.20683L4.88694 3.1019C4.94304 2.90013 4.97231 2.69269 4.97556 2.48443C4.97652 1.99395 4.83152 1.5142 4.55889 1.10587C4.28626 0.697541 3.89825 0.37897 3.44395 0.190456C2.98964 0.00194307 2.48944 -0.0480437 2.00663 0.046819C1.52381 0.141682 1.08007 0.377132 0.731531 0.723388C0.382992 1.06964 0.145313 1.51115 0.0485586 1.99206C-0.0481958 2.47297 0.000325484 2.97167 0.187977 3.42509C0.375628 3.87852 0.693979 4.26628 1.10277 4.53935C1.51156 4.81241 1.99242 4.9585 2.48452 4.95915C2.91505 4.95712 3.33751 4.84257 3.70972 4.62692L11.4535 10.633C10.7579 11.6805 10.395 12.9125 10.4121 14.1686C10.4292 15.4246 10.8255 16.6464 11.5495 17.6747L9.19421 20.023C9.00382 19.9623 8.80547 19.9301 8.6056 19.9273C8.20166 19.9277 7.80688 20.0473 7.47114 20.2712C7.13541 20.4951 6.8738 20.8131 6.71937 21.1851C6.56493 21.5572 6.52461 21.9665 6.60349 22.3613C6.68238 22.7562 6.87693 23.1189 7.16256 23.4036C7.44819 23.6883 7.81208 23.8822 8.20824 23.9608C8.60441 24.0394 9.01507 23.9992 9.38832 23.8453C9.76158 23.6914 10.0807 23.4306 10.3053 23.096C10.5299 22.7614 10.65 22.3679 10.6503 21.9653C10.6477 21.7661 10.6153 21.5684 10.5544 21.3786L12.8844 19.0554C13.6442 19.6385 14.5279 20.0404 15.4676 20.2305C16.4074 20.4206 17.3783 20.3937 18.306 20.152C19.2338 19.9103 20.0937 19.4602 20.8199 18.8361C21.5461 18.212 22.1194 17.4305 22.4958 16.5514C22.8722 15.6724 23.0417 14.7192 22.9913 13.7647C22.941 12.8103 22.6721 11.88 22.2054 11.0452C21.7386 10.2103 21.0863 9.49301 20.2984 8.94823C19.5106 8.40345 18.608 8.04564 17.66 7.90222ZM16.6941 17.3019C16.2635 17.3137 15.8349 17.2394 15.4336 17.0833C15.0323 16.9272 14.6664 16.6925 14.3577 16.3931C14.0489 16.0937 13.8034 15.7357 13.6359 15.3401C13.4683 14.9446 13.3819 14.5196 13.3819 14.0902C13.3819 13.6609 13.4683 13.2358 13.6359 12.8403C13.8034 12.4448 14.0489 12.0867 14.3577 11.7873C14.6664 11.4879 15.0323 11.2532 15.4336 11.0971C15.8349 10.941 16.2635 10.8667 16.6941 10.8785C17.5287 10.9076 18.3194 11.2585 18.8997 11.8571C19.4799 12.4558 19.8044 13.2555 19.8049 14.0878C19.8053 14.9202 19.4816 15.7203 18.9019 16.3195C18.3223 16.9187 17.532 17.2703 16.6974 17.3003"/></svg>')
                ->children(function () {
                    return Form::all()->sortBy->title()->map(function ($form) {
                        return Nav::item($form->title())
                            ->url(cp_route('hubspot.edit', $form->handle()))
                            ->can('edit', $form);
                    });
                });
        });

        Statamic::afterInstalled(function ($command) {
            $command->call('vendor:publish', [
                '--tag' => 'statamic-hubspot-config',
            ]);
        });

        $formConfigStore = new FormConfigStore;
        $formConfigStore->directory(base_path('resources/hubspot'));
        app(Stache::class)->registerStore($formConfigStore);
    }
}
