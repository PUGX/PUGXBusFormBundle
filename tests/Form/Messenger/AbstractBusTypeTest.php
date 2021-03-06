<?php

namespace PUGX\BusFormBundle\Tests\Form\Type\Messenger;

use PHPUnit\Framework\TestCase;
use PUGX\BusFormBundle\Tests\BusFormTypeMessengerStub;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class AbstractBusTypeTest extends TestCase
{
    public function testBuildForm(): void
    {
        $bus = $this->getMockBuilder(MessageBusInterface::class)->disableOriginalConstructor()->getMock();
        $builder = $this->getMockBuilder(FormBuilder::class)->disableOriginalConstructor()->getMock();

        $builder->expects(self::once())->method('addEventListener');

        $type = new BusFormTypeMessengerStub($bus);
        $type->buildForm($builder, ['data' => 'Foo']);
    }

    public function testHandleValidForm(): void
    {
        $bus = $this->getMockBuilder(MessageBusInterface::class)->disableOriginalConstructor()->getMock();
        $event = $this->getMockBuilder(FormEvent::class)->disableOriginalConstructor()->getMock();
        $form = $this->getMockBuilder(FormInterface::class)->getMock();
        $fooCommand = new \stdClass();

        $event->expects(self::once())->method('getForm')->willReturn($form);
        $form->expects(self::once())->method('isValid')->willReturn(true);
        $bus->expects(self::once())->method('dispatch')->willReturn(new Envelope($fooCommand));

        $type = new BusFormTypeMessengerStub($bus);
        $type->handle($event, $fooCommand);
    }

    public function testHandleInvalidForm(): void
    {
        $bus = $this->getMockBuilder(MessageBusInterface::class)->disableOriginalConstructor()->getMock();
        $event = $this->getMockBuilder(FormEvent::class)->disableOriginalConstructor()->getMock();
        $form = $this->getMockBuilder(FormInterface::class)->getMock();
        $fooCommand = new \stdClass();

        $event->expects(self::once())->method('getForm')->willReturn($form);
        $form->expects(self::once())->method('isValid')->willReturn(false);
        $bus->expects(self::never())->method('dispatch')->willReturn(new Envelope($fooCommand));

        $type = new BusFormTypeMessengerStub($bus);
        $type->handle($event, $fooCommand);
    }

    public function testHandleException(): void
    {
        $bus = $this->getMockBuilder(MessageBusInterface::class)->disableOriginalConstructor()->getMock();
        $event = $this->getMockBuilder(FormEvent::class)->disableOriginalConstructor()->getMock();
        $form = $this->getMockBuilder(FormInterface::class)->getMock();
        $fooCommand = new \stdClass();

        $event->expects(self::exactly(2))->method('getForm')->willReturn($form);
        $form->expects(self::once())->method('isValid')->willReturn(true);
        $form->expects(self::once())->method('addError');
        $bus->expects(self::once())->method('dispatch')->will(self::throwException(new \DomainException()));

        $type = new BusFormTypeMessengerStub($bus);
        $type->handle($event, $fooCommand);
    }
}
