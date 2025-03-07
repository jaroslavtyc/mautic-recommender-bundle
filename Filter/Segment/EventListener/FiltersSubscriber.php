<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecommenderBundle\Filter\Segment\EventListener;

use Mautic\CoreBundle\Event\BuildJsEvent;
use Mautic\LeadBundle\Event\LeadListFilteringEvent;
use Mautic\LeadBundle\Event\LeadListFiltersChoicesEvent;
use Mautic\LeadBundle\LeadEvents;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticRecommenderBundle\Filter\Recommender\Choices;
use MauticPlugin\MauticRecommenderBundle\Filter\Segment\Decorator\Decorator;
use MauticPlugin\MauticRecommenderBundle\Filter\Segment\FilterFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class FiltersSubscriber implements EventSubscriberInterface
{
    /**
     * @var FilterFactory
     */
    private $filterFactory;

    /**
     * @var Choices
     */
    private $choices;

    /**
     * @var Decorator
     */
    private $decorator;

    /**
     * @var IntegrationHelper
     */
    protected $integrationHelper;
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        FilterFactory $segmentFilterFactory,
        Choices $choices,
        Decorator $decorator,
        IntegrationHelper $integrationHelper,
        RequestStack $requestStack
    )
    {
        $this->filterFactory = $segmentFilterFactory;
        $this->choices = $choices;
        $this->decorator = $decorator;
        $this->integrationHelper = $integrationHelper;
        $this->requestStack = $requestStack;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            LeadEvents::LIST_FILTERS_CHOICES_ON_GENERATE => [
                ['onListFiltersGenerate', 0],
            ],

            LeadEvents::LIST_FILTERS_ON_FILTERING => [
                ['onListFiltersFiltering', 0],
            ],
        ];
    }

    /**
     * @param LeadListFilteringEvent $event
     */
    public function onListFiltersFiltering(LeadListFilteringEvent $event)
    {
        $integration = $this->integrationHelper->getIntegrationObject('Recommender');
        if (!$integration || $integration->getIntegrationSettings()->getIsPublished() === false) {
            return;
        }

        $qb = $event->getQueryBuilder();
        $filter = $event->getDetails();
        if (false !== strpos($filter['object'], 'recommender')) {
            $filter = $this->filterFactory->getContactSegmentFilter($filter, $this->decorator);
            $filter->applyQuery($qb);
            $event->setFilteringStatus(true);
        }
    }

    /**
     * @param BuildJsEvent $event
     */
    public function onListFiltersGenerate(LeadListFiltersChoicesEvent $event)
    {
        $integration = $this->integrationHelper->getIntegrationObject('Recommender');
        if (!$integration || $integration->getIntegrationSettings()->getIsPublished() === false) {
            return;
        }

        if (in_array($this->requestStack->getCurrentRequest()->attributes->get('_route'), ['mautic_segment_action', 'mautic_recommender_action'])) {
            $this->choices->addChoicesToEvent($event, 'recommender_event');
        }
    }
}
