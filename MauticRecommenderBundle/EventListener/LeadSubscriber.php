<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecommenderBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Mautic\LeadBundle\Event\LeadTimelineEvent;
use Mautic\LeadBundle\LeadEvents;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticRecommenderBundle\Entity\EventLog;
use MauticPlugin\MauticRecommenderBundle\Entity\EventLogRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

class LeadSubscriber implements EventSubscriberInterface
{
    /**
     * @var integrationHelper
     */
    protected $integrationHelper;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function __construct(
        IntegrationHelper $integrationHelper,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    )
    {
        $this->integrationHelper = $integrationHelper;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LeadEvents::TIMELINE_ON_GENERATE => ['onTimelineGenerate', 0],
        ];
    }

    /**
     * Compile events for the lead timeline.
     *
     * @param LeadTimelineEvent $event
     */
    public function onTimelineGenerate(LeadTimelineEvent $event)
    {
        $integration = $this->integrationHelper->getIntegrationObject('Recommender');
        if (!$integration) {
            return;
        }
        $integrationSettings = $integration->getIntegrationSettings();
        if ($integrationSettings->getIsPublished() === false) {
            return;
        }

        // Set available event types
        $eventTypeKey = 'recommender.event';
        $eventTypeName = $this->translator->trans('mautic.plugin.recommender.event.timeline_event');
        $event->addEventType($eventTypeKey, $eventTypeName);

        if (!$event->isApplicable($eventTypeKey)) {
            return;
        }

        /** @var EventLogRepository $eventLogRepository */
        $eventLogRepository = $this->entityManager->getRepository('MauticRecommenderBundle:EventLog');
        $rows = $eventLogRepository->getTimeLineEvents($event->getLead(), $event->getQueryOptions());

        // Add total to counter
        $event->addToCounter($eventTypeKey, $rows);

        if (!$event->isEngagementCount()) {
            // Add the submissions to the event array
            foreach ($rows['results'] as $row) {
                /** @var EventLog $eventLogEntity */
                $eventLogEntity = $eventLogRepository->getEntity($row['id']);
                $event->addEvent(
                    [
                        'event' => $eventTypeKey,
                        'eventId' => $eventTypeKey . $row['id'],
                        'eventLabel' => $this->getLabel($eventLogEntity),
                        'eventType' => $eventTypeName,
                        'timestamp' => $row['date_added'],
                        'icon' => 'fa-shopping-bag',
                        'contactId' => $row['lead_id'],
                    ]
                );
            }
        }
    }

    private function getLabel(EventLog $eventLogEntity): string
    {
        return $this->translator->trans(
            'mautic.plugin.recommender.event.timeline_event.label',
            [
                '%event_name%' => $eventLogEntity->getEvent() ? $eventLogEntity->getEvent()->getName() : 'deleted',
                '%item_id%' => $eventLogEntity->getItem() ? $eventLogEntity->getItem()->getId() : 'deleted',
            ]
        );
    }
}
