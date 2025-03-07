<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

/** @var bool $preview */
/** @var \MauticPlugin\MauticRecommenderBundle\Entity\Recommender $recommender */

?>
</tr>
</table>
</td>
</tr>
<tr>
    <td align="center" valign="top">
        <?php if ($preview) {
            echo html_entity_decode($recommender->getProperties()['footer']); ?>
            <?php
        } else {
            echo $recommender->getProperties()['footer']; ?>
            <?php
        } ?>
    </td>
</tr>
</table>
</center>

