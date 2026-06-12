# PUGXBusFormBundle Documentation

## 1. Installation

``` bash
$ composer require pugx/bus-form-bundle symfony/messenger
```

## 2. Configuration

No configuration is needed. Flex is taking care of that for you.

## 3. Usage

In your forms that are bound to a Command, extends one of bundle's forms instead of Symfony one.

Example:

```php
<?php

namespace App\Form;

use MyDomain\Command\DoSomethingCommand;
use PUGX\BusFormBundle\Form\Messenger\AbstractBusType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FooType extends AbstractBusType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // it's very important to call parent constructor, otherwise this won't work!
        parent::buildForm($builder, $options);
        $builder
            ->add('bar')
            ->add('baz')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DoSomethingCommand::class,
        ]);
    }
}
```

Now, your controller doesn't need to dispatch the Command any more.
The Command is dispatched by the form.

Example:

```php
<?php

use App\Form\FooType;
use MyDomain\Command\DoSomethingCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

final class FooController extends AbstractController
{
    // before
    public function doSomething(MessageBusInterface $bus, Request $request)
    {
        $command = new DoSomethingCommand();
        $form = $this->createForm(FooType::class, $command)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $bus->dispatch($command);

            return $this->redirectToRoute('some_route');
        }

        return $this->render('do/something.html.twig', ['form' => $form]);
    }

    // after
    public function doSomething(Request $request)
    {
        $form = $this->createForm(FooType::class, new DoSomethingCommand())->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // look ma, no command dispatching needed!
            return $this->redirectToRoute('some_route');
        }

        return $this->render('do/something.html.twig', ['form' => $form]);
    }
}

```

Also, if your handler throws a `\DomainException` or an `\InvalidArgumentException`, such exceptions
are caught and transformed into a form error.


## 4. Direct use

Sometimes it happens that your command is not bound to a form, but you want to use a form anyway to handle it.
Usually, you would build an anonymous form directly in your controller. With this bundle, you can instead
use `BusType`. This case can be applied only when your command doesn't need any dynamic value to be
assigned.

Then, in your controller, you can do something like the following:

```php
<?php

use MyDomain\Command\DoSomethingCommand;
use PUGX\BusFormBundle\Form\Messenger\BusType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

final class FooController extends AbstractController
{
    public function doSomething(Request $request)
    {
        $form = $this->createForm(BusType::class, new DoSomethingCommand())->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('some_route');
        }

        return $this->render('do/something.html.twig', ['form' => $form]);
    }
}

```
