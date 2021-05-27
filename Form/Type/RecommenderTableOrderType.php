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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class RecommenderTableOrderType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * RecommenderTableOrderType constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$options['fields']['weight']
        // Build a list of columns

        $column = $options['data']['column'] ?? null;

        $fields = $options['fields'];
        unset($fields['mautic.lead.recommender_item'], $fields['mautic.lead.recommender_item_property_value']);
        $builder->add('column', 'choice', [
            'choices'     => $fields,
            'expanded'    => false,
            'multiple'    => false,
            'label'       => 'mautic.report.report.label.filtercolumn',
            'label_attr'  => ['class' => 'control-label'],
            'empty_value' => false,
            'required'    => false,
            'attr'        => [
                'class' => 'form-control filter-columns',
            ],
        ]);

        // Direction
        $builder->add('direction', 'choice', [
            'choices' => [
                'DESC' => $this->translator->trans('mautic.report.report.label.tableorder_dir.desc'),
                'ASC'  => $this->translator->trans('mautic.report.report.label.tableorder_dir.asc'),
            ],
            'expanded'    => false,
            'multiple'    => false,
            'label'       => 'mautic.core.order',
            'label_attr'  => ['class' => 'control-label'],
            'empty_value' => false,
            'required'    => false,
            'attr'        => [
                'class' => 'form-control not-chosen',
            ],
        ]);

        $builder->add('function', 'choice', [
            'choices'     => $this->getAvabilableFunctionChoices($column ?? null),
            'expanded'    => false,
            'multiple'    => false,
            'label'       => 'mautic.report.function',
            'label_attr'  => ['class' => 'control-label'],
            'empty_value' => false,
            'required'    => true,
            'attr'        => [
                'class' => 'form-control not-chosen',
            ],
        ]);

        $builder->get('column')->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                $column = $event->getData();

                $this->setupAvailableFunctionChoices(
                        $form->getParent(),
                        $column
                    );
            }
            );
    }

    public function getAvabilableFunctionChoices($column=null)
    {
        $choices = [
            ''       => $this->translator->trans('mautic.core.none'),
            'COUNT'  => $this->translator->trans('mautic.report.report.label.aggregators.count'),
            'AVG'    => $this->translator->trans('mautic.report.report.label.aggregators.avg'),
            'SUM'    => $this->translator->trans('mautic.report.report.label.aggregators.sum'),
            'MIN'    => $this->translator->trans('mautic.report.report.label.aggregators.min'),
            'MAX'    => $this->translator->trans('mautic.report.report.label.aggregators.max'),
        ];

        switch ($column) {
            case 'weight':
            case 'date_added':
                unset($choices['']);
            break;
        }

        if (mb_ereg("date_added_\d+", $column)) {
            unset($choices['']);
        }
        if (mb_ereg("event_\d+", $column)) {
            unset($choices['']);
        }

        return $choices;
    }

    public function setupAvailableFunctionChoices(FormInterface $form, ?string $column)
    {
        if (null === $column) {
            return;
        }
        $choices = $this->getAvabilableFunctionChoices($column);
        if (null === $choices) {
            $form->remove('function');

            return;
        }

        // function
        $form->add('function', 'choice', [
            'choices'     => $choices,
            'expanded'    => false,
            'multiple'    => false,
            'label'       => 'mautic.report.function',
            'label_attr'  => ['class' => 'control-label'],
            'empty_value' => false,
            'required'    => true,
            'attr'        => [
                'class' => 'form-control not-chosen',
            ],
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(
            [
                'fields',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['fields'] = $options['fields'];
    }
}
