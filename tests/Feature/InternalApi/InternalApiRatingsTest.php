<?php

namespace Tests\Feature\InternalApi;

use App\Collaborator;
use App\Package;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalApiRatingsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unauthenticated_users_cant_post_ratings(): void
    {
        $package = Package::factory()->create();
        $response = $this->post(route('internalapi.ratings.store'), [
            'package_id' => $package->id,
            'rating' => 4,
        ]);

        $this->assertEquals(route('login'), $response->getTargetUrl());
    }

    /** @test */
    public function posting_a_rating_increases_the_packages_overall_rating(): void
    {
        $package = Package::factory()->create();
        $user = User::factory()->create();

        $this->be($user)->post(route('internalapi.ratings.store'), [
            'package_id' => $package->id,
            'rating' => 4,
        ]);

        $this->assertEquals(4, $package->average_rating);
    }

    /** @test */
    public function the_same_user_cant_add_two_ratings_to_a_package(): void
    {
        $package = Package::factory()->create();
        $user = User::factory()->create();

        $this->be($user)->post(route('internalapi.ratings.store'), [
            'package_id' => $package->id,
            'rating' => 4,
        ]);

        $this->be($user)->post(route('internalapi.ratings.store'), [
            'package_id' => $package->id,
            'rating' => 2,
        ]);

        $this->assertEquals(1, $package->ratings()->count());
    }

    /** @test */
    public function users_can_modify_their_ratings(): void
    {
        $package = Package::factory()->create();
        $user = User::factory()->create();

        $this->be($user)->post(route('internalapi.ratings.store'), [
            'package_id' => $package->id,
            'rating' => 4,
        ]);

        $this->be($user)->post(route('internalapi.ratings.store'), [
            'package_id' => $package->id,
            'rating' => 2,
        ]);

        $this->assertEquals(2, (int) $package->user_average_rating);
    }

    /** @test */
    public function a_user_cannot_rate_a_package_they_authored(): void
    {
        $user = User::factory()->create();
        $package = Package::factory()->create([
            'author_id' => Collaborator::factory()->create([
                'user_id' => $user->id,
            ]),
        ]);

        $request = $this->be($user)->post(route('internalapi.ratings.store'), [
            'package_id' => $package->id,
            'rating' => 5,
        ]);

        $this->assertEquals(0, $package->ratings()->count());
        $request->assertStatus(422);
        $request->assertJson([
            'status' => 'error',
            'message' => 'A package cannot be rated by its author',
        ]);
    }

    /** @test */
    public function a_user_cannot_rate_a_package_they_collaborated_on(): void
    {
        $user = User::factory()->create();
        $collaborator = Collaborator::factory()->make();
        $user->collaborators()->save($collaborator);
        $package = Package::factory()->create();
        $package->contributors()->save($collaborator);

        $request = $this->be($user)->post(route('internalapi.ratings.store'), [
            'package_id' => $package->id,
            'rating' => 5,
        ]);

        $this->assertEquals(0, $package->ratings()->count());
        $request->assertStatus(422);
        $request->assertJson([
            'status' => 'error',
            'message' => 'A package cannot be rated by its author',
        ]);
    }
}
