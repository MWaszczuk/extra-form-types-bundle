<?php

namespace MWaszczuk\ExtraFormTypesBundle\Form\Type;

use MWaszczuk\ExtraFormTypesBundle\Form\DataTransformer\ChoiceGroupModelTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $this->validateChoices($options['choices']);

        $builder->addModelTransformer(new ChoiceGroupModelTransformer());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'multiple' => true,
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix()
    {
        return 'mw_checkbox_group_type';
    }

    private function validateChoices(array $choices)
    {
        foreach ($choices as $choice) {
            if (($choice & ($choice - 1)) != 0 || $choice <= 0 || !is_int($choice)) {
                throw new \RuntimeException(sprintf('The "choices" option of %s contains invalidate data. Only numbers which are power of 2 and higher than 0 can be used.', \get_class($this)));
            }
        }
    }
}