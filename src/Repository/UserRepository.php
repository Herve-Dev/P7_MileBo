<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }

    /**
     * Requête custom sur objet JSON
     * (Problèmes de retour avec findBy)
     *
     * @return array
     */
    public function findByRoleCustomers(): array
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $expr = $queryBuilder->expr();

        $queryBuilder->where(
            $expr->like('u.roles', $expr->literal('%ROLE_CUSTOMERS%'))
        );

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    /**
     * Méthode pour trouver un Client via son id
     * (Besoin d'une requete custom car role JSON)
     *
     * @param integer $id
     * @return User|null
     */
    public function findCustomerById(int $id): ?User
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $expr = $queryBuilder->expr();

        $queryBuilder
            ->where(
                $expr->andX(
                    $expr->like('u.roles', $expr->literal('%ROLE_CUSTOMERS%')),
                    $expr->eq('u.id', ':id')
                )
            )
            ->setParameter('id', $id)
            ->setMaxResults(1);

        $query = $queryBuilder->getQuery();

        return $query->getOneOrNullResult();
    }

    public function findUsersByRoleAndParentId(int $parentId): array
    {
        $queryBuilder = $this->createQueryBuilder('u');
        $expr = $queryBuilder->expr();

        $queryBuilder
            ->select('u', 'p') // Sélectionne l'utilisateur et l'entité parent
            ->leftJoin('u.parent', 'p') // Effectue une jointure sur la relation parent
            ->where(
                $expr->andX(
                    $expr->eq('u.parent', ':parentId'),
                    $expr->like('u.roles', $expr->literal('%ROLE_USER%'))
                )
            )
            ->setParameter('parentId', $parentId);

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
