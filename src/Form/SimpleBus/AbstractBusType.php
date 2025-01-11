<?php

namespace PUGX\BusFormBundle\Form\SimpleBus;

use SimpleBus\Message\Bus\MessageBus as Bus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

abstract class AbstractBusType extends AbstractType
{
    public function __construct(private readonly Bus $bus)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (isset($options['data']) && \is_object($options['data'])) {
            $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options): void {
                $this->handle($event, $options['data']);
            });
        }
    }

    public function handle(FormEvent $event, object $command): void
    {
        if (!$event->getForm()->isValid()) {
            return;
        }
        try {
            $this->bus->handle($command);
        } catch (\DomainException|\InvalidArgumentException $exception) {
            $event->getForm()->addError(new FormError($exception->getMessage(), null, [], null, $exception));
        }
    }
}
