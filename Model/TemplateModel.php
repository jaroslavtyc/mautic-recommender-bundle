<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecommenderBundle\Model;

use Mautic\CoreBundle\Model\AjaxLookupModelInterface;
use Mautic\CoreBundle\Model\FormModel;
use MauticPlugin\MauticRecommenderBundle\Entity\RecommenderTemplate;
use MauticPlugin\MauticRecommenderBundle\Entity\RecommenderTemplateRepository;
use MauticPlugin\MauticRecommenderBundle\Event\RecommenderEvent;
use MauticPlugin\MauticRecommenderBundle\Form\Type\RecommenderTemplatesType;
use MauticPlugin\MauticRecommenderBundle\MauticRecommenderEvents;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class TemplateModel extends FormModel implements AjaxLookupModelInterface
{
    public function getPermissionBase(): string
    {
        return 'recommender:recommender';
    }

    /**
     * {@inheritdoc}
     *
     * @return RecommenderTemplateRepository
     */
    public function getRepository(): RecommenderTemplateRepository
    {
        /** @var RecommenderTemplateRepository $repo */
        $repo = $this->em->getRepository('MauticRecommenderBundle:RecommenderTemplate');

        $repo->setTranslator($this->translator);

        return $repo;
    }

    /**
     * Here just so PHPStorm calms down about type hinting.
     *
     * @param null $id
     *
     * @return null|RecommenderTemplate
     */
    public function getEntity($id = null): ?RecommenderTemplate
    {
        if ($id === null) {
            return new RecommenderTemplate();
        }

        return parent::getEntity($id);
    }

    /**
     * {@inheritdoc}
     *
     * @param       $entity
     * @param FormFactory $formFactory
     * @param null $action
     * @param array $options
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function createForm($entity, $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof RecommenderTemplate) {
            throw new \InvalidArgumentException('Entity must be of class RecommenderTemplate');
        }

        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create(RecommenderTemplatesType::class, $entity, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @param $action
     * @param $entity
     * @param $isNew
     * @param $event
     *
     * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
     */
    protected function dispatchEvent($action, &$entity, $isNew = false, Event $event = null)
    {
        if (!$entity instanceof RecommenderTemplate) {
            throw new MethodNotAllowedHttpException(['RecommenderTemplate']);
        }

        switch ($action) {
            case 'pre_save':
                $name = MauticRecommenderEvents::PRE_SAVE;
                break;
            case 'post_save':
                $name = MauticRecommenderEvents::POST_SAVE;
                break;
            case 'pre_delete':
                $name = MauticRecommenderEvents::PRE_DELETE;
                break;
            case 'post_delete':
                $name = MauticRecommenderEvents::POST_DELETE;
                break;
            default:
                return null;
        }

        if ($this->dispatcher->hasListeners($name)) {
            if (!$event) {
                $event = new RecommenderEvent($entity, $isNew);
                $event->setEntityManager($this->em);
            }

            $this->dispatcher->dispatch($name, $event);

            return $event;
        }
        return null;
    }

    /**
     * @param        $type
     * @param string $filter
     * @param int $limit
     * @param int $start
     * @param array $options
     */
    public function getLookupResults($type, $filter = '', $limit = 10, $start = 0, $options = [])
    {
        $results = [];
        switch ($type) {
            case 'recommender_templates':
                $entities = $this->getRepository()->getRecommenderList(
                    $filter,
                    $limit,
                    $start,
                    $this->security->isGranted($this->getPermissionBase() . ':viewother'),
                    isset($options['top_level']) ? $options['top_level'] : false,
                    isset($options['ignore_ids']) ? $options['ignore_ids'] : []
                );

                foreach ($entities as $entity) {
                    $results[$entity['language']][$entity['id']] = $entity['name'];
                }

                //sort by language
                ksort($results);

                break;
        }

        return $results;
    }
}
