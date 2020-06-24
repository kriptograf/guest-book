<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

/**
 * Тип формы задаёт поля формы, связанные с моделью. Он выполняет
 * преобразование между отправленными данными и свойствами класса
 * модели. По умолчанию для определения конфигурации каждого поля,
 * Symfony использует метаданные (например, метаданные Doctrine)
 * сущности Comment . К примеру, поле text будет отрисовано как textarea ,
 * так как в базе данных используется столбец для хранения текста, а не строки.
 *
 * @package App\Form
 * @author  Виталий Москвин <foreach@mail.ru>
 */
class CommentFormType extends AbstractType
{
	/**
	 * @param FormBuilderInterface $builder
	 * @param array                $options
	 *
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('author', null, [
				'label' => 'Your name'
			])
			->add('text')
			->add('email', EmailType::class)
			->add('photo', FileType::class, [
				'required' => false,
				'mapped' => false,
				'constraints' => [
					new Image(['maxSize' => '1024k'])
				]
			])
			->add('submit', SubmitType::class)
		;
	}

	/**
	 * @param OptionsResolver $resolver
	 *
	 * @author Виталий Москвин <foreach@mail.ru>
	 */
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => Comment::class,
		]);
	}
}
