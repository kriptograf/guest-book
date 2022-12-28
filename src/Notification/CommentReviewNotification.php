<?php

namespace App\Notification;

use App\Entity\Comment;
use Symfony\Component\Notifier\Bridge\Slack\Block\SlackActionsBlock;
use Symfony\Component\Notifier\Bridge\Slack\Block\SlackDividerBlock;
use Symfony\Component\Notifier\Bridge\Slack\Block\SlackSectionBlock;
use Symfony\Component\Notifier\Bridge\Slack\SlackOptions;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\ChatNotificationInterface;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\Recipient;

/**
 * Class CommentReviewNotification
 *
 * @package App\Notification
 * @author  Виталий Москвин <foreach@mail.ru>
 */
class CommentReviewNotification extends Notification implements EmailNotificationInterface, ChatNotificationInterface
{
	private $comment;
	private $reviewUrl;

	public function __construct(Comment $comment, string $reviewUrl)
	{
		$this->comment = $comment;
		$this->reviewUrl = $reviewUrl;

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

	/**
	 * @param Recipient $recipient
	 *
	 * @return array
	 *
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function getChannels(Recipient $recipient): array
	{
		if (preg_match('{\b(great|awesome)\b}i', $this->comment->getText())) {
			return ['email', 'chat/slack'];
		}

		$this->importance(Notification::IMPORTANCE_LOW);

		return ['email'];
	}

	/**
	 * @param Recipient   $recipient
	 * @param string|null $transport
	 *
	 * @return null|ChatMessage
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function asChatMessage(Recipient $recipient, string $transport = null): ?ChatMessage
	{
		if ('slack' !== $transport) {
			return null;
		}

		$message = ChatMessage::fromNotification($this, $recipient, $transport);
		$message->subject($this->getSubject());
		$message->options((new SlackOptions())
			->iconEmoji('tada')
			->iconUrl('https://guestbook.example.com')
			->username('Guestbook')
			->block((new SlackSectionBlock())->text($this->getSubject()))
			->block(new SlackDividerBlock())
			->block((new SlackSectionBlock())
				->text(sprintf('%s (%s) says: %s', $this->comment->getAuthor(), $this->comment->getEmail(), $this->comment->getText()))
			)
			->block((new SlackActionsBlock())
				->button('Accept', $this->reviewUrl, 'primary')
				->button('Reject', $this->reviewUrl.'?reject=1', 'danger')
			)
		);

		return $message;
	}
}