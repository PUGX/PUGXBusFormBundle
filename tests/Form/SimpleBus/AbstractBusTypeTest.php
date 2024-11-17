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
        $bus = $this->createMock(MessageBusSupportingMiddleware::class);
        $builder = $this->createMock(FormBuilder::class);

        $builder->expects($this->once())->method('addEventListener');

        $type = new BusFormTypeStub($bus);
        $type->buildForm($builder, ['data' => new \stdClass()]);
    }

    public function testHandleValidForm(): void
    {
        $bus = $this->createMock(MessageBusSupportingMiddleware::class);
        $event = $this->createMock(FormEvent::class);
        $form = $this->createMock(FormInterface::class);
        $fooCommand = new \stdClass();

        $event->expects($this->once())->method('getForm')->willReturn($form);
        $form->expects($this->once())->method('isValid')->willReturn(true);
        $bus->expects($this->once())->method('handle');

        $type = new BusFormTypeStub($bus);
        $type->handle($event, $fooCommand);
    }

    public function testHandleInvalidForm(): void
    {
        $bus = $this->createMock(MessageBusSupportingMiddleware::class);
        $event = $this->createMock(FormEvent::class);
        $form = $this->createMock(FormInterface::class);
        $fooCommand = new \stdClass();

        $event->expects($this->once())->method('getForm')->willReturn($form);
        $form->expects($this->once())->method('isValid')->willReturn(false);
        $bus->expects($this->never())->method('handle');

        $type = new BusFormTypeStub($bus);
        $type->handle($event, $fooCommand);
    }

    public function testHandleException(): void
    {
        $bus = $this->createMock(MessageBusSupportingMiddleware::class);
        $event = $this->createMock(FormEvent::class);
        $form = $this->createMock(FormInterface::class);
        $fooCommand = new \stdClass();

        $event->expects($this->exactly(2))->method('getForm')->willReturn($form);
        $form->expects($this->once())->method('isValid')->willReturn(true);
        $form->expects($this->once())->method('addError');
        $bus->expects($this->once())->method('handle')->will($this->throwException(new \DomainException()));

        $type = new BusFormTypeStub($bus);
        $type->handle($event, $fooCommand);
    }
}
