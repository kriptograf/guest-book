<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Message\CommentMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Workflow\Registry;

/**
 * Class AdminController
 *
 * @package App\Controller
 * @author  Виталий Москвин <foreach@mail.ru>
 */
class AdminController extends AbstractController
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
	 * @Route("/admin/comment/review/{id}", name="review_comment")
	 *
	 * @param Request  $request
	 * @param Comment  $comment
	 * @param Registry $registry
	 *
	 * @return Response
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function reviewComment(Request $request, Comment $comment, Registry $registry)
	{
		$accepted = !$request->query->get('reject');

		$machine = $registry->get($comment);

		if ($machine->can($comment, 'publish')) {
			$transition = $accepted ? 'publish' : 'reject';
		}
		elseif ($machine->can($comment, 'publish_ham')) {
			$transition = $accepted ? 'publish_ham' : 'reject_ham';
		}
		else {
			return new Response('Comment already reviewed or not in the right state.');
		}

		$machine->apply($comment, $transition);

		$this->entityManager->flush();

		if ($accepted) {
			$reviewUrl = $this->generateUrl('review_comment', ['id' =>$comment->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
			$this->bus->dispatch(new CommentMessage($comment->getId(), $reviewUrl));
		}

		return $this->render('admin/review.html.twig', [
			'transition' => $transition,
			'comment' => $comment,
		]);
	}
}
