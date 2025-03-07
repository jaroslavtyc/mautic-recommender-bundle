<?php

/*
 * @copyright   2016 Mautic, Inc. All rights reserved
 * @author      Mautic, Inc
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecommenderBundle\Controller;

use Mautic\CoreBundle\Controller\AjaxController as CommonAjaxController;
use Mautic\CoreBundle\Helper\InputHelper;
use MauticPlugin\MauticRecommenderBundle\Entity\RecommenderTemplate;
use MauticPlugin\MauticRecommenderBundle\Form\Type\RecommenderTableOrderType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

class AjaxController extends CommonAjaxController
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function generatePreviewAction(Request $request)
    {
        $data = [];
        $recommender = $request->request->all();

        if (isset($recommender['recommender_templates'])) {
            $recommenderEntity = new RecommenderTemplate();
            $accessor = PropertyAccess::createPropertyAccessor();
            $recommenderArrays = InputHelper::_($recommender['recommender_templates']);
            foreach ($recommenderArrays as $key => $recommenderArray) {
                //   $accessor->setValue($recommenderEntity, $key, $recommenderArray);
                $setter = 'set' . ucfirst($key);
                if (method_exists($recommenderEntity, $setter)) {
                    $recommenderEntity->$setter($recommenderArray);
                }
            }
            $data['content'] = $this->get('mautic.helper.templating')->getTemplating()->render(
                'MauticRecommenderBundle:Builder\\Page:generator.html.php',
                [
                    'recommender' => $recommenderEntity,
                    'settings' => $this->get('mautic.helper.integration')->getIntegrationObject('Recommender')->getIntegrationSettings()->getFeatureSettings(),
                    'preview' => true,
                ]
            );
        }

        return $this->sendJsonResponse($data);
    }

    public function listavailablefunctionsAction(Request $request)
    {
        $column = $request->request->get('column', $request->query->get('column'));
        //$tableOrderForm = $this->get();
        $fields = $this->get('mautic.recommender.filter.fields.recommender')->getSelectOptions();

        $form = $this->get('form.factory')->createNamedBuilder(
            'recommender',
            FormType::class,
            null,
            ['auto_initialize' => false]
        )->add(
            'tableOrder',
            RecommenderTableOrderType::class,
            ['data' => ['column' => $column], 'fields' => $fields]
        )->getForm();

        //return $this->get('mautic.recommender.contact.search')->delegateForm($objectId, $this);

        $data['content'] = $this->get('mautic.helper.templating')->getTemplating()->render(
            'MauticRecommenderBundle:Recommender:form.function.html.php',
            [
                'form' => $form->createView(),
            ]
        );

        return $this->sendJsonResponse($data);
    }
}
