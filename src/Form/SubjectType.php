<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Subject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Subject>
 */
final class SubjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('description', TextType::class, [
            'label' => 'Descrição',
            'attr' => ['maxlength' => 20, 'autofocus' => true, 'placeholder' => 'Ex.: Romance'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Subject::class]);
    }
}
