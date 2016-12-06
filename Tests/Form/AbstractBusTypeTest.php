<?php

namespace PUGX\BusFormBundle\Tests\Form\Type;

use PUGX\BusFormBundle\Tests\BusFormTypeStub;

class AbstractBusTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildForm()
    {
        $bus = $this->getMockBuilder('SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware')->disableOriginalConstructor()->getMock();
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')->disableOriginalConstructor()->getMock();

        $builder->expects($this->once())->method('addEventListener');

        $type = new BusFormTypeStub($bus);
        $type->buildForm($builder, ['data' => 'Foo']);
    }

    public function testHandleValidForm()
    {
        $bus = $this->getMockBuilder('SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware')->disableOriginalConstructor()->getMock();
        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')->disableOriginalConstructor()->getMock();
        $form = $this->getMockBuilder('Symfony\Component\Form\FormInterface')->getMock();
        $fooCommand = new \stdClass();

        $event->expects($this->once())->method('getForm')->will($this->returnValue($form));
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $bus->expects($this->once())->method('handle');

        $type = new BusFormTypeStub($bus);
        $type->handle($event, $fooCommand);
    }

    public function testHandleInvalidForm()
    {
        $bus = $this->getMockBuilder('SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware')->disableOriginalConstructor()->getMock();
        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')->disableOriginalConstructor()->getMock();
        $form = $this->getMockBuilder('Symfony\Component\Form\FormInterface')->getMock();
        $fooCommand = new \stdClass();

        $event->expects($this->once())->method('getForm')->will($this->returnValue($form));
        $form->expects($this->once())->method('isValid')->will($this->returnValue(false));
        $bus->expects($this->never())->method('handle');

        $type = new BusFormTypeStub($bus);
        $type->handle($event, $fooCommand);
    }

    public function testHandleException()
    {
        $bus = $this->getMockBuilder('SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware')->disableOriginalConstructor()->getMock();
        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')->disableOriginalConstructor()->getMock();
        $form = $this->getMockBuilder('Symfony\Component\Form\FormInterface')->getMock();
        $fooCommand = new \stdClass();

        $event->expects($this->exactly(2))->method('getForm')->will($this->returnValue($form));
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('addError');
        $bus->expects($this->once())->method('handle')->will($this->throwException(new \DomainException()));

        $type = new BusFormTypeStub($bus);
        $type->handle($event, $fooCommand);
    }
}
