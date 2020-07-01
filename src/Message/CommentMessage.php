<?php

namespace App\Message;

/**
 * Class CommentMessage
 *
 * @package App\Message
 * @author  Виталий Москвин <foreach@mail.ru>
 */
class CommentMessage
{
	/** @var int  */
	private $id;

	/** @var array  */
	private $context;

	/**
	 * CommentMessage constructor.
	 *
	 * @param int   $id
	 * @param array $context
	 */
	public function __construct(int $id, array $context = [])
	{
		$this->id      = $id;
		$this->context = $context;
	}

	/**
	 * @return int
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @return array
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function getContext(): array
	{
		return $this->context;
	}
}