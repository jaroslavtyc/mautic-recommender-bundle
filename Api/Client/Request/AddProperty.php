<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecommenderBundle\Api\Client\Request;

use MauticPlugin\MauticRecommenderBundle\Entity\Property;

class AddProperty extends AbstractRequest
{
    public function find()
    {
        return $this->getRepo()->findOneBy(['name' => $this->getOptions()['name']]);
    }

    /**
     * Just return new entity.
     *
     * @return Property
     */
    public function newEntity()
    {
        return new Property();
    }

    /**
     * @return \Doctrine\ORM\EntityRepository|\MauticPlugin\MauticRecommenderBundle\Entity\PropertyRepository
     */
    public function getRepo()
    {
        return $this->getModel()->getPropertyRepository();
    }
}
