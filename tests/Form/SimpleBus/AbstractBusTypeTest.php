<?php

namespace PUGX\BusFormBundle\Tests\Form\Type\SimpleBus;

use PHPUnit\Framework\TestCase;
use PUGX\BusFormBundle\Tests\BusFormTypeStub;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

final class AbstractBusTypeTest extends TestCase
{
    public function testBuildForm(): void
    {
        $bus = $this->getMockBuilder(MessageBusSupportingMiddleware::class)->disableOriginalConstructor()->getMock();
        $builder = $this->getMockBuilder(FormBuilder::class)->disableOriginalConstructor()->getMock();

        $builder->expects(self::once())->method('addEventListener');

        $type = new BusFormTypeStub($bus);
        $type->buildForm($builder, ['data' => new \stdClass()]);
    }

    public function testHandleValidForm(): void
    {
        $bus = $this->getMockBuilder(MessageBusSupportingMiddleware::class)->disableOriginalConstructor()->getMock();
        $event = $this->getMockBuilder(FormEvent::class)->disableOriginalConstructor()->getMock();
        $form = $this->getMockBuilder(FormInterface::class)->getMock();
        $fooCommand = new \stdClass();

        $event->expects(self::once())->method('getForm')->willReturn($form);
        $form->expects(self::once())->method('isValid')->willReturn(true);
        $bus->expects(self::once())->method('handle');

        $type = new BusFormTypeStub($bus);
        $type->handle($event, $fooCommand);
    }

    public function testHandleInvalidForm(): void
    {
        $bus = $this->getMockBuilder(MessageBusSupportingMiddleware::class)->disableOriginalConstructor()->getMock();
        $event = $this->getMockBuilder(FormEvent::class)->disableOriginalConstructor()->getMock();
        $form = $this->getMockBuilder(FormInterface::class)->getMock();
        $fooCommand = new \stdClass();

        $event->expects(self::once())->method('getForm')->willReturn($form);
        $form->expects(self::once())->method('isValid')->willReturn(false);
        $bus->expects(self::never())->method('handle');

        $type = new BusFormTypeStub($bus);
        $type->handle($event, $fooCommand);
    }

    public function testHandleException(): void
    {
        $bus = $this->getMockBuilder(MessageBusSupportingMiddleware::class)->disableOriginalConstructor()->getMock();
        $event = $this->getMockBuilder(FormEvent::class)->disableOriginalConstructor()->getMock();
        $form = $this->getMockBuilder(FormInterface::class)->getMock();
        $fooCommand = new \stdClass();

        $event->expects(self::exactly(2))->method('getForm')->willReturn($form);
        $form->expects(self::once())->method('isValid')->willReturn(true);
        $form->expects(self::once())->method('addError');
        $bus->expects(self::once())->method('handle')->will(self::throwException(new \DomainException()));

        $type = new BusFormTypeStub($bus);
        $type->handle($event, $fooCommand);
    }
}
