<?php

namespace App\Notification;

use App\Entity\Comment;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\Recipient;

/**
 * Class CommentReviewNotification
 *
 * @package App\Notification
 * @author  Виталий Москвин <foreach@mail.ru>
 */
class CommentReviewNotification extends Notification implements EmailNotificationInterface
{
	private $comment;

	public function __construct(Comment $comment)
	{
		$this->comment = $comment;

		parent::__construct('New comment posted');
	}

	/**
	 * Необязательный метод EmailMessage() интерфейса EmailNotificationInterfaceas
	 * позволяет изменить сообщение электронной почты.
	 *
	 * @param Recipient   $recipient
	 * @param string|null $transport
	 *
	 * @return null|EmailMessage
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function asEmailMessage(Recipient $recipient, string $transport = null): ?EmailMessage
	{
		$message = EmailMessage::fromNotification($this, $recipient, $transport);
		$message->getMessage()
			->htmlTemplate('emails/comment_notification.html.twig')
			->context(['comment' => $this->comment])
		;

		return $message;
	}
}