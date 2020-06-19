<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Comment
{
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $author;

	/**
	 * @ORM\Column(type="text")
	 */
	private $text;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $email;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $createdAt;

	/**
	 * @ORM\ManyToOne(targetEntity=Conference::class, inversedBy="comments")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $conference;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $photo;

	/**
	 * @return string
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function __toString()
	{
		return (string)$this->getEmail();
	}

	/**
	 * @return int|null
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * @return null|string
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function getAuthor(): ?string
	{
		return $this->author;
	}

	/**
	 * @param string $author
	 *
	 * @return Comment
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function setAuthor(string $author): self
	{
		$this->author = $author;

		return $this;
	}

	/**
	 * @return null|string
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function getText(): ?string
	{
		return $this->text;
	}

	/**
	 * @param string $text
	 *
	 * @return Comment
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function setText(string $text): self
	{
		$this->text = $text;

		return $this;
	}

	/**
	 * @return null|string
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function getEmail(): ?string
	{
		return $this->email;
	}

	/**
	 * @param string $email
	 *
	 * @return Comment
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function setEmail(string $email): self
	{
		$this->email = $email;

		return $this;
	}

	/**
	 * @return \DateTimeInterface|null
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function getCreatedAt(): ?\DateTimeInterface
	{
		return $this->createdAt;
	}

	/**
	 * @param \DateTimeInterface $createdAt
	 *
	 * @return Comment
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function setCreatedAt(\DateTimeInterface $createdAt): self
	{
		$this->createdAt = $createdAt;

		return $this;
	}

	/**
	 * Событие срабатывает, когда объект впервые сохранятся в базе данных
	 *
	 * @ORM\PrePersist()
	 *
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function setCreatedAtValue()
	{
		$this->createdAt = new \DateTime();
	}

	/**
	 * @return Conference|null
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function getConference(): ?Conference
	{
		return $this->conference;
	}

	/**
	 * @param Conference|null $conference
	 *
	 * @return Comment
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function setConference(?Conference $conference): self
	{
		$this->conference = $conference;

		return $this;
	}

	/**
	 * @return null|string
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function getPhoto(): ?string
	{
		return $this->photo;
	}

	/**
	 * @param null|string $photo
	 *
	 * @return Comment
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function setPhoto(?string $photo): self
	{
		$this->photo = $photo;

		return $this;
	}
}
