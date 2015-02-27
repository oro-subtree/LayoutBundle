<?php

namespace Oro\Bundle\LayoutBundle\Tests\Unit\Layout\Block\Type;

use Oro\Component\Layout\Block\Type\ContainerType;

use Oro\Bundle\LayoutBundle\Layout\Block\Type\FormType;
use Oro\Bundle\LayoutBundle\Tests\Unit\BlockTypeTestCase;
use Oro\Component\Layout\BlockView;
use Oro\Component\Layout\LayoutContext;
use Symfony\Component\Form\FormView;

class FormTypeTest extends BlockTypeTestCase
{
    /**
     * @dataProvider optionsDataProvider
     */
    public function testSetDefaultOptions($options, $expected)
    {
        $resolvedOptions = $this->resolveOptions(FormType::NAME, $options);
        $this->assertEquals($expected, $resolvedOptions);
    }

    public function optionsDataProvider()
    {
        return [
            'no options'     => [
                'options'  => [],
                'expected' => [
                    'form_name'         => 'form',
                    'preferred_fields'  => [],
                    'groups'            => [],
                    'form_field_prefix' => 'form_',
                    'form_group_prefix' => 'form:group_'
                ]
            ],
            'with form_name' => [
                'options'  => [
                    'form_name' => 'test'
                ],
                'expected' => [
                    'form_name'         => 'test',
                    'preferred_fields'  => [],
                    'groups'            => [],
                    'form_field_prefix' => 'test_',
                    'form_group_prefix' => 'test:group_'
                ]
            ],
            'all options'    => [
                'options'  => [
                    'form_name'         => 'test',
                    'preferred_fields'  => ['field1'],
                    'groups'            => ['group1' => ['title' => 'TestGroup']],
                    'form_field_prefix' => 'form_field_prefix_',
                    'form_group_prefix' => 'form_group_prefix_'
                ],
                'expected' => [
                    'form_name'         => 'test',
                    'preferred_fields'  => ['field1'],
                    'groups'            => ['group1' => ['title' => 'TestGroup']],
                    'form_field_prefix' => 'form_field_prefix_',
                    'form_group_prefix' => 'form_group_prefix_'
                ]
            ]
        ];
    }

    public function testBuildBlockWithForm()
    {
        $formName = 'test_form';

        $form = $this->getMock('Symfony\Component\Form\Test\FormInterface');

        $this->context->set($formName, $form);

        $builder = $this->getMock('Oro\Component\Layout\BlockBuilderInterface');
        $builder->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($this->context));

        $formLayoutBuilder = $this->getMock('Oro\Bundle\LayoutBundle\Layout\Form\FormLayoutBuilderInterface');

        $type    = new FormType($formLayoutBuilder);
        $options = $this->resolveOptions(
            $type,
            ['form_name' => $formName]
        );

        $formLayoutBuilder->expects($this->once())
            ->method('build')
            ->with(
                $this->isInstanceOf('Oro\Bundle\LayoutBundle\Layout\Form\FormAccessor'),
                $this->identicalTo($builder),
                $options
            );

        $type->buildBlock($builder, $options);

