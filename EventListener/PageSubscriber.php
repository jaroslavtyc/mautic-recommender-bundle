<?php

/*
 * @copyright   2015 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecommenderBundle\EventListener;

use Mautic\CoreBundle\Helper\BuilderTokenHelperFactory;
use Mautic\LeadBundle\Tracker\ContactTracker;
use Mautic\PageBundle\Event as Events;
use Mautic\PageBundle\PageEvents;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticRecommenderBundle\Helper\RecommenderHelper;
use MauticPlugin\MauticRecommenderBundle\Service\RecommenderTokenReplacer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PageSubscriber implements EventSubscriberInterface
{
    /**
     * @var RecommenderTokenReplacer
     */
    private $recommenderTokenReplacer;

    /**
     * @var ContactTracker
     */
    private $contactTracker;

    /**
     * @var IntegrationHelper
     */
    protected $integrationHelper;

    /**
     * @var BuilderTokenHelperFactory
     */
    protected $builderTokenHelperFactory;

    public function __construct(
        RecommenderTokenReplacer $recommenderTokenReplacer,
        ContactTracker $contactTracker,
        IntegrationHelper $integrationHelper,
        BuilderTokenHelperFactory $builderTokenHelperFactory
    )
    {
        $this->recommenderTokenReplacer = $recommenderTokenReplacer;
        $this->contactTracker = $contactTracker;
        $this->integrationHelper = $integrationHelper;
        $this->builderTokenHelperFactory = $builderTokenHelperFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PageEvents::PAGE_ON_BUILD => ['onPageBuild', 0],
            PageEvents::PAGE_ON_DISPLAY => ['onPageDisplay', 200],
        ];
    }

    /**
     * Add forms to available page tokens.
     */
    public function onPageBuild(Events\PageBuilderEvent $event)
    {
        $integration = $this->integrationHelper->getIntegrationObject('Recommender');
        if (!$integration || $integration->getIntegrationSettings()->getIsPublished() === false) {
            return;
        }

        if ($event->tokensRequested(RecommenderHelper::$recommenderRegex)) {
            $tokenHelper = $this->builderTokenHelperFactory->getBuilderTokenHelper('recommender');
            $event->addTokensFromHelper($tokenHelper, RecommenderHelper::$recommenderRegex, 'name', 'id', true);
        }
    }

    public function onPageDisplay(Events\PageDisplayEvent $event)
    {
        $integration = $this->integrationHelper->getIntegrationObject('Recommender');
        if (!$integration || $integration->getIntegrationSettings()->getIsPublished() === false) {
            return;
        }

        $lead = $this->contactTracker->getContact();
        $leadId = ($lead) ? $lead->getId() : null;
        if ($leadId && $event->getPage()) {
            $this->recommenderTokenReplacer->getRecommenderToken()->setUserId($leadId);
            $this->recommenderTokenReplacer->getRecommenderToken()->setContent($event->getContent());
            $event->setContent($this->recommenderTokenReplacer->getReplacedContent());
        }
    }
}
