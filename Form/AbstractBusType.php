<?php

namespace PUGX\BusFormBundle\Form;

use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware as Bus;
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

    /**
     * @param Bus $bus
     */
    public function __construct(Bus $bus)
    {
        $this->bus = $bus;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($options) {
            $this->handle($event, $options['data']);
        });
    }

    /**
     * @param FormEvent $event
     * @param mixed     $command
     */
    public function handle(FormEvent $event, $command)
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
