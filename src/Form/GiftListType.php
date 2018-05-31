<?php

namespace App\Form;

use App\Entity\GiftList;
use App\Entity\Gift;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;


class GiftListType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class,
                array(
                    'required' => true,
                    'constraints' => array(new NotBlank(), new Length(['max' => 255]))
                ))
            ->add('email', EmailType::class, array(
                'required' => true,
                'constraints' => array(new Email(), new NotBlank())
            ))
            ->add('title', TextType::class,
                array(
                    'required' => true,
                    'constraints' => array(new Length(array('min' => 3)), new NotBlank(), new Length(['max' => 255]))
                ))
            ->add('description', TextareaType::class,
                array(
                    'required' => true,
                    'constraints' => array(new Length(array('min' => 3)), new NotBlank())
                ))
            ->add('gifts', CollectionType::class, [
                'label' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                // each entry in the array will be a "gift" field
                'entry_type' => GiftType::class,
                'required' => true,
                // manage a collection of similar items in a form
                'entry_options' => array(
                    'attr' => ['class' => 'form-group'],
                ),
                // allows to define specific data for the prototype
                'prototype' => true,
                // describe empty condition
                'delete_empty' => function (Gift $gift = null) {
                    return null === $gift || empty($gift->getTitle());
                },
                'disabled' => !$options['allow_gift_editing'],
                ])
            ->add('isPublic', CheckboxType::class, [
                    'required' => false,
                ])
            ->add('Save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => GiftList::class,
            'allow_gift_editing' => true,
        ));
    }
}