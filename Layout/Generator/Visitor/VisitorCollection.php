<?php

namespace Oro\Bundle\LayoutBundle\Layout\Generator\Visitor;

use Oro\Component\Layout\Exception\UnexpectedTypeException;

class VisitorCollection extends \ArrayIterator
{
    /**
     * @param array $conditions
     */
    public function __construct(array $conditions = [])
    {
        $this->validate($conditions);

        parent::__construct($conditions);
    }

    /**
     * {@inheritdoc}
     */
    public function append($condition)
    {
        $this->validate([$condition]);

        parent::append($condition);
    }

    /**
     * @param array $conditions
     */
    protected function validate(array $conditions)
    {
        foreach ($conditions as $condition) {
            if (!$condition instanceof VisitorInterface) {
                throw new UnexpectedTypeException(
                    $condition,
                    'Oro\Bundle\LayoutBundle\Layout\Generator\Visitor\VisitorInterface'
                );
            }
        }
    }
}
