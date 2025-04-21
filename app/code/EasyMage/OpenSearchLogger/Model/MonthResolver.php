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

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Month Resolver
 *
 * Class MonthResolver
 * @package EasyMage\OpenSearchLogger\Model
 */
class MonthResolver
{
    public function __construct(private readonly TimezoneInterface $timezone)
    {
    }

    /**
     * Get the month name
     *
     * @param bool $isNextMonth
     *
     * @return string
     */
    public function getMonthName(bool $isNextMonth = false): string
    {
        $currentDate = $this->timezone->date();
        if ($isNextMonth) {
            $currentDate = $currentDate->modify('+1 month');
        }

        return strtolower($currentDate->format('F'));
    }
}
