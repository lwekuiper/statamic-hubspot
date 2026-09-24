<?php

namespace Lwekuiper\StatamicHubspot\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Lwekuiper\StatamicHubspot\Tests\FakesRoles;
use Lwekuiper\StatamicHubspot\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class FetchOptionsTest extends TestCase
{
    use FakesRoles;
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_denies_access_if_you_dont_have_permission()
    {
        $this->setTestRoles(['test' => ['access cp']]);
        $user = User::make()->assignRole('test')->save();

        $form = tap(Form::make('test_form'))->save();

        Http::fake();

        $this->actingAs($user)
            ->getJson(cp_route('hubspot.form-fields', ['form' => $form->handle()]))
            ->assertUnauthorized();

        $this->actingAs($user)
            ->getJson(cp_route('hubspot.contact-properties'))
            ->assertUnauthorized();

        Http::assertNothingSent();
    }
}
