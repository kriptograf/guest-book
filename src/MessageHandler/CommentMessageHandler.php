<?php

namespace App\MessageHandler;

use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\Checker\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\WorkflowInterface;

/**
 * Обработчик сообщения CommentMessage
 *
 * @package App\MessageHandler
 * @author  Виталий Москвин <foreach@mail.ru>
 */
class CommentMessageHandler implements MessageHandlerInterface
{
	/** @var SpamChecker  */
	private $spamChecker;

	/** @var EntityManagerInterface  */
	private $entityManager;

	/** @var CommentRepository  */
	private $commentRepository;

	/** @var MessageBusInterface  */
	private $bus;

	/** @var WorkflowInterface  */
	private $workflow;

	private $mailer;

	private $adminEmail;

	/** @var null|LoggerInterface  */
	private $logger;

	/**
	 * CommentMessageHandler constructor.
	 *
	 * @param EntityManagerInterface $entityManager
	 * @param SpamChecker            $spamChecker
	 * @param CommentRepository      $commentRepository
	 * @param MessageBusInterface    $bus
	 * @param WorkflowInterface      $commentStateMachine
	 * @param MailerInterface        $mailer
	 * @param string                 $adminEmail
	 * @param LoggerInterface|null   $logger
	 */
	public function __construct(EntityManagerInterface $entityManager, SpamChecker $spamChecker, CommentRepository $commentRepository, MessageBusInterface $bus, WorkflowInterface $commentStateMachine, MailerInterface $mailer, string $adminEmail, LoggerInterface $logger = null)
	{
		$this->entityManager     = $entityManager;
		$this->spamChecker       = $spamChecker;
		$this->commentRepository = $commentRepository;
		$this->bus               = $bus;
		$this->workflow          = $commentStateMachine;
		$this->mailer            = $mailer;
		$this->adminEmail        = $adminEmail;
		$this->logger            = $logger;
	}

	/**
	 * • Если комментарий может перейти в состояние accept , значит проверяем сообщение на спам;
	 * • В зависимости от результата проверки, нужно выбрать подходящий переход;
	 * • Вызываем метод apply() , чтобы обновить состояние для объекта Comment,
	 * который в свою очередь вызывает в этом объекте метод setState() ;
	 * • Сохраняем данные в базе данных, используя метод flush() ;
	 * • Повторно отправляем сообщение на шину, чтобы ещё раз запустить бизнес-процесс комментария
	 * для определения следующего перехода.
	 *
	 * @param CommentMessage $message
	 *
	 * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
	 * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
	 * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
	 * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function __invoke(CommentMessage $message)
	{
		$comment = $this->commentRepository->find($message->getId());

		if (!$comment) {
			return;
		}

		if ($this->workflow->can($comment, 'accept')) {
			$score = $this->spamChecker->getSpamScore($comment, $message->getContext());
			$transition = 'accept';

			if (2 === $score) {
				$transition = 'reject_spam';
			}
			elseif (1 === $score) {
				$transition = 'might_be_spam';
			}
			$this->workflow->apply($comment, $transition);
			$this->entityManager->flush();
			$this->bus->dispatch($message);
		}
		elseif ($this->workflow->can($comment, 'publish') || $this->workflow->can($comment, 'publish_ham')) {
			$this->mailer->send((new NotificationEmail())->subject('New comment posted')
					->htmlTemplate('emails/comment_notification.html.twig')
					->from($this->adminEmail)
					->to($this->adminEmail)
					->context(['comment' => $comment])
			);
		}
		elseif ($this->logger) {
			$this->logger->debug('Dropping comment message', ['comment' => $comment->getId(), 'state' => $comment->getState()]);
		}
	}
}