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

namespace EasyMage\OpenSearchLogger\Plugin;

use EasyMage\OpenSearchLogger\Api\ConfigInterface;
use EasyMage\OpenSearchLogger\Model\SearchClient;
use Magento\Framework\Logger\Handler\Base;
use Psr\Log\LoggerInterface;
use Monolog\LogRecord as MonologLogRecord;
use Throwable;

/**
 * LogRecord Plugin
 *
 * Class LogRecord
 * @package EasyMage\OpenSearchLogger\Plugin
 */
class LogRecord
{
    /**
     * @param LoggerInterface $logger
     * @param SearchClient $client
     * @param ConfigInterface $config
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly SearchClient $client,
        private readonly ConfigInterface $config
    ) {
    }

    /**
     * @param Base $subject
     * @param bool $result
     * @param MonologLogRecord $record
     *
     * @return bool
     */
    public function afterHandle(Base $subject, bool $result, MonologLogRecord $record): bool
    {
        try {
            $shouldLog = true;
            if (!$this->config->isEnabled()) {
                $shouldLog = false;
            }

            $level = $record['level'] ?? null;
            if ($shouldLog && !in_array((int)$level, $this->config->getLogLevels(), true)) {
                $shouldLog = false;
            }

            if ($shouldLog && !empty($record['extra']) && !empty($record['extra']['is_search_exception'])) {
                $shouldLog = false;
            }

            if ($shouldLog) {
                $this->client->getClientInstance()->index([
                    'index' => $this->config->getIndex(),
                    'body' => [
                        'message'     => (string)($record['message'] ?? ''),
                        'level_name'  => (string)($record['level_name'] ?? ''),
                        'channel'     => (string)($record['channel'] ?? ''),
                        'path'        => (string)($subject->getUrl()),
                        'datetime'    => $record['datetime'] ?? date('c'),
                        'context'     => $this->sanitizeData($record['context'] ?? []),
                        'extra'       => $this->sanitizeData($record['extra'] ?? []),
                    ],
                ]);
            }
        } catch (Throwable $th) {
            $this->logger->error('Error while logging to search engine', [
                'message' => $th->getMessage(),
                'trace'   => $th->getTraceAsString(),
            ]);
        }

        return $result;
    }

    /**
     * @param mixed $context
     *
     * @return array<int|string, mixed>
     */
    private function sanitizeData(mixed $context): array
    {
        try {
            if (!is_array($context) && !is_object($context)) {
                $context = [
                    'message' => (string)$context,
                ];
            } elseif (is_object($context)) {
                $context = (array)$context;
            }
        } catch (\Exception $e) {
            $context = [];
            $this->logger->error("Error while sanitising data", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $context;
    }
}
