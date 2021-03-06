PUGXBusFormBundle Documentation
===============================

## 1. Installation

``` bash
$ composer require pugx/bus-form-bundle
```

You also need to require `simple-bus/message-bus` or `symfony/messenger`.

## 2. Configuration

No configuration is needed. Flex is taking care of that for you.

## 3. Usage

In your forms that are bound to a Command, extends one of bundle's forms instead of Symfony one.

Example with SimpleBus (usage with Messenger is pretty similar):

```php
<?php

namespace App\Form;

use MyDomain\Command\DoSomethingCommand;
use PUGX\BusFormBundle\SimpleBus\AbstractBusType;
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

If you don't use autowiring, you'll need to declare your form as a service, injecting the proper service.

```yaml
# config/services.yaml

App\Form\FooType:
    arguments: ['@command_bus'] # or ['@Symfony\Component\Messenger\MessageBusInterface']
    tags: [form.type]
```

Now, your controller doesn't need to handle/dispatch the Command any more.
The Command is handled/dispatched by the form.

Example:

```php
<?php

use App\Form\FooType;
use MyDomain\Command\DoSomethingCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

final class FooController extends AbstractController
{
    // before
    public function doSomethingAction(Request $request)
    {
        $form = $this->createForm(FooType::class, new DoSomethingCommand());
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('command_bus')->handle($command);

            return $this->redirectToRoute('some_route');
        }

        return $this->render('do/something.html.twig', ['form' => $form->createView()]);
    }

    // after
    public function doSomethingAction(Request $request)
    {
        $form = $this->createForm(FooType::class, new DoSomethingCommand())->handleRequest($request);
        if ($formisSubmitted() && $form->isValid()) {
            // look ma, no command handling needed!
            return $this->redirectToRoute('some_route');
        }

        return $this->render('do/something.html.twig', ['form' => $form->createView()]);
    }
}

```

Also, if your handler is throwing a `\DomainException` or an `\InvalidArgumentException`, such exceptions
are caught and transformed into a form error (and logged as well).


## 4. Direct use

Sometimes it happens that your command is not bound to a form, but you want to use a form anyway to handle it.
Usually, you would build an anonymous form directly in your controller. With this bundle, you can instead
use `BusType`. Of course, this case can be applied only when your command is not needing any dynamic value to be
assigned.

If you don't use autowiring, you'll need to declare `BusType` as a service (only once):

```yaml
# config/services.yaml

PUGX\BusFormBundle\Form\SimpleBus\BusType: ~
```

Or, if you still old system (not autowired/autoconfigured):

```yaml
# config/services.yaml

app.form.baz:
    class: PUGX\BusFormBundle\Form\SimpleBus\BusType
    arguments: ['@command_bus']
    tags: [form.type]
```
Then, in your controller, you can do something like the following:

```php
<?php

use MyDomain\Command\DoSomethingCommand;
use PUGX\BusFormBundle\Form\BusType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

final class FooController extends AbstractController
{
    public function doSomethingAction(Request $request)
    {
        $form = $this->createForm(BusType::class, new DoSomethingCommand())->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('some_route');
        }

        return $this->render('do/something.html.twig', ['form' => $form->createView()]);
    }
}

```