        $this->assertInstanceOf(
            'Oro\Bundle\LayoutBundle\Layout\Form\FormAccessor',
            $this->context->get($formName)
        );
    }

    public function testBuildBlockWithFormAccessor()
    {
        $formName = 'test_form';

        $formAccessor = $this->getMock('Oro\Bundle\LayoutBundle\Layout\Form\FormAccessorInterface');

        $this->context->set($formName, $formAccessor);

        $builder = $this->getMock('Oro\Component\Layout\BlockBuilderInterface');
        $builder->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($this->context));

        $formLayoutBuilder = $this->getMock('Oro\Bundle\LayoutBundle\Layout\Form\FormLayoutBuilderInterface');

        $type    = new FormType($formLayoutBuilder);
        $options = $this->resolveOptions(
            $type,
            ['form_name' => $formName]
        );

        $formLayoutBuilder->expects($this->once())
            ->method('build')
            ->with($this->identicalTo($formAccessor), $this->identicalTo($builder), $options);

        $type->buildBlock($builder, $options);

        $this->assertSame(
            $formAccessor,
            $this->context->get($formName)
        );
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Undefined index: test_form.
     */
    public function testBuildBlockWithoutForm()
    {
        $builder = $this->getMock('Oro\Component\Layout\BlockBuilderInterface');
        $builder->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($this->context));

        $type    = $this->getBlockType(FormType::NAME);
        $options = $this->resolveOptions(
            $type,
            ['form_name' => 'test_form']
        );
        $type->buildBlock($builder, $options);
    }

    // @codingStandardsIgnoreStart
    /**
     * @expectedException \Oro\Component\Layout\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Invalid "context[test_form]" argument type. Expected "Symfony\Component\Form\FormInterface or Oro\Bundle\LayoutBundle\Layout\Form\FormAccessorInterface", "integer" given.
     */
    // @codingStandardsIgnoreEnd
    public function testBuildBlockWithInvalidForm()
    {
        $formName = 'test_form';

        $this->context->set($formName, 123);

        $builder = $this->getMock('Oro\Component\Layout\BlockBuilderInterface');
        $builder->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($this->context));

        $type    = $this->getBlockType(FormType::NAME);
        $options = $this->resolveOptions(
            $type,
            ['form_name' => $formName]
        );
        $type->buildBlock($builder, $options);
    }

    public function testBuildView()
    {
        $formLayoutBuilder = $this->getMock('Oro\Bundle\LayoutBundle\Layout\Form\FormLayoutBuilderInterface');
        $type              = new FormType($formLayoutBuilder);

        $formName     = 'form';
        $view         = new BlockView();
        $block        = $this->getMock('Oro\Component\Layout\BlockInterface');
        $formAccessor = $this->getMock('Oro\Bundle\LayoutBundle\Layout\Form\FormAccessorInterface');
        $context      = new LayoutContext();
        $formView     = new FormView();

        $context->set('form', $formAccessor);

        $block->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($context));
        $formAccessor->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($formView));

        $type->buildView($view, $block, ['form_name' => $formName]);
        $this->assertSame($formView, $view->vars['form']);
    }

    public function testFinishView()
    {
        $formLayoutBuilder = $this->getMock('Oro\Bundle\LayoutBundle\Layout\Form\FormLayoutBuilderInterface');
        $type              = new FormType($formLayoutBuilder);

        $formName           = 'form';
        $rootView           = new BlockView();
        $view               = new BlockView($rootView);
        $block              = $this->getMock('Oro\Component\Layout\BlockInterface');
        $formAccessor       = $this->getMock('Oro\Bundle\LayoutBundle\Layout\Form\FormAccessorInterface');
        $context            = new LayoutContext();
        $formView           = new FormView();
        $view->vars['form'] = $formView;

        $view->children['block1']     = new BlockView($view);
        $rootView->children['block3'] = new BlockView($rootView);

        $formView->children['field1']    = new FormView($formView);
        $formView->children['field2']    = new FormView($formView);
        $field3View                      = new FormView($formView);
        $formView->children['field3']    = $field3View;
        $field3View->children['field31'] = new FormView($field3View);
        $field3View->children['field32'] = new FormView($field3View);

        $context->set('form', $formAccessor);

        $block->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($context));
        $formAccessor->expects($this->once())
            ->method('getProcessedFields')
            ->will(
                $this->returnValue(
                    [
                        'field1'         => 'block1',
                        'field2'         => 'block2',
                        'field3.field31' => 'block3',
                        'field3.field32' => 'block4'
                    ]
                )
            );

        $type->finishView($view, $block, ['form_name' => $formName]);

        $this->assertFalse($formView->isRendered());
        $this->assertFalse($formView['field1']->isRendered());
        $this->assertTrue($formView['field2']->isRendered());
        $this->assertFalse($formView['field3']['field31']->isRendered());
        $this->assertTrue($formView['field3']['field32']->isRendered());
    }

    public function testFinishViewWhenFormBlockIsRoot()
    {
        $formLayoutBuilder = $this->getMock('Oro\Bundle\LayoutBundle\Layout\Form\FormLayoutBuilderInterface');
        $type              = new FormType($formLayoutBuilder);

        $formName           = 'form';
        $view               = new BlockView();
        $block              = $this->getMock('Oro\Component\Layout\BlockInterface');
        $formAccessor       = $this->getMock('Oro\Bundle\LayoutBundle\Layout\Form\FormAccessorInterface');
        $context            = new LayoutContext();
        $formView           = new FormView();
        $view->vars['form'] = $formView;

        $view->children['block1'] = new BlockView($view);
        $view->children['block3'] = new BlockView($view);

        $formView->children['field1']    = new FormView($formView);
        $formView->children['field2']    = new FormView($formView);
        $field3View                      = new FormView($formView);
        $formView->children['field3']    = $field3View;
        $field3View->children['field31'] = new FormView($field3View);
        $field3View->children['field32'] = new FormView($field3View);

        $context->set('form', $formAccessor);

        $block->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($context));
        $formAccessor->expects($this->once())
            ->method('getProcessedFields')
            ->will(
                $this->returnValue(
                    [
                        'field1'         => 'block1',
                        'field2'         => 'block2',
                        'field3.field31' => 'block3',
                        'field3.field32' => 'block4'
                    ]
                )
            );

        $type->finishView($view, $block, ['form_name' => $formName]);

        $this->assertFalse($formView->isRendered());
        $this->assertFalse($formView['field1']->isRendered());
        $this->assertTrue($formView['field2']->isRendered());
        $this->assertFalse($formView['field3']['field31']->isRendered());
        $this->assertTrue($formView['field3']['field32']->isRendered());
    }

    public function testGetName()
    {
        $type = $this->getBlockType(FormType::NAME);

        $this->assertSame(FormType::NAME, $type->getName());
    }

    public function testGetParent()
    {
        $type = $this->getBlockType(FormType::NAME);

        $this->assertSame(ContainerType::NAME, $type->getParent());
    }
}
