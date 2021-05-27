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

use Mautic\CoreBundle\Helper\BuilderTokenHelperFactory;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event\EmailBuilderEvent;
use Mautic\EmailBundle\Event\EmailSendEvent;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticRecommenderBundle\Helper\RecommenderHelper;
use MauticPlugin\MauticRecommenderBundle\Service\RecommenderTokenReplacer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class EmailSubscriber.
 */
class EmailSubscriber implements EventSubscriberInterface
{
    /**
     * @var RecommenderHelper
     */
    protected $recommenderHelper;

    /**
     * @var RecommenderTokenReplacer
     */
    private $recommenderTokenReplacer;

    /**
     * @var IntegrationHelper
     */
    protected $integrationHelper;

    /**
     * @var BuilderTokenHelperFactory
     */
    protected $builderTokenHelperFactory;

    public function __construct(
        RecommenderHelper $recommenderHelper,
        RecommenderTokenReplacer $recommenderTokenReplacer,
        IntegrationHelper $integrationHelper,
        BuilderTokenHelperFactory $builderTokenHelperFactory
    )
    {
        $this->recommenderHelper = $recommenderHelper;
        $this->recommenderTokenReplacer = $recommenderTokenReplacer;
        $this->integrationHelper = $integrationHelper;
        $this->builderTokenHelperFactory = $builderTokenHelperFactory;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EmailEvents::EMAIL_ON_BUILD => ['onPageBuild', 0],
            EmailEvents::EMAIL_ON_SEND => ['onEmailGenerate', 0],
            EmailEvents::EMAIL_ON_DISPLAY => ['onEmailDisplay', 0],
        ];
    }

    /**
     * Add email to available page tokens.
     *
     * @param EmailBuilderEvent $event
     */
    public function onPageBuild(EmailBuilderEvent $event)
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

    /**
     * @param EmailSendEvent $event
     */
    public function onEmailDisplay(EmailSendEvent $event)
    {
        $integration = $this->integrationHelper->getIntegrationObject('Recommender');
        if (!$integration || $integration->getIntegrationSettings()->getIsPublished() === false) {
            return;
        }

        $this->onEmailGenerate($event);
    }

    /**
     * @param EmailSendEvent $event
     */
    public function onEmailGenerate(EmailSendEvent $event)
    {
        $integration = $this->integrationHelper->getIntegrationObject('Recommender');
        if (!$integration || $integration->getIntegrationSettings()->getIsPublished() === false) {
            return;
        }

        if ($event->getEmail() && $event->getEmail()->getId() && !empty($event->getLead()['id'])) {
            $this->recommenderTokenReplacer->getRecommenderToken()->setUserId($event->getLead()['id']);
            $this->recommenderTokenReplacer->getRecommenderToken()->setContent($event->getContent());
            if ($content = $this->recommenderTokenReplacer->getReplacedContent('Email')) {
                $event->setContent($content);
            }
            if ($subject = $this->recommenderTokenReplacer->getRecommenderGenerator()->replaceTagsFromContent($event->getSubject())) {
                $event->setSubject($subject);
            }
        }
    }
}
