<?php

namespace App\EventSubscriber;

use App\Repository\ConferenceRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;

/**
 * Подписчик на события контроллера
 *
 * @package App\EventSubscriber
 * @author  Виталий Москвин <foreach@mail.ru>
 */
class TwigEventSubscriber implements EventSubscriberInterface
{
	/** @var Environment */
	private $twig;

	/** @var ConferenceRepository */
	private $conferenceRepository;

	/**
	 * TwigEventSubscriber constructor.
	 *
	 * @param Environment          $twig
	 * @param ConferenceRepository $conferenceRepository
	 */
	public function __construct(Environment $twig, ConferenceRepository $conferenceRepository)
	{
		$this->twig                = $twig;
		$this->conferenceRepository = $conferenceRepository;
	}

	/**
	 * Определяем глобальную переменную conferences ,
	 * чтобы Twig имел к ней доступ во время отрисовки шаблона контроллером
	 *
	 * @param ControllerEvent $event
	 *
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function onKernelController(ControllerEvent $event)
	{
		$this->twig->addGlobal('conferences', $this->conferenceRepository->findAll());
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return array
	 *
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'kernel.controller' => 'onKernelController',
		];
	}
}
