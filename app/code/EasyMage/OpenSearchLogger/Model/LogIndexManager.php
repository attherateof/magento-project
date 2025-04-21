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
use OpenSearch\Common\Exceptions\Missing404Exception;
use Exception;
use RuntimeException;

/**
 * Log Index Manager
 *
 * Class LogIndexManager
 * @package EasyMage\OpenSearchLogger\Model
 */
class LogIndexManager
{
    /**
     * ILM policy name
     */
    public const ILM_POLICY_NAME = 'magento_log_policy';

    /**
     * @param SearchClient $client
     * @param ConfigInterface $config
     */
    public function __construct(
        private readonly SearchClient $client,
        private readonly ConfigInterface $config
    ) {
    }

    /**
     * Ensure ILM policy exists
     *
     * @return void
     */
    public function ensureIlmPolicyExists(): void
    {
        try {
            if ($this->client->getClientInstance()->ism()->existsPolicy([
                'policy_id' => self::ILM_POLICY_NAME
            ])) {
                return;
            }
        } catch (Missing404Exception $e) {
            // No log needed
            // ILM policy not found - this is expected
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }

        $policyBody = [
            'policy' => [
                'description' => 'Policy to delete indices after 180 days',
                'default_state' => 'hot',
                'states' => [
                    [
                        'name' => 'hot',
                        'actions' => [],
                        'transitions' => [
                            [
                                'state_name' => 'delete',
                                'conditions' => [
                                    'min_index_age' => '180d'
                                ]
                            ]
                        ]
                    ],
                    [
                        'name' => 'delete',
                        'actions' => [
                            [
                                'delete' => (object)[]
                            ]
                        ],
                        'transitions' => []
                    ]
                ],
                'ism_template' => [
                    'index_patterns' => ['magento_logs*']
                ]
            ]
        ];
        $result = $this->client->getClientInstance()->ism()->putPolicy([
            'policy_id' => self::ILM_POLICY_NAME,
            'body' => $policyBody
        ]);
    }

    /**
     * Create index for the current or next month
     *
     * @param bool $isNextMonth
     * @return void
     */
    public function createIndexForMonth(bool $isNextMonth = false): void
    {
        $indexName = $this->config->getIndex($isNextMonth);

        if ($this->client->getClientInstance()->indices()->exists(['index' => $indexName])) {
            return;
        }

        $this->client->getClientInstance()->indices()->create([
            'index' => $indexName,
            'body' => [
                'settings' => [
                    'index' => [
                        'number_of_shards' => 1,
                        'number_of_replicas' => 1,
                        'plugins.index_state_management.policy_id' => self::ILM_POLICY_NAME, // 👈 Correctly nested
                    ]
                ],
                'mappings' => [
                    'properties' => [
                        'message' => ['type' => 'text'],
                        'level_name' => ['type' => 'keyword'],
                        'channel' => ['type' => 'keyword'],
                        'path' => ['type' => 'keyword'],
                        'datetime' => ['type' => 'date'],
                        'context' => ['type' => 'object'],
                        'extra' => ['type' => 'object']
                    ]
                ]
            ]
        ]);
    }
}
