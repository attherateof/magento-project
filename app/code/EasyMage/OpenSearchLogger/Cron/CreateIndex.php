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

namespace EasyMage\OpenSearchLogger\Cron;

use EasyMage\OpenSearchLogger\Model\LogIndexManager;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Cron job for creating monthly log index
 *
 * Class CreateIndex
 * @package EasyMage\OpenSearchLogger\Cron
 */
class CreateIndex
{
    /**
     * @param LoggerInterface $logger
     * @param LogIndexManager $logIndexManager
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly LogIndexManager $logIndexManager
    ) {
    }
    /**
     * Cronjob Description
     *
     * @return void
     */
    public function execute(): void
    {
        try {
            $this->logIndexManager->ensureIlmPolicyExists();
            $this->logIndexManager->createIndexForMonth(true);
        } catch (Throwable $th) {
            $this->logger->error("Error creating log index via cron job", [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
        }
    }
}
