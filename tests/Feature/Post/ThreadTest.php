<?php

namespace Tests\Feature\Post;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ThreadTest extends TestCase
{
    use RefreshDatabase;
    private $user, $secUser;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @group post-thread
     */
    public function test_user_can_make_thread(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
