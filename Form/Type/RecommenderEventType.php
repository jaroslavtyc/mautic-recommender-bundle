<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecommenderBundle\Form\Type;

use MauticPlugin\MauticRecommenderBundle\Enum\EventTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class RecommenderEventType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            TextType::class,
            [
                'label'       => 'mautic.plugin.recommender.form.event.name',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => [
                    'class'   => 'form-control',
                ],
                'required'    => true,
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'mautic.core.value.required',
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'weight',
            NumberType::class,
            [
                'label'       => 'mautic.plugin.recommender.form.event.weight',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => [
                    'class'   => 'form-control',
                ],
                'required'    => true,
                'constraints' => [
                    new NotBlank(
                        [
                            'message' => 'mautic.core.value.required',
                        ]
                    ),
                    new Range(
                        [
                            'min' => 0,
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'type',
            ChoiceType::class,
            [
                'choices'     => EventTypeEnum::getChoices(),
                'expanded'    => false,
                'multiple'    => false,
                'label'       => 'mautic.recommender.form.event.type',
                'label_attr'  => ['class' => ''],
                'empty_value' => '',
                'required'    => true,
            ]
        );

        $builder->add(
            'buttons',
            'form_buttons'
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'recommender_event';
    }
}
