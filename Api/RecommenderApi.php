<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticRecommenderBundle\Api;

use Mautic\CoreBundle\Templating\Helper\VersionHelper;
use Mautic\PageBundle\Model\TrackableModel;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\MauticRecommenderBundle\Api\Client\Client;
use Monolog\Logger;

class RecommenderApi
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var IntegrationHelper
     */
    private $integrationHelper;

    /**
     * @var VersionHelper
     */
    private $versionHelper;

    /**
     * TwilioApi constructor.
     *
     * @param TrackableModel $pageTrackableModel
     * @param IntegrationHelper $integrationHelper
     * @param Logger $logger
     * @param VersionHelper $versionHelper
     * @param Client $client
     *
     * @internal param CoreParametersHelper $coreParametersHelper
     */
    public function __construct(
        TrackableModel $pageTrackableModel,
        IntegrationHelper $integrationHelper,
        Logger $logger,
        VersionHelper $versionHelper,
        Client $client
    )
    {
        $this->logger = $logger;
        $this->client = $client;

        return;

        $integration = $integrationHelper->getIntegrationObject('Recommender');
        if (($integration && $integration->getIntegrationSettings()->getIsPublished()) || isset($_POST['integration_details'])) {
            $keys = $integration->getDecryptedApiKeys();

            if (!empty($_POST['integration_details']['apiKeys'])) {
                $keys = $_POST['integration_details']['apiKeys'];
            }
            if (empty($keys['database']) && empty($keys['secret_key'])) {
                $keys['database'] = trim(getenv('d'));
                $keys['secret_key'] = trim(getenv('s'));
            }
        }

        $database = '';
        if (!empty($keys['database'])) {
            $database = $keys['database'];
        }

        $secretKey = '';
        if (!empty($keys['secret_key'])) {
            $secretKey = $keys['secret_key'];
        }

        /*  $this->client = new Client(
              $database,
              $secretKey,
              'https',
              ['serviceName' => 'Mautic '.$versionHelper->getVersion()]
          );*/

        parent::__construct($pageTrackableModel);
        $this->integrationHelper = $integrationHelper;
        $this->versionHelper = $versionHelper;
        $this->client = $client;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
