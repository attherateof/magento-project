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

namespace EasyMage\OpenSearchLogger\Api;

/**
 * Search engine config interface
 *
 * @namespace EasyMage\OpenSearchLogger\Api
 * @interface ConfigInterface
 * @api
 */
interface ConfigInterface
{
    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @param bool $isNextMonth
     *
     * @return string
     */
    public function getIndex(bool $isNextMonth = false): string;

    /**
     * @return array<int, int>
     */
    public function getLogLevels(): array;

    /**
     * @return string
     */
    public function geSearchEngineType(): string;
}
