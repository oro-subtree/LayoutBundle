<?php

namespace Oro\Bundle\LayoutBundle\Layout\Block\Extension;

use Oro\Component\Layout\Block\OptionsResolver\OptionsResolver;
use Oro\Component\Layout\AbstractBlockTypeExtension;
use Oro\Component\Layout\Action;
use Oro\Component\Layout\ArrayOptionValueBuilder;
use Oro\Component\Layout\Block\Type\BaseType;
use Oro\Component\Layout\BlockInterface;
use Oro\Component\Layout\BlockView;
use Oro\Component\Layout\ContextInterface;
use Oro\Component\Layout\DataAccessorInterface;
use Oro\Component\Layout\OptionValueBag;
use Oro\Component\Layout\OptionValueBuilderInterface;
use Oro\Component\Layout\StringOptionValueBuilder;
use Oro\Component\Layout\Block\Type\Options;

/**
 * Automatically converts OptionValueBag to an appropriate data representation
 */
class OptionValueBagExtension extends AbstractBlockTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['resolve_value_bags' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function normalizeOptions(Options $options, ContextInterface $context, DataAccessorInterface $data)
    {
        if ($options['resolve_value_bags'] && !$this->checkExpression($options)) {
            $this->resolveValueBags($options);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(BlockView $view, BlockInterface $block, Options $options)
    {
        $exprEvaluate = $block->getContext()->getOr('expressions_evaluate');
        if ($options['resolve_value_bags'] && $exprEvaluate) {
            $this->resolveValueBags($view->vars);
        } elseif (!$exprEvaluate) {
            throw new \InvalidArgumentException('expression should be evaluate');
        }
    }

    /**
     * @param $options
     * @return bool
     */
    protected function checkExpression($options)
    {
        $check = false;
        foreach ($options as $value) {
            if (is_string($value) && strpos($value, '=data') !== false) {
                return true;
            }
        }
        return $check;
    }

    /**
     * @param array $options
     * @return array
     */
    protected function resolveValueBags(array &$options)
    {
        foreach ($options as $key => $value) {
            if (is_array($value)) {
                $options[$key] = $this->resolveValueBags($value);
            } elseif ($value instanceof OptionValueBag) {
                $options[$key] = $value->buildValue($this->getOptionsBuilder($value, $options));
            }
        }

        return $options;
    }

    /**
     * @param OptionValueBag $valueBag
     * @param Options $options
     * @return OptionValueBuilderInterface
     */
    protected function getOptionsBuilder(OptionValueBag $valueBag, Options $options)
    {
        $isArray = false;

        // guess builder type based on arguments
        $actions = $valueBag->all();
        if ($actions) {
            /** @var Action $action */
            $action = reset($actions);
            $arguments = $action->getArguments();
            if ($arguments) {
                $argument = reset($arguments);
                if (is_array($argument)) {
                    $isArray = true;
                }
            }
        }

        if ($isArray) {
            return new ArrayOptionValueBuilder();
        }

        return new StringOptionValueBuilder();
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return BaseType::NAME;
    }
}
