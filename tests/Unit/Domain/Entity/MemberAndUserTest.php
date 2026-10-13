<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Entity;

use App\Domain\Entity\Member;
use App\Domain\Entity\User;
use App\Domain\Exception\AdminRoleNotAllowed;
use App\Domain\Exception\InvalidRole;
use App\Domain\ValueObject\Role;
use PHPUnit\Framework\TestCase;

final class MemberAndUserTest extends TestCase
{
    private const ID = 'dddddddd-dddd-4ddd-8ddd-dddddddddddd';

    public function test_username_is_trimmed_and_lowercased(): void
    {
        $user = User::createByApi(self::ID, 'Ana ', 'hash', 'librarian');

        $this->assertSame('ana', $user->username());
    }

    public function test_username_with_uppercase_letters_is_lowercased(): void
    {
        $user = User::createByApi(self::ID, '  MaRiA  ', 'hash', 'librarian');

        $this->assertSame('maria', $user->username());
    }

    public function test_user_created_by_api_gets_the_librarian_role(): void
    {
        $user = User::createByApi(self::ID, 'ana', 'hash', 'librarian');

        $this->assertSame(Role::Librarian, $user->role());
        $this->assertSame('hash', $user->passwordHash());
    }

    public function test_admin_role_requested_by_api_is_rejected(): void
    {
        $this->expectException(AdminRoleNotAllowed::class);

        User::createByApi(self::ID, 'ana', 'hash', 'admin');
    }

    public function test_unknown_role_is_rejected(): void
    {
        $this->expectException(InvalidRole::class);

        User::createByApi(self::ID, 'ana', 'hash', 'superuser');
    }

    public function test_provisioned_admin_has_the_admin_role(): void
    {
        $user = User::provisionAdmin(self::ID, ' Admin ', 'hash');

        $this->assertSame(Role::Admin, $user->role());
        $this->assertSame('admin', $user->username());
    }

    public function test_member_keeps_name_and_document(): void
    {
        $member = Member::register(self::ID, 'Ana Gómez', '1020304050');

        $this->assertSame('Ana Gómez', $member->name());
        $this->assertSame('1020304050', $member->document());
    }
}