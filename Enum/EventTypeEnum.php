<?php

/*
 * @copyright   2020 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecommenderBundle\Enum;

use Mautic\CoreBundle\Helper\ArrayHelper;
use Symfony\Component\Form\AbstractType;

class EventTypeEnum extends AbstractType
{
    public const DETAIL_VIEW = 'detail_view';
    public const CART_ADDITIONS = 'cart_additions';
    public const PURCHASE = 'purchase';

    public static function getTypes(): array
    {
        return [
            self::DETAIL_VIEW,
            self::CART_ADDITIONS,
            self::PURCHASE,
        ];
    }

    public static function getChoices(): array
    {
        return [
            self::DETAIL_VIEW => 'mautic.recommender.event.type.detail_view',
            self::CART_ADDITIONS => 'mautic.recommender.event.type.cart_additions',
            self::PURCHASE => 'mautic.recommender.event.type.purchase',
        ];
    }

    public static function getChoice(string $type = null)
    {
        return ArrayHelper::getValue($type, self::getChoices());
    }
}
