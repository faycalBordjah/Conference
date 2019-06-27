<?php

namespace App\Form;

use App\Entity\Conference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConferenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',TextType::class,['label' => "Title"])
            ->add('content',TextareaType::class,['label' => "Content"])
            ->add('date',DateType::class,['format' => 'yyy-MM-dd'])
            ->add('create',SubmitType::class,['label' => "New Conference"])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Conference::class,
        ]);
    }
}
