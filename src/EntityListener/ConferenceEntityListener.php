<?php

namespace App\EntityListener;

use App\Entity\Conference;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Обработчик сущности Doctrine
 *
 * @package App\EntityListener
 * @author  Виталий Москвин <foreach@mail.ru>
 */
class ConferenceEntityListener
{
	/** @var SluggerInterface  */
	private $slugger;

	/**
	 * ConferenceEntityListener constructor.
	 *
	 * @param SluggerInterface $slugger
	 */
	public function __construct(SluggerInterface $slugger)
	{
		$this->slugger = $slugger;
	}

	/**
	 * @param Conference         $conference
	 * @param LifecycleEventArgs $event
	 *
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function prePersist(Conference $conference, LifecycleEventArgs $event)
	{
		$conference->computeSlug($this->slugger);
	}

	/**
	 * @param Conference         $conference
	 * @param LifecycleEventArgs $event
	 *
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function preUpdate(Conference $conference, LifecycleEventArgs $event)
	{
		$conference->computeSlug($this->slugger);
	}
}