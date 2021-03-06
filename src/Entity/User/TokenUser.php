<?php

/*
 * This file is part of the Silverback API Component Bundle Project
 *
 * (c) Daniel West <daniel@silverback.is>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Silverback\ApiComponentBundle\Entity\User;

use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

class TokenUser implements SymfonyUserInterface
{
    public function getRoles(): array
    {
        return ['ROLE_TOKEN_USER'];
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUsername(): string
    {
        return 'token_user';
    }

    public function eraseCredentials(): void
    {
    }
}
