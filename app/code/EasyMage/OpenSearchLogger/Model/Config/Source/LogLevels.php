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

namespace EasyMage\OpenSearchLogger\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Monolog\Logger;

/**
 * Log Levels for config options
 *
 * Class LogLevels
 * @package EasyMage\OpenSearchLogger\Model\Config\Source
 */
class LogLevels implements OptionSourceInterface
{
    /**
     * @return array<int, array<string, int|string>>
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => Logger::DEBUG, 'label' => __('DEBUG')->render()],
            ['value' => Logger::INFO, 'label' => __('INFO')->render()],
            ['value' => Logger::NOTICE, 'label' => __('NOTICE')->render()],
            ['value' => Logger::WARNING, 'label' => __('WARNING')->render()],
            ['value' => Logger::ERROR, 'label' => __('ERROR')->render()],
            ['value' => Logger::CRITICAL, 'label' => __('CRITICAL')->render()],
            ['value' => Logger::ALERT, 'label' => __('ALERT')->render()],
            ['value' => Logger::EMERGENCY, 'label' => __('EMERGENCY')->render()],
        ];
    }
}
