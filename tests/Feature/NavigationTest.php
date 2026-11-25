<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicHubspot\Tests\Feature;

use Illuminate\Support\Facades\File;
use Lwekuiper\StatamicHubspot\ServiceProvider;
use Lwekuiper\StatamicHubspot\Tests\FakesRoles;
use Lwekuiper\StatamicHubspot\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\CP\Navigation\Nav as Navigation;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class NavigationTest extends TestCase
{
    use FakesRoles;
    use PreventsSavingStacheItemsToDisk;

    private string $formsPath;

    protected function setUp(): void
    {
        parent::setUp();

        // AddonTestCase mocks Nav::build(), so build a real nav and register the addon's item on it.
        Nav::swap(new Navigation());
        $this->app->getProvider(ServiceProvider::class)->bootAddon();

        // Other tests leave form files without a title in the shared forms directory, and those break the nav.
        config(['statamic.forms.forms' => $this->formsPath = sys_get_temp_dir().'/hubspot-nav-forms-'.uniqid()]);

        Form::make('contact_us')->title('Contact Us')->save();

        $this->setTestRoles([
            'submissions' => ['access cp', 'view contact_us form submissions'],
            'configure' => ['access cp', 'configure forms'],
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->formsPath);

        parent::tearDown();
    }

    #[Test]
    public function it_hides_the_nav_item_from_users_who_can_only_view_submissions()
    {
        $this->actingAs(tap(User::make()->assignRole('submissions'))->save());

        $names = $this->navItemNames();

        $this->assertContains('Forms', $names);
        $this->assertNotContains('HubSpot', $names);
    }

    #[Test]
    public function it_shows_the_nav_item_to_users_who_can_configure_forms()
    {
        $this->actingAs(tap(User::make()->assignRole('configure'))->save());

        $this->assertContains('HubSpot', $this->navItemNames());
    }

    private function navItemNames(): array
    {
        return collect(Nav::build())
            ->flatMap(fn ($section) => collect($section['items'] ?? [])->map(fn ($item) => $item->display()))
            ->all();
    }
}
