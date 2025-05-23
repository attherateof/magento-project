<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CodeMessDetector\Rule\Design;

use Magento\Framework\GetParameterClassTrait;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieReaderInterface;
use PDepend\Source\AST\ASTClass;
use PHPMD\AbstractNode;
use PHPMD\AbstractRule;
use PHPMD\Node\ClassNode;
use PHPMD\Rule\ClassAware;

/**
 * Session and Cookies must be used only in HTML Presentation layer.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CookieAndSessionMisuse extends AbstractRule implements ClassAware
{
    use GetParameterClassTrait;

    /**
     * Is given class a controller?
     *
     * @param \ReflectionClass $class
     * @return bool
     */
    private function isController(\ReflectionClass $class): bool
    {
        return $class->isSubclassOf(\Magento\Framework\App\ActionInterface::class);
    }

    /**
     * Is given class a block?
     *
     * @param \ReflectionClass $class
     * @return bool
     */
    private function isBlock(\ReflectionClass $class): bool
    {
        return $class->isSubclassOf(\Magento\Framework\View\Element\BlockInterface::class);
    }

    /**
     * Is given class an HTML UI data provider?
     *
     * @param \ReflectionClass $class
     * @return bool
     */
    private function isUiDataProvider(\ReflectionClass $class): bool
    {
        return $class->isSubclassOf(
            \Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface::class
        );
    }

    /**
     * Is given class a Layout Processor?
     *
     * @param \ReflectionClass $class
     * @return bool
     */
    private function isLayoutProcessor(\ReflectionClass $class): bool
    {
        return $class->isSubclassOf(
            \Magento\Checkout\Block\Checkout\LayoutProcessorInterface::class
        );
    }

    /**
     * Is given class a View Model?
     *
     * @param \ReflectionClass $class
     * @return bool
     */
    private function isViewModel(\ReflectionClass $class): bool
    {
        return $class->isSubclassOf(
            \Magento\Framework\View\Element\Block\ArgumentInterface::class
        );
    }

    /**
     * Is given class an HTML UI Document?
     *
     * @param \ReflectionClass $class
     * @return bool
     */
    private function isUiDocument(\ReflectionClass $class): bool
    {
        return $class->isSubclassOf(\Magento\Framework\View\Element\UiComponent\DataProvider\Document::class)
            || $class->getName() === \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class;
    }

    /**
     * Is given class a plugin for controllers?
     *
     * @param \ReflectionClass $class
     * @return bool
     */
    private function isControllerPlugin(\ReflectionClass $class): bool
    {
        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (preg_match('/^(after|around|before).+/i', $method->getName())) {
                try {
                    $parameters = $method->getParameters();
                    if (count($parameters) === 0) {
                        continue;
                    }
                    $argument = $this->getParameterClass($parameters[0]);
                } catch (\Throwable $exception) {
                    //Non-existing class (autogenerated perhaps) or doesn't have an argument.
                    continue;
                }
                if ($argument) {
                    $isAction = $argument->isSubclassOf(\Magento\Framework\App\ActionInterface::class)
                        || $argument->getName() === \Magento\Framework\App\ActionInterface::class;
                    if ($isAction) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Is given class a plugin for blocks?
     *
     * @param \ReflectionClass $class
     * @return bool
     */
    private function isBlockPlugin(\ReflectionClass $class): bool
    {
        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (preg_match('/^(after|around|before).+/i', $method->getName())) {
                try {
                    $parameters = $method->getParameters();
                    if (count($parameters) === 0) {
                        continue;
                    }
                    $argument = $this->getParameterClass($parameters[0]);
                } catch (\Throwable $exception) {
                    //Non-existing class (autogenerated perhaps) or doesn't have an argument.
                    continue;
                }
                if ($argument) {
                    $isBlock = $argument->isSubclassOf(\Magento\Framework\View\Element\BlockInterface::class)
                        || $argument->getName() === \Magento\Framework\View\Element\BlockInterface::class;
                    if ($isBlock) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Whether given class depends on classes to pay attention to.
     *
     * @param \ReflectionClass $class
     * @return bool
     */
    private function doesUseRestrictedClasses(\ReflectionClass $class): bool
    {
        $constructor = $class->getConstructor();
        if ($constructor) {
            foreach ($constructor->getParameters() as $argument) {
                try {
                    $class = $this->getParameterClass($argument);
                    if ($class === null) {
                        continue;
                    }
                    if ($class->isSubclassOf(SessionManagerInterface::class)
                        || $class->getName() === SessionManagerInterface::class
                        || $class->isSubclassOf(CookieReaderInterface::class)
                        || $class->getName() === CookieReaderInterface::class
                    ) {
                        return true;
                    }
                } catch (\ReflectionException $exception) {
                    //Failed to load the argument's class information
                    continue;
                }
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     *
     * @param ClassNode|ASTClass $node
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function apply(AbstractNode $node): void
    {
        try {
            $class = new \ReflectionClass($node->getFullQualifiedName());
        } catch (\Throwable $exception) {
            //Failed to load class, nothing we can do
            return;
        }

        if ($this->doesUseRestrictedClasses($class)) {
            if (!$this->isController($class)
                && !$this->isBlock($class)
                && !$this->isUiDataProvider($class)
                && !$this->isUiDocument($class)
                && !$this->isControllerPlugin($class)
                && !$this->isBlockPlugin($class)
                && !$this->isLayoutProcessor($class)
                && !$this->isViewModel($class)
            ) {
                $this->addViolation($node, [$node->getFullQualifiedName()]);
            }
        }
    }
}
