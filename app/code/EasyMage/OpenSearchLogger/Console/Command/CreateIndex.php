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

namespace EasyMage\OpenSearchLogger\Console\Command;

use EasyMage\OpenSearchLogger\Model\LogIndexManager;
use Magento\Framework\Console\Cli;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Create monthly index command
 *
 * Class CreateIndex
 * @namespace EasyMage\OpenSearchLogger\Console\Command
 */
class CreateIndex extends Command
{
    /**
     * @var string
     */
    private const ARG_IS_NEXT_MONTH = 'next-month';

    /**
     * @param LoggerInterface $logger
     * @param LogIndexManager $logIndexManager
     * @param string|null $name
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly LogIndexManager $logIndexManager,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('easy:mage:create:log:index');
        $this->setDescription('Create Log Index for current or next month')
            ->addArgument(
                self::ARG_IS_NEXT_MONTH,
                InputArgument::OPTIONAL,
                'Set to 1 to create for next month, 0 (or omit) for current month',
                '0' // Default: current month
            );
        parent::configure();
    }

    /**
     * CLI command description.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $isNext = (bool)((int)$input->getArgument(self::ARG_IS_NEXT_MONTH));

        try {
            $this->logIndexManager->ensureIlmPolicyExists();
            $this->logIndexManager->createIndexForMonth($isNext);
            $output->writeln("<info>Log index has been created successfully.</info>");

            return Cli::RETURN_SUCCESS;
        } catch (Throwable $th) {
            $this->logger->error("Error creating log index", [
                'message' => $th->getMessage(),
                'is_next' => $isNext,
                'trace' => $th->getTraceAsString(),
            ]);
            $output->writeln("<error>Error: {$th->getMessage()}</error>");

            return Cli::RETURN_FAILURE;
        }
    }
}
