<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentFormType;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Контроллер конференций
 *
 * @package App\Controller
 * @author  Виталий Москвин <foreach@mail.ru>
 */
class ConferenceController extends AbstractController
{
	/** @var EntityManagerInterface  */
	private $entityManager;

	/** @var MessageBusInterface  */
	private $bus;

	public function __construct(EntityManagerInterface $entityManager, MessageBusInterface $bus)
	{
		$this->entityManager = $entityManager;
		$this->bus           = $bus;
	}

	/**
	 * Главная страница
	 *
	 * @Route("/", name="homepage")
	 *
	 * @param ConferenceRepository $conferenceRepository
	 *
	 * @return Response
	 *
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function index(ConferenceRepository $conferenceRepository): Response
	{
		mail('foreach@mail.ru', 'test', 'test message');
		return $this->render('conference/index.html.twig', [
			'conferences' => $conferenceRepository->findAll(),
		]);
	}

	/**
	 * Просмотр конференции
	 *
	 * @Route("/conference/{slug}", name="conference")
	 *
	 * @param Request           $request
	 * @param Conference        $conference
	 * @param CommentRepository $commentRepository
	 * @param string            $photoDir
	 *
	 * @return Response
	 *
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function show(Request $request, Conference $conference, CommentRepository $commentRepository, NotifierInterface $notifier, string $photoDir)
	{
		$comment = new Comment();
		//Никогда не следует инициализировать класс формы напрямую. Для
		//упрощения создания форм, используйте метод createForm() класса
		//AbstractController .
		$form = $this->createForm(CommentFormType::class, $comment);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$comment->setConference($conference);
			if ($photo = $form['photo']->getData()) {
				$filename = bin2hex(random_bytes(6)).'.'.$photo->guessExtension();

				try{
					$photo->move($photoDir, $filename);
				}catch(FileException $e){
					die($e->getMessage());
				}

				$comment->setPhoto($filename);
			}

			$this->entityManager->persist($comment);
			$this->entityManager->flush();

			// -- Проверка спам-чекром
			$context = [
				'user_ip' => $request->getClientIp(),
				'user_agent' => $request->headers->get('user-agent'),
				'referrer' => $request->headers->get('referer'),
				'permalink' => $request->getUri(),
			];
			// отправляем сообщение на шину
			$this->bus->dispatch(new CommentMessage($comment->getId(), $context));

			// -- отправляем уведомление получателям по каналу
			$notifier->send(new Notification('Thank you for the feedback; your comment will be posted after moderation.', ['browser']));

			return $this->redirectToRoute('conference', ['slug' => $conference->getSlug()]);
		}

		// -- Если форма отправлена, показать уведомление в браузере
		if ($form->isSubmitted()) {
			$notifier->send(new Notification('Can you check your submission? There are some problems with it.', ['browser']));
		}

		$offset    = max(0, $request->query->getInt('offset', 0));
		$paginator = $commentRepository->getCommentPaginator($conference, $offset);

		return $this->render('conference/show.html.twig', [
			'conference'   => $conference,
			'comments'     => $paginator,
			'previous'     => $offset - CommentRepository::PAGINATOR_PER_PAGE,
			'next'         => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
			'comment_form' => $form->createView(),
		]);
	}
}
