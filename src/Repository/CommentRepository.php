<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Conference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
	/** Количество комментариев на странице */
	public const PAGINATOR_PER_PAGE = 2;

	/**
	 * CommentRepository constructor.
	 *
	 * @param ManagerRegistry $registry
	 */
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Comment::class);
	}

	/**
	 * Получить опубликованные комментарии с постраничной разбивкой
	 *
	 * @param Conference $conference
	 * @param int        $offset
	 *
	 * @return Paginator
	 *
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function getCommentPaginator(Conference $conference, int $offset)
	{
		$query = $this->createQueryBuilder('c')
			->andWhere('c.conference = :conference')
			->andWhere('c.status = :status')
			->setParameter('conference', $conference)
			->setParameter('status', 'published')
			->orderBy('c.createdAt')
			->setMaxResults(static::PAGINATOR_PER_PAGE)
			->setFirstResult($offset)
			->getQuery()
		;

		return new Paginator($query);
	}

	/**
	 * Найти комментарий по email
	 *
	 * @param $value Email
	 *
	 * @return Comment|null
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	/*public function findOneByEmail($value): ?Comment
	{
		return $this->createQueryBuilder('c')
			->andWhere('c.email = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
			;
	}*/

	// /**
	//  * @return Comment[] Returns an array of Comment objects
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
	public function findOneBySomeField($value): ?Comment
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
