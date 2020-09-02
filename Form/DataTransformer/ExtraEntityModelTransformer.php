<?php

namespace MWaszczuk\ExtraFormTypesBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ExtraEntityModelTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $choiceLabel;

    public function __construct($class, $choiceLabel)
    {
        $this->class = $class;
        $this->choiceLabel = $choiceLabel;
    }

    public function transform($value)
    {
        return $value;
    }

    public function reverseTransform($value)
    {
        if (is_null($value)) {
            return null;
        }

        if (!is_a($value, $this->class) && is_string($value)) {
            $entity = new $this->class();
            $setter = 'set'.ucfirst($this->choiceLabel);

            $entity->$setter($value);

            return $entity;
        } elseif (is_a($value, $this->class)) {
            return $value;
        }

        throw new TransformationFailedException('Expected a string or class instance of '.$this->class);
    }
}