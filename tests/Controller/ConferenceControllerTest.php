<?php

namespace App\Tests\Controller;

use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Функциональный тест для контроллера конференций
 *
 * @author  Виталий Москвин <foreach@mail.ru>
 */
class ConferenceControllerTest extends WebTestCase
{
	/**
	 * Проверяет, что главная страница возвращает статус 200 в HTTP-ответе.
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function testIndex()
	{
		$client = static::createClient();
		$client->request('GET', '/');

		$this->assertResponseIsSuccessful();
		$this->assertSelectorTextContains('h2', 'Give your feedback');
	}

	/**
	 * - Сначала переходим на главную страницу;
	 * - Метод request() возвращает экземпляр Crawler , с помощью
	 * которого можно найти нужные элементы на странице (например,
	 * ссылки, формы и всё остальное, что можно получить через селекторы CSS или XPath);
	 * - Благодаря CSS-селектору проверяем, что на главной странице есть
	 * две конференции;
	 * - Далее кликаем по ссылке «View» (из-за того, что один вызов
	 * щёлкает только по одной ссылке, Symfony автоматически выберет
	 * первую найденную в разметке);
	 * - Проверяем заголовок страницы, ответ и тег <h2> для того, чтобы
	 * знать наверняка, что мы находимся на нужной странице
	 * (дополнительно можно было проверить на совпадение маршрут);
	 * - И последнее: проверяем, что на странице есть 1 комментарий. В Symfony кое-какие
	 * некорректные в CSS селекторы позаимствованы из jQuery.
	 * Как раз один из таких селекторов мыиспользуем — div:contains() .
	 *
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function testConferencePage()
	{
		$client = static::createClient();
		$crawler = $client->request('GET', '/');

		$this->assertCount(2, $crawler->filter('h4'));
		$client->clickLink('View');

		$this->assertPageTitleContains('Amsterdam');
		$this->assertResponseIsSuccessful();
		$this->assertSelectorTextContains('h2', 'Amsterdam 2019');
		$this->assertSelectorExists('div:contains("There are 1 comments")');
	}

	/**
	 * Отправка формы с комментарием
	 *
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function testCommentSubmission()
	{
		$client = static::createClient();
		$client->request('GET', '/conference/amsterdam-2019');

		$client->submitForm('Submit', [
				'comment_form[author]' => 'Fabien',
				'comment_form[text]' => 'Some feedback from an automated functional test',
				'comment_form[email]' => $email = 'me@automat.ed',
			]);

		$this->assertResponseRedirects();

		// Симулируем валидацию комментария
		$comment = self::$container->get(CommentRepository::class)->findOneByEmail($email);
		$comment->setStatus('published');
		self::$container->get(EntityManagerInterface::class)->flush();

		$client->followRedirect();
		$this->assertSelectorExists('div:contains("There are 2 comments")');
	}
}