<?php

namespace Bmatovu\QueryDecorator\Tests\Support;

use Bmatovu\QueryDecorator\Tests\TestCase;

class HelpersTest extends TestCase
{
    public function test_can_get_array_value_or_default()
    {
        $user = [
            'name' => 'John Doe',
            'email_verified_at' => null,
            'role' => [
                'id' => '3897b5f2-3b14-4425-9fbe-e3436868422a',
                'name' => 'Sys Admin',
            ],
        ];

        $this->assertEquals('John Doe', array_get($user, 'name'));
        $this->assertNull(array_get($user, 'email_verified_at'));
        $this->assertNull(array_get($user, 'created_at'));
        $this->assertFalse(array_get($user, 'is_enabled', false));
        $this->assertEquals([
            'id' => '3897b5f2-3b14-4425-9fbe-e3436868422a',
            'name' => 'Sys Admin',
        ], array_get($user, 'role'));
        $this->assertEquals('Sys Admin', array_get($user, 'role.name'));
    }
}
