<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Контроллер конференций
 *
 * @package App\Controller
 * @author  Виталий Москвин <foreach@mail.ru>
 */
class ConferenceController extends AbstractController
{
	private $entityManager;

	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
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
	public function show(Request $request, Conference $conference, CommentRepository $commentRepository, string $photoDir)
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

			return $this->redirectToRoute('conference', ['slug' => $conference->getSlug()]);
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
