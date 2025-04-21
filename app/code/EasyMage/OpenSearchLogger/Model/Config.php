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
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Configuration model for EasyMage OpenSearch Logger
 *
 * Class Config
 * @package EasyMage\OpenSearchLogger\Model
 */
class Config implements ConfigInterface
{
    /**
     * Configuration paths
     */
    public const IS_ENABLED = 'easy_mage/general/is_enabled';
    public const INDEX = 'easy_mage/general/index_prefix';
    public const LOG_LEVELS = 'easy_mage/general/log_levels';
    public const SEARCH_ENGINE_TYPE = 'catalog/search/engine';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param MonthResolver $monthService
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly MonthResolver $monthService
    ) {
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::IS_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritDoc
     */
    public function getIndex(bool $isNextMonth = false): string
    {
        $month = $this->monthService->getMonthName($isNextMonth);

        return $this->scopeConfig->getValue(
            self::INDEX,
            ScopeInterface::SCOPE_STORE
        ) . '_' . $month;
    }

    /**
     * @inheritDoc
     */
    public function getLogLevels(): array
    {
        $parsedData = [];
        $rawData = $this->scopeConfig->getValue(
            self::LOG_LEVELS,
            ScopeInterface::SCOPE_STORE
        );

        if (!empty($rawData)) {
            $tempArray = explode(',', $rawData);
            $parsedData = array_map('intval', array_filter($tempArray, 'is_numeric'));
        }

        return $parsedData;
    }

    /**
     * @return string
     */
    public function geSearchEngineType(): string
    {
        return $this->scopeConfig->getValue(
            self::SEARCH_ENGINE_TYPE,
            ScopeInterface::SCOPE_STORE
        );
    }
}
