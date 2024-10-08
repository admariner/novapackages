<?php

namespace Tests\Feature\Api;

use App\Package;
use App\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagsOnApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_attaches_tags_to_api_responses(): void
    {
        $package = Package::factory()->create();
        $tag = Tag::factory()->create();
        $package->tags()->attach($tag);

        $apiCall = $this->get('api/recent')->json();

        $tags = reset($apiCall['data'])['tags'];
        $this->assertCount(1, $tags);
        $this->assertEquals($tag->slug, reset($tags)['slug']);
        $this->assertEquals($tag->name, reset($tags)['name']);
    }
}
