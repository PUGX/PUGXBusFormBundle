<?php

namespace PUGX\BusFormBundle\Form;

use SimpleBus\Message\Bus\MessageBus as Bus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

abstract class AbstractBusType extends AbstractType
{
    /**
     * @var Bus
     */
    private $bus;

    public function __construct(Bus $bus)
    {
        $this->bus = $bus;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (isset($options['data'])) {
            $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options): void {
                $this->handle($event, $options['data']);
            });
        }
    }

    public function handle(FormEvent $event, $command): void
    {
        if (!$event->getForm()->isValid()) {
            return;
        }
        try {
            $this->bus->handle($command);
        } catch (\DomainException $exception) {
            $event->getForm()->addError(new FormError($exception->getMessage()));
        }
    }
}
