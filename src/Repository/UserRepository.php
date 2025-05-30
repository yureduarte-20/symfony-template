<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
    
    public function findByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.email = :val' )
            ->setParameter('val', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }
public function findUsersWithSpecificToken(string $tokenValue)
    {
        $qb = $this->createQueryBuilder('u');

        $subQb = $this->getEntityManager()->createQueryBuilder();
        $subQb->select('1') 
              ->from(UserToken::class, 'ut')
              ->where('ut.user = u.id') 
              ->andWhere('ut.token = :tokenValue')
              ;

        $qb->andWhere($qb->expr()->exists($subQb->getDQL()))
           ->setParameter('tokenValue', $tokenValue);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function save(User $user)
    {
         $this->getEntityManager()->persist($user);
         $this->getEntityManager()->flush();
    }
}
