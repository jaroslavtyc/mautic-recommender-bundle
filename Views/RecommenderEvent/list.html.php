<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if ($tmpl == 'index') {
    $view->extend('MauticRecommenderBundle:RecommenderEvent:index.html.php');
}
/* @var \MauticPlugin\MauticRecommenderBundle\Entity\Event[] $items */
?>
<?php if (count($items)): ?>
    <div class="table-responsive page-list">
        <table class="table table-hover table-striped table-bordered msgtable-list" id="msgTable">
            <thead>
            <tr>
                <?php
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'        => 'true',
                        'target'          => '#msgTable',
                        'routeBase'       => 'recommender_event',
                        'templateButtons' => [
                            'delete' => $permissions['recommender:recommender:deleteown']
                                || $permissions['recommender:recommender:deleteother'],
                        ],
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'recommender',
                        'orderBy'    => 'e.name',
                        'text'       => 'mautic.core.name',
                        'class'      => 'col-msg-name',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'text'       => 'mautic.recommender.form.event.type',
                        'class'      => 'col-msg-event-type',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'text'       => 'mautic.plugin.recommender.form.event.weight',
                        'class'      => 'col-msg-name',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'recommender',
                        'orderBy'    => 'e.id',
                        'text'       => 'mautic.core.id',
                        'class'      => 'col-msg-id visible-md visible-lg',
                    ]
                );
                ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <?php
                        echo $view->render(
                            'MauticCoreBundle:Helper:list_actions.html.php',
                            [
                                'item'            => $item,
                                'templateButtons' => [
                                    'edit' => $view['security']->hasEntityAccess(
                                        $permissions['recommender:recommender:editown'],
                                        $permissions['recommender:recommender:editother'],
                                        $item->getCreatedBy()
                                    ),
                                    'clone'  => $permissions['recommender:recommender:create'],
                                    'delete' => $view['security']->hasEntityAccess(
                                        $permissions['recommender:recommender:deleteown'],
                                        $permissions['recommender:recommender:deleteother'],
                                        $item->getCreatedBy()
                                    ),
                                ],
                                'routeBase'  => 'recommender_event',
                                'nameGetter' => 'getName',
                            ]
                        );
                        ?>
                    </td>
                    <td>
                        <a href="<?php echo $view['router']->url(
                            'mautic_recommender_event_action',
                            ['objectAction' => 'view', 'objectId' => $item->getId()]
                        ); ?>" data-toggle="ajax">
                            <?php echo $item->getName(); ?>

                        </a>
                    </td>
                    <td>
                        <?php echo $item->getType(); ?>
                    </td>
                    <td>
                        <?php echo $item->getWeight(); ?>
                    </td>
                    <td class="visible-md visible-lg"><?php echo $item->getId(); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="panel-footer">
            <?php echo $view->render(
                'MauticCoreBundle:Helper:pagination.html.php',
                [
                    'totalItems' => count($items),
                    'page'       => $page,
                    'limit'      => $limit,
                    'menuLinkId' => 'mautic_recommender_event_index',
                    'baseUrl'    => $view['router']->url('mautic_recommender_event_index'),
                    'sessionVar' => 'recommender',
                ]
            ); ?>
        </div>
    </div>
<?php else: ?>
    <?php echo $view->render('MauticCoreBundle:Helper:noresults.html.php'); ?>
<?php endif; ?>
