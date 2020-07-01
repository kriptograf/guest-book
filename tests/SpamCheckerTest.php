<?php

namespace App\Tests;

use App\Entity\Comment;
use App\Checker\SpamChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Class SpamCheckerTest
 *
 * @package App\Tests
 * @author  Виталий Москвин <foreach@mail.ru>
 */
class SpamCheckerTest extends TestCase
{
	/**
	 * Тест для случая, когда API возвращает ошибку
	 * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
	 * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
	 * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
	 * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
	 *
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function testSpamScoreWithInvalidRequest()
	{
		$comment = new Comment();
		$comment->setCreatedAtValue();
		$context = [];

		// С помощью класса MockHttpClient можно создать фиктивную
		//реализацию любого HTTP-сервера. Для этого классу нужно передать
		//массив с экземплярами MockResponse , каждый из которых содержит
		//ожидаемые тело и заголовки ответа.
		$client = new MockHttpClient([
			new MockResponse('invalid', [
				'response_headers' => ['x-akismet-debug-help: Invalid key']
			])
		]);

		$checker = new SpamChecker($client, 'abcde');

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Unable to check for spam: invalid (Invalid key).');
		//проверяем, что было выброшено исключение, используя встроенный в PHPUnit метод
		//expectException()
		$checker->getSpamScore($comment, $context);
	}

	/**
	 * Позитивный сценарий
	 *
	 * @dataProvider getComments
	 *
	 * @param int               $expectedScore
	 * @param ResponseInterface $response
	 * @param Comment           $comment
	 * @param array             $context
	 *
	 * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
	 * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
	 * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
	 * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
	 *
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function testSpamScore(int $expectedScore, ResponseInterface $response, Comment $comment, array $context)
	{
		$client  = new MockHttpClient([$response]);
		$checker = new SpamChecker($client, 'abcde');

		$score = $checker->getSpamScore($comment, $context);
		$this->assertSame($expectedScore, $score);
	}

	/**
	 * Вернем провайдер для testSpamScore
	 *
	 * @return \Generator
	 *
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function getComments(): iterable
	{
		$comment = new Comment();
		$comment->setCreatedAtValue();
		$context = [];

		$response = new MockResponse('', ['response_headers' => ['x-akismet-pro-tip: discard']]);
		yield 'blatant_spam' => [2, $response, $comment, $context];

		$response = new MockResponse('true');
		yield 'spam' => [1, $response, $comment, $context];

		$response = new MockResponse('false');
		yield 'ham' => [0, $response, $comment, $context];
	}
}
