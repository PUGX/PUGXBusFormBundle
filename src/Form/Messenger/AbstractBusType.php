<?php

namespace PUGX\BusFormBundle\Form\Messenger;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface as Bus;

abstract class AbstractBusType extends AbstractType
{
    public function __construct(private Bus $bus)
    {
    }

    /**
     * @param FormBuilderInterface<AbstractType> $builder
     * @param array<string, mixed>               $options
     */
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
            $this->bus->dispatch($command);
        } catch (HandlerFailedException $exception) {
            $prev = $exception->getPrevious();
            if ($prev instanceof \DomainException || $prev instanceof \InvalidArgumentException) {
                $event->getForm()->addError(new FormError($prev->getMessage(), null, [], null, $prev));
            } elseif (null !== $prev) {
                throw $prev;
            }
        } catch (\DomainException|\InvalidArgumentException $exception) {
            $event->getForm()->addError(new FormError($exception->getMessage(), null, [], null, $exception));
        }
    }
}
