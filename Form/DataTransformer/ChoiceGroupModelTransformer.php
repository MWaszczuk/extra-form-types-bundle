<?php

namespace MWaszczuk\ExtraFormTypesBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ChoiceGroupModelTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        if (is_null($value)) {
            return null;
        }

        if (!is_int($value)) {
            throw new TransformationFailedException('Value should be integer');
        }

        $arr = [];
        while ($value != 0) {
            $i = intval(pow(2, floor(log($value,2))));
            $arr[] = $i;
            $value -= $i;
        }

        return $arr;
    }

    public function reverseTransform($value)
    {
        if (!is_array($value)) {
            throw new TransformationFailedException('Value should be array.');
        }

        $return = 0;
        foreach ($value as $i) {
            $return += $i;
        }

        return $return;
    }
}