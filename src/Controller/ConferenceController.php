<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
	 * @Route("/conference/{id}", name="conference")
	 *
	 * @param Request           $request
	 * @param Conference        $conference
	 * @param CommentRepository $commentRepository
	 *
	 * @return Response
	 *
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function show(Request $request, Conference $conference, CommentRepository $commentRepository)
	{
		$offset    = max(0, $request->query->getInt('offset', 0));
		$paginator = $commentRepository->getCommentPaginator($conference, $offset);

		return $this->render('conference/show.html.twig', [
			'conference' => $conference,
			'comments'   => $paginator,
			'previous'   => $offset - CommentRepository::PAGINATOR_PER_PAGE,
			'next'       => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
		]);
	}
}
