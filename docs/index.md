PUGXBusFormBundle Documentation
===============================

## 1. Installation

``` bash
$ composer require pugx/bus-form-bundle
```

You likey want to install [SymfonyBridge](https://github.com/SimpleBus/SymfonyBridge).

## 2. Configuration

If you don't use Flex, you'll need to enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = [
        // ...
        new SimpleBus\SymfonyBridge\SimpleBusCommandBusBundle(),
        new PUGX\BusFormBundle\PUGXBusFormBundle(),
    ];
}
```

## 3. Usage

In your forms that are bound to a Command, extends bundle's form instead of Symfony one.

Example:

```php
<?php

namespace App\Form;

use MyDomain\Command\DoSomethingCommand;
use PUGX\BusFormBundle\AbstractBusType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FooType extends AbstractBusType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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

If you don't use autowiring, you'll need to declare your form as a service, injecting the `command_bus` service.

```yaml
# app/config/services.yml

app.form.foo:
    class: App\Form\FooType
    arguments: ['@command_bus']
    tags: [form.type]
```

Now, your controller doesn't need to handle the Command any more. The Command is handled by the form.

Example:

```php
<?php

use App\Form\FooType;
use MyDomain\Command\DoSomethingCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class FooController extends AbstractController
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

Also, if your handler is throwing a `\DomainException`, such exception is caught and transformed
into a form error.


## 4. Direct use

Sometimes it happens that your command is not bound to a form, but you want to use a form anyway to handle it.
Usually, you would build an anonymous form directly in your controller. With this bundle, you can instead
use `BusType`. Of course, this case can be applied only when your command is not needing any dynamic value to be
assigned.

If you don't use autowiring, you'll need to declare `BusType` as a service (only once):

```yaml
# config/services.yaml

PUGX\BusFormBundle\Form\BusType: ~
```

Or, if you still old system (not autowired/autoconfigured):

```yaml
# app/config/services.yml

app.form.baz:
    class: PUGX\BusFormBundle\Form\BusType
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

class FooController extends AbstractController
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
