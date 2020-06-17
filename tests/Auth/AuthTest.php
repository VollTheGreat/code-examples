<?php

namespace Tests\Feature\Auth;

use Tests\Feature\TestCase;
use Illuminate\Database\Eloquent\Collection;
use App\Users\User;
use App\Roles\Role;
use App\Permissions\PermissionCategory;

class AuthTest extends TestCase
{
    /**
     * Test auth for a user with a permission
     */
    public function testUserHasPermission()
    {
        /** @var \App\Users\User */
        $user = factory(User::class)->create();
        $user->allow('test');

        $this->assertTrue($user->can('test'));
    }

    /**
     * Test auth for a user without a permission
     */
    public function testUserHasNoPermission()
    {
        /** @var \App\Users\User */
        $user = factory(User::class)->create();

        $this->assertFalse($user->can('test'));
    }

    /**
     * Test auth for a user with a forbidden permission
     */
    public function testUserHasForbiddenPermission()
    {
        /** @var \App\Users\User */
        $user = factory(User::class)->create();
        $user->forbid('test');

        $this->assertFalse($user->can('test'));
    }

    /**
     * Test auth for a user with a permission from role
     */
    public function testUserHasPermissionFromRole()
    {
        /** @var \App\Roles\Role */
        $role = factory(Role::class)->create();
        $role->allow('test');

        /** @var \App\Users\User */
        $user = factory(User::class)->create();
        $user->assign($role);

        $this->assertTrue($user->can('test'));
    }

    /**
     * Test auth for a user without a permission from role
     */
    public function testUserHasNoPermissionFromRole()
    {
        /** @var \App\Roles\Role */
        $role = factory(Role::class)->create();

        /** @var \App\Users\User */
        $user = factory(User::class)->create();
        $user->assign($role);

        $this->assertFalse($user->can('test'));
    }

    /**
     * Test auth for a user with a forbidden permission from role
     */
    public function testUserHasForbiddenPermissionFromRole()
    {
        /** @var \App\Roles\Role */
        $role = factory(Role::class)->create();
        $role->forbid('test');

        /** @var \App\Users\User */
        $user = factory(User::class)->create();
        $user->assign($role);

        $this->assertFalse($user->can('test'));
    }

    /**
     * Test auth precendence for a user with a forbidden permission
     */
    public function testUserForbiddenPermissionPrecendence()
    {
        /** @var \App\Permissions\PermissionCategory */
        $group = factory(PermissionCategory::class)->create();
        $group->allow('test');

        /** @var \App\Roles\Role */
        $role = factory(Role::class)->create();
        $role->allow('test');

        /** @var \App\Users\User */
        $user = factory(User::class)->create();
        $user->assign($role)->assignGroup($group);
        $user->forbid('test');

        $this->assertFalse($user->can('test'));
    }

    /**
     * Test auth precedence for a user with a forbidden permission on role
     * TODO: Implement valid auth precedence
     */
    public function testRoleForbiddenPermissionPrecendence()
    {
        $this->markTestSkipped(
            'Current auth implementation does not support valid permission precedence.'
        );

        /** @var \App\Permissions\PermissionCategory */
        $group = factory(PermissionCategory::class)->create();
        $group->allow('test');

        /** @var \App\Roles\Role */
        $role = factory(Role::class)->create();
        $role->forbid('test');

        /** @var \App\Users\User */
        $user = factory(User::class)->create();
        $user->assign($role)->assignGroup($group);
        $user->allow('test');

        $this->assertTrue($user->can('test'));
    }

    /**
     * Test auth for a user belonging to a group with a permission
     */
    public function testUserHasGroupWithPermission()
    {
        /** @var \App\Permissions\PermissionCategory */
        $group = factory(PermissionCategory::class)->create();
        $group->allow('test');

        $this->assertTrue($group->can('test'));

        /** @var \App\Users\User */
        $user = factory(User::class)->create();
        $user->assignGroup($group);

        $this->assertTrue($user->can('test'));
    }

    /**
     * Test auth for a user belonging to a group without a permission
     */
    public function testUserHasGroupWithoutPermission()
    {
        /** @var \App\Permissions\PermissionCategory */
        $group = factory(PermissionCategory::class)->create();

        /** @var \App\Users\User */
        $user = factory(User::class)->create();
        $user->assignGroup($group);

        $this->assertFalse($user->can('test'));
    }

    /**
     * Test auth for a user belonging to a group with a forbidden permission
     */
    public function testUserHasGroupWithForbiddenPermission()
    {
        /** @var \App\Permissions\PermissionCategory */
        $group = factory(PermissionCategory::class)->create();
        $group->forbid('test');

        /** @var \App\Users\User */
        $user = factory(User::class)->create();
        $user->assignGroup($group);

        $this->assertFalse($user->can('test'));
    }

    /**
     * Test auth precedence for role over a group
     */
    public function testRoleOverGroupPrecedence()
    {
        /** @var \App\Permissions\PermissionCategory */
        $group = factory(PermissionCategory::class)->create();
        $group->forbid('test');

        /** @var \App\Roles\Role */
        $role = factory(Role::class)->create();
        $role->allow('test');

        /** @var \App\Users\User */
        $user = factory(User::class)->create();
        $user->assign($role);
        $user->assignGroup($group);

        $this->assertTrue($user->can('test'));
    }

    /**
     * Test auth precedence for user over a group
     */
    public function testUserOverGroupPrecedence()
    {
        /** @var \App\Permissions\PermissionCategory */
        $group = factory(PermissionCategory::class)->create();
        $group->forbid('test');

        /** @var \App\Users\User */
        $user = factory(User::class)->create();
        $user->allow('test');
        $user->assignGroup($group);

        $this->assertTrue($user->can('test'));
    }
}
