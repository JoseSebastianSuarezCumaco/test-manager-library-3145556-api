<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\AdminRoleNotAllowed;
use App\Domain\ValueObject\Role;

final class User
{
    private function __construct(
        private readonly string $id,
        private readonly string $username,
        private readonly string $passwordHash,
        private readonly Role $role,
    ) {
    }

    /**
     * User created through the API: the role is validated and "admin" is
     * rejected (CA-02.3). The password arrives already hashed.
     */
    public static function createByApi(
        string $id,
        string $username,
        string $passwordHash,
        string $requestedRole,
    ): self {
        $role = Role::parse($requestedRole);

        if ($role === Role::Admin) {
            throw AdminRoleNotAllowed::viaApi();
        }

        return new self($id, self::normalize($username), $passwordHash, $role);
    }

    /** The only way to get an admin: provisioned at startup from the environment (T-17). */
    public static function provisionAdmin(string $id, string $username, string $passwordHash): self
    {
        return new self($id, self::normalize($username), $passwordHash, Role::Admin);
    }

    public static function reconstitute(
        string $id,
        string $username,
        string $passwordHash,
        Role $role,
    ): self {
        return new self($id, $username, $passwordHash, $role);
    }

    /** Usernames are stored trimmed and lowercase (CA-02.2). */
    public static function normalize(string $username): string
    {
        return mb_strtolower(trim($username));
    }

    public function id(): string
    {
        return $this->id;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function role(): Role
    {
        return $this->role;
    }
}