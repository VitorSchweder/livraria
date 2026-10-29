<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Subject;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Book>
 */
final class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Título',
                'attr' => ['maxlength' => 40, 'autofocus' => true],
            ])
            ->add('publisher', TextType::class, [
                'label' => 'Editora',
                'attr' => ['maxlength' => 40],
            ])
            ->add('edition', IntegerType::class, [
                'label' => 'Edição',
                'attr' => ['min' => 1, 'max' => 999, 'placeholder' => 'Ex.: 1'],
            ])
            ->add('publicationYear', TextType::class, [
                'label' => 'Ano de publicação',
                'attr' => [
                    'maxlength' => 4,
                    'inputmode' => 'numeric',
                    'pattern' => '\d{4}',
                    'placeholder' => 'AAAA',
                    'data-mask' => 'year',
                ],
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Valor',
                'currency' => 'BRL',
                'input' => 'string',
                'scale' => 2,
                'grouping' => true,
                'html5' => false,
                'invalid_message' => 'Informe um valor válido (ex.: 59,90).',
                'attr' => ['inputmode' => 'decimal', 'placeholder' => '0,00', 'data-mask' => 'currency'],
            ])
            ->add('authors', EntityType::class, [
                'label' => 'Autores',
                'class' => Author::class,
                'choice_label' => 'name',
                'multiple' => true,
                'query_builder' => static fn (EntityRepository $r): QueryBuilder => $r->createQueryBuilder('a')->orderBy('a.name', 'ASC'),
                'attr' => ['data-multi-select' => true, 'data-placeholder' => 'Selecione um ou mais autores'],
                'help' => 'Um livro pode ter mais de um autor.',
            ])
            ->add('subjects', EntityType::class, [
                'label' => 'Assuntos',
                'class' => Subject::class,
                'choice_label' => 'description',
                'multiple' => true,
                'query_builder' => static fn (EntityRepository $r): QueryBuilder => $r->createQueryBuilder('s')->orderBy('s.description', 'ASC'),
                'attr' => ['data-multi-select' => true, 'data-placeholder' => 'Selecione um ou mais assuntos'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Book::class]);
    }
}
