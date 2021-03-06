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

namespace Silverback\ApiComponentBundle\Repository\User;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Silverback\ApiComponentBundle\Entity\User\AbstractUser;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * @method AbstractUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbstractUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbstractUser[]    findAll()
 * @method AbstractUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    private int $passwordRequestTimeout;

    public function __construct(ManagerRegistry $registry, int $passwordRequestTimeout, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
        $this->passwordRequestTimeout = $passwordRequestTimeout;
    }

    public function findOneByEmail($value): ?AbstractUser
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByPasswordResetToken(string $username, string $token): ?AbstractUser
    {
        $minimumRequestDateTime = new \DateTime();
        $minimumRequestDateTime->modify(sprintf('-%d seconds', $this->passwordRequestTimeout));

        return $this->createQueryBuilder('u')
            ->andWhere('u.username = :username')
            ->andWhere('u.passwordResetConfirmationToken = :token')
            ->andWhere('u.passwordResetConfirmationToken NOT NULL')
            ->andWhere('u.passwordRequestedAt > :passwordRequestedAt')
            ->setParameter('username', $username)
            ->setParameter('token', $token)
            ->setParameter('passwordRequestedAt', $minimumRequestDateTime)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByEmailVerificationToken(string $username, string $email, string $token): ?AbstractUser
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.username = :username')
            ->andWhere('u.newEmail = :email')
            ->andWhere('u.newEmailVerificationToken = :token')
            ->andWhere('u.newEmailVerificationToken NOT NULL')
            ->setParameter('username', $username)
            ->setParameter('email', $email)
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function loadUserByUsername($usernameOrEmail): ?AbstractUser
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.username = :username')
            ->orWhere('u.emailAddress = :username')
            ->setParameter('username', $usernameOrEmail)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
