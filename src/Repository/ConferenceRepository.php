<?php

namespace App\Repository;

use App\Entity\Conference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Conference|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conference|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConferenceRepository extends ServiceEntityRepository
{
	/**
	 * {@inheritdoc}
	 *
	 * @param ManagerRegistry $registry
	 */
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Conference::class);
	}

	/**
	 * Переопределим стандартную реализацию метода findAll() ,
	 * чтобы данная сортировка применялась всегда по умолчанию
	 *
	 * @return Conference[]|array
	 *
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function findAll()
	{
		return $this->findBy([], [
			'year' => 'ASC',
			'city' => 'ASC'
		]);
	}

	// /**
	//  * @return Conference[] Returns an array of Conference objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('c')
			->andWhere('c.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('c.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/

	/*
	public function findOneBySomeField($value): ?Conference
	{
		return $this->createQueryBuilder('c')
			->andWhere('c.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
