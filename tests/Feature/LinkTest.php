<?php

namespace Tests\Feature;

use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_shorten_a_link()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/links', [
            'original_url' => 'https://www.google.com',
        ]);

        $this->assertDatabaseHas('links', [
            'original_url' => 'https://www.google.com',
        ]);

        $response->assertRedirect('/links');
    }

    public function test_a_user_can_update_a_link()
    {
        $user = User::factory()->create();
        $link = $user->links()->create([
            'original_url' => 'https://www.google.com',
            'short_code' => '123456',
        ]);

        $response = $this->actingAs($user)->patch("/links/{$link->id}", [
            'original_url' => 'https://www.google.com/updated',
        ]);

        $this->assertDatabaseHas('links', [
            'original_url' => 'https://www.google.com/updated',
        ]);

        $response->assertRedirect('/links');
    }

    public function test_a_user_can_delete_a_link()
    {
        $user = User::factory()->create();
        $link = $user->links()->create([
            'original_url' => 'https://www.google.com',
            'short_code' => '123456',
        ]);

        $response = $this->actingAs($user)->delete("/links/{$link->id}");

        $this->assertDatabaseMissing('links', [
            'id' => $link->id,
        ]);

        $response->assertRedirect('/links');
    }
}
