<?php

namespace Lwekuiper\StatamicHubspot\Tests\Stache;

use Lwekuiper\StatamicHubspot\Data\FormConfig;
use Lwekuiper\StatamicHubspot\Facades\FormConfig as FormConfigFacade;
use Lwekuiper\StatamicHubspot\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Path;
use Statamic\Facades\Stache;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class FormConfigStoreTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    private $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Stache::store('hubspot-form-configs');
    }

    #[Test]
    public function it_makes_form_config_instances_from_files()
    {
        $contents = 'email_field: email';
        $item = $this->store->makeItemFromFile(Path::tidy($this->store->directory().'/test_form.yaml'), $contents);

        $this->assertInstanceOf(FormConfig::class, $item);
        $this->assertEquals('test_form::default', $item->id());
        $this->assertEquals('test_form', $item->handle());
        $this->assertEquals('email', $item->emailField());
    }

    #[Test]
    public function it_makes_form_config_instances_from_files_when_using_multisite()
    {
        $this->setSites([
            'en' => ['url' => 'https://example.com/'],
            'nl' => ['url' => 'https://example.com/nl/'],
        ]);

        $contents = 'email_field: email';
        $item = $this->store->makeItemFromFile(Path::tidy($this->store->directory().'/nl/test_form.yaml'), $contents);

        $this->assertInstanceOf(FormConfig::class, $item);
        $this->assertEquals('test_form::nl', $item->id());
        $this->assertEquals('test_form', $item->handle());
        $this->assertEquals('email', $item->emailField());
    }

    #[Test]
    public function it_uses_the_form_handle_and_locale_as_the_item_key()
    {
        $this->assertEquals(
            'test_form::default',
            $this->store->getItemKey(FormConfigFacade::make()->form('test_form')->locale('default'))
        );
    }

    #[Test]
    public function it_saves_to_disk()
    {
        $formConfig = FormConfigFacade::make()->form('test_form')
            ->emailField('email');

        $this->store->save($formConfig);

        $this->assertStringEqualsFile(Path::tidy($this->store->directory().'/test_form.yaml'), $formConfig->fileContents());
    }

    #[Test]
    public function it_saves_to_disk_with_multiple_sites()
    {
        $this->setSites([
            'en' => ['url' => 'https://example.com/'],
            'nl' => ['url' => 'https://example.com/nl/'],
        ]);

        $enFormConfig = FormConfigFacade::make()->form('test_form')->locale('en')->emailField('email');
        $nlFormConfig = FormConfigFacade::make()->form('test_form')->locale('nl')->emailField('email');

        $this->store->save($enFormConfig);
        $this->store->save($nlFormConfig);

        $this->assertStringEqualsFile(Path::tidy($this->store->directory().'/en/test_form.yaml'), $enFormConfig->fileContents());
        $this->assertStringEqualsFile(Path::tidy($this->store->directory().'/nl/test_form.yaml'), $nlFormConfig->fileContents());
    }
}
