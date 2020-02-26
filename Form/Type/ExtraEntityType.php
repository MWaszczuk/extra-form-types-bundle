<?php

namespace MWaszczuk\ExtraFormTypesBundle\Form\Type;

use MWaszczuk\ExtraFormTypesBundle\Form\DataTransformer\ExtraEntityModelTransformer;
use MWaszczuk\ExtraFormTypesBundle\Form\DataTransformer\ExtraEntityViewTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\ChoiceList\Factory\CachingFactoryDecorator;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\ChoiceList\Factory\DefaultChoiceListFactory;
use Symfony\Component\Form\ChoiceList\Factory\PropertyAccessDecorator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExtraEntityType extends EntityType
{
    private $choiceListFactory;

    public function __construct($registry, ChoiceListFactoryInterface $choiceListFactory = null)
    {
        $this->choiceListFactory = $choiceListFactory ?: new CachingFactoryDecorator(
            new PropertyAccessDecorator(
                new DefaultChoiceListFactory()
            )
        );

        parent::__construct($registry);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired([
            'class',
            'choice_label',
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choiceList = $this->createChoiceList($options);
        $builder->setAttribute('choice_list', $choiceList);

        $builder->resetViewTransformers();
        $builder->addViewTransformer(new ExtraEntityViewTransformer(
            $choiceList,
            $options['class'],
            $options['choice_label'],
            $options['multiple']
        ));

        if (!$options['multiple']) {
            $builder->addModelTransformer(new ExtraEntityModelTransformer(
                $options['class'],
                $options['choice_label']
            ));
        }

        parent::buildForm($builder, $options);
    }

    public function getBlockPrefix()
    {
        return 'mw_extra_entity_type';
    }

    private function createChoiceList(array $options)
    {
        return $this->choiceListFactory->createListFromLoader(
            $options['choice_loader'],
            $options['choice_value']
        );
    }
}