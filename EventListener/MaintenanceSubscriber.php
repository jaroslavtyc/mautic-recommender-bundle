<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecommenderBundle\EventListener;

use Doctrine\DBAL\Connection;
use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Event\MaintenanceEvent;
use Recurr\Transformer\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MaintenanceSubscriber implements EventSubscriberInterface
{
    /**
     * @var Connection
     */
    protected $db;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(Connection $db, TranslatorInterface $translator)
    {
        $this->db = $db;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::MAINTENANCE_CLEANUP_DATA => ['onDataCleanup', 10], // Cleanup before visitors are processed
        ];
    }

    /**
     * @param MaintenanceEvent $event
     */
    public function onDataCleanup(MaintenanceEvent $event)
    {
        $this->cleanEventLog($event, 'recommender_event_log');
        $this->cleanItems($event, 'recommender_item');
    }

    /**
     * @param MaintenanceEvent $event
     * @param string $table tableName
     *
     * This function cleans old data from recommender_event_log table like standard Mautic cleanup: old data of unidentified contacts OR with --gdpr option: very old contacts are cleaned up
     * Later we may add funcionality clean old data overall. But that may require plugin setting.
     */
    private function cleanEventLog(MaintenanceEvent $event, $table)
    {
        $qb = $this->db->createQueryBuilder()
            ->setParameter('date', $event->getDate()->format('Y-m-d H:i:s'));

        if ($event->isDryRun()) {
            $qb->select('count(*) as records')
                ->from(MAUTIC_TABLE_PREFIX . $table, 'rel')
                ->join('rel', MAUTIC_TABLE_PREFIX . 'leads', 'l', 'rel.lead_id = l.id')
                ->where($qb->expr()->lte('l.last_active', ':date'));

            if ($event->isGdpr() === false) {
                $qb->andWhere($qb->expr()->isNull('l.date_identified'));
            } else {
                $qb->orWhere(
                    $qb->expr()->andX(
                        $qb->expr()->lte('l.date_added', ':date2'),
                        $qb->expr()->isNull('l.last_active')
                    ));
                $qb->setParameter('date2', $event->getDate()->format('Y-m-d H:i:s'));
            }

            $rows = $qb->execute()->fetchOne();
        } else {
            $subQb = $this->db->createQueryBuilder();
            $subQb->select('id')->from(MAUTIC_TABLE_PREFIX . 'leads', 'l')
                ->where($qb->expr()->lte('l.last_active', ':date'));

            if ($event->isGdpr() === false) {
                $subQb->andWhere($qb->expr()->isNull('l.date_identified'));
            } else {
                $subQb->orWhere(
                    $subQb->expr()->and(
                        $subQb->expr()->lte('l.date_added', ':date2'),
                        $subQb->expr()->isNull('l.last_active')
                    ));
                $subQb->setParameter('date2', $event->getDate()->format('Y-m-d H:i:s'));
            }
            $rows = 0;
            $loop = 0;
            $subQb->setParameter('date', $event->getDate()->format('Y-m-d H:i:s'));
            while (true) {
                $subQb->setMaxResults(100)->setFirstResult($loop * 100);

                $leadsIds = array_column($subQb->execute()->fetchAllNumeric(), 'id');

                if (count($leadsIds) === 0) {
                    break;
                }

                $rows += $qb->delete(MAUTIC_TABLE_PREFIX . $table)
                    ->where(
                        $qb->expr()->in(
                            'lead_id', $leadsIds
                        )
                    )
                    ->execute();
                ++$loop;
            }
        }
        $event->setStat($this->translator->trans('mautic.plugin.recommender.maintenance.' . $table), $rows, $qb->getSQL(), $qb->getParameters());
    }

    /**
     * @param MaintenanceEvent $event
     * @param string $table tableName
     */
    private function cleanItems(MaintenanceEvent $event, $table)
    {
        $qb = $this->db->createQueryBuilder()
            ->setParameter('date', $event->getDate()->format('Y-m-d H:i:s'));

        if ($event->isDryRun()) {
            $qb->select('count(*) as records')
                ->from(MAUTIC_TABLE_PREFIX . $table, 'ri')
                ->andWhere($qb->expr()->lte('ri.date_modified', ':date'))
                ->andWhere($qb->expr()->eq('ri.active', '0'));

            $rows = $qb->execute()->fetchColumn();
        } else {
            $qb->select('id')
                ->from(MAUTIC_TABLE_PREFIX . $table, 'ri')
                ->andWhere($qb->expr()->lte('ri.date_modified', ':date'))
                ->andWhere($qb->expr()->eq('ri.active', '0'));

            $itemIds = $qb->execute()->fetchAll(\PDO::FETCH_COLUMN, 0);
            $rows = 0;

            foreach ($itemIds as $item_id) {
                $rows += $qb->delete(MAUTIC_TABLE_PREFIX . $table)
                    ->where(
                        $qb->expr()->eq(
                            'id', $item_id
                        )
                    )
                    ->execute();
            }
        }
        $event->setStat($this->translator->trans('mautic.plugin.recommender.maintenance.' . $table), $rows, $qb->getSQL(), $qb->getParameters());
    }
}
