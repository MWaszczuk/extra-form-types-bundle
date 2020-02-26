<?php

namespace MWaszczuk\ExtraFormTypesBundle\Form\DataTransformer;

use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ExtraEntityViewTransformer implements DataTransformerInterface
{
    /**
     * @var ChoiceListInterface
     */
    private $choiceList;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $choiceLabel;

    /**
     * @var bool
     */
    private $multiple;

    public function __construct(ChoiceListInterface $choiceList, $class, $choiceLabel, $multiple)
    {
        $this->choiceList = $choiceList;
        $this->class = $class;
        $this->choiceLabel = $choiceLabel;
        $this->multiple = $multiple;
    }

    public function transform($value)
    {
        if (!$this->multiple) {
            return (string) current($this->choiceList->getValuesForChoices([$value]));
        }

        if (null === $value) {
            return [];
        }

        if (!\is_array($value)) {
            throw new TransformationFailedException('Expected an array.');
        }

        $newItems = [];
        foreach ($value as $key => $item) {
            if (is_a($item, $this->class)) {
                if (!$item->getId()) {
                    $newItems[] = $item;
                    unset($value[$key]);
                }
            }
        }

        $values = $this->choiceList->getValuesForChoices($value);

        return array_merge($values, $newItems);
    }

    public function reverseTransform($value)
    {
        if ($this->multiple) {
            return $this->reverseTransformForMultipleSelection($value);
        }

        return $this->reverseTransformForSingleSelection($value);
    }

    private function reverseTransformForSingleSelection($value)
    {
        if (null !== $value && !\is_string($value)) {
            throw new TransformationFailedException('Expected a string or null.');
        }

        $choices = $this->choiceList->getChoicesForValues([(string) $value]);

        if (1 !== \count($choices)) {
            if (null === $value || '' === $value) {
                return null;
            }

            if (is_string($value)) {
                return $value;
            }

            throw new TransformationFailedException(sprintf('The choice "%s" does not exist or is not unique', $value));
        }

        return current($choices);
    }

    private function reverseTransformForMultipleSelection($value)
    {
        if ('' === $value || null === $value) {
            $value = [];
        }

        $newItems = [];
        foreach ($value as $key => $item) {
            if (!is_numeric($item) && is_string($item)) {
                $entity = new $this->class();
                $setter = 'set'.ucfirst($this->choiceLabel);

                $entity->$setter($item);

                $newItems[] = $entity;
                unset($value[$key]);
            }
        }

        $choices = $this->choiceList->getChoicesForValues($value);

        if (\count($choices) !== \count($value)) {
            throw new TransformationFailedException('Could not find all matching choices for the given values');
        }

        return array_merge($choices, $newItems);
    }
}