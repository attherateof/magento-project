<?php
/**
 * Copyright © 2025 EasyMage. All rights reserved.
 * See COPYING.txt for license details.
 *
 * @author    Amit Biswas <amit.biswas.webdeveloper@gmail.com>
 * @copyright 2025 EasyMage
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace EasyMage\OpenSearchLogger\Model;

use EasyMage\OpenSearchLogger\Api\ConfigInterface;
use OpenSearch\Client;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use RuntimeException;

/**
 * Search Client
 *
 * Class SearchClient
 * @package EasyMage\OpenSearchLogger\Model
 */
class SearchClient
{
    /**
     * @param ConnectionManager $client
     * @param ConfigInterface $config
     */
    public function __construct(
        private readonly ConnectionManager $client,
        private readonly ConfigInterface $config
    ) {
    }

    /**
     * @return Client
     *
     * @throws RuntimeException
     */
    public function getClientInstance(): Client
    {
        $engine = $this->config->geSearchEngineType();
        $client = $this->client->getConnection();
        if ($engine === 'opensearch' && method_exists($client, 'getOpenSearchClient')) {
            $searchClient = $client->getOpenSearchClient();
        } else {
            throw new RuntimeException('Unsupported or misconfigured search engine: ' . $engine);
        }

        return $searchClient;
    }
}
