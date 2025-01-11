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
        $bus = $this->createMock(MessageBusInterface::class);
        $builder = $this->createMock(FormBuilder::class);

        $builder->expects($this->once())->method('addEventListener');

        $type = new BusFormTypeMessengerStub($bus);
        $type->buildForm($builder, ['data' => new \stdClass()]);
    }

    public function testHandleValidForm(): void
    {
        $bus = $this->createMock(MessageBusInterface::class);
        $event = $this->createMock(FormEvent::class);
        $form = $this->createMock(FormInterface::class);
        $fooCommand = new \stdClass();

        $event->expects($this->once())->method('getForm')->willReturn($form);
        $form->expects($this->once())->method('isValid')->willReturn(true);
        $bus->expects($this->once())->method('dispatch')->willReturn(new Envelope($fooCommand));

        $type = new BusFormTypeMessengerStub($bus);
        $type->handle($event, $fooCommand);
    }

    public function testHandleInvalidForm(): void
    {
        $bus = $this->createMock(MessageBusInterface::class);
        $event = $this->createMock(FormEvent::class);
        $form = $this->createMock(FormInterface::class);
        $fooCommand = new \stdClass();

        $event->expects($this->once())->method('getForm')->willReturn($form);
        $form->expects($this->once())->method('isValid')->willReturn(false);
        $bus->expects($this->never())->method('dispatch')->willReturn(new Envelope($fooCommand));

        $type = new BusFormTypeMessengerStub($bus);
        $type->handle($event, $fooCommand);
    }

    public function testHandleException(): void
    {
        $bus = $this->createMock(MessageBusInterface::class);
        $event = $this->createMock(FormEvent::class);
        $form = $this->createMock(FormInterface::class);
        $fooCommand = new \stdClass();

        $event->expects($this->exactly(2))->method('getForm')->willReturn($form);
        $form->expects($this->once())->method('isValid')->willReturn(true);
        $form->expects($this->once())->method('addError');
        $bus->expects($this->once())->method('dispatch')->will($this->throwException(new \DomainException()));

        $type = new BusFormTypeMessengerStub($bus);
        $type->handle($event, $fooCommand);
    }
}
