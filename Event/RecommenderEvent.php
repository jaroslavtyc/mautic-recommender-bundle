<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecommenderBundle\Event;

use Mautic\CoreBundle\Event\CommonEvent;
use MauticPlugin\MauticRecommenderBundle\Entity\RecommenderTemplate;

class RecommenderEvent extends CommonEvent
{
    /**
     * RecommenderEvent constructor.
     *
     * @param RecommenderTemplate $entity
     * @param bool $isNew
     */
    public function __construct(RecommenderTemplate $entity, bool $isNew = false)
    {
        $this->entity = $entity;
        $this->isNew = $isNew;
    }

    /**
     * @return RecommenderTemplate
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param RecommenderTemplate $entity
     */
    public function setEntity(RecommenderTemplate $entity)
    {
        $this->entity = $entity;
    }
}
