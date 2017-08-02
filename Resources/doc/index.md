PUGXBusFormBundle Documentation
===============================

## 1. Installation

``` bash
$ composer require pugx/bus-form-bundle
```

You likey want to install [SymfonyBridge](https://github.com/SimpleBus/SymfonyBridge).

## 2. Configuration

Enable the bundle in the kernel:

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

namespace AppBundle\Form;

use MyDomain\Command\DoSomethingCommand;
use PUGX\BusFormBundle\AbstractBusType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FooType extends AbstractBusType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('bar')
            ->add('baz')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DoSomethingCommand::class,
        ]);
    }
}
```

You need to declare your form as a service, injecting the `command_bus` service.

```yaml
# app/config/services.yml

    app.form.foo:
        class: AppBundle\Form\FooType
        arguments: ['@command_bus']
        tags:
            - { name: form.type }

```

Now, your controller doesn't need to handle the Command any more. The Command is handled by the form.

Example:

```php
<?php

use AppBundle\Form\FooType;
use MyDomain\Command\DoSomethingCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FooController  extends Controller
{
    // before
    public function doSomethingAction()
    {
        $form = $this->createForm(FooType::class, new DoSomethingCommand());
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('command_bus')->handle($command);

            return $this->redirectToRoute('some_route');
        }

        return $this->render('do/something.html.twig', ['form' => $form->createView()]);
    }

    // after
    public function doSomethingAction()
    {
        $form = $this->createForm(FooType::class, new DoSomethingCommand());
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
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

You need to declare `BusType` as a service (only once):

```yaml
# app/config/services.yml

    app.form.baz:
        class: PUGX\BusFormBundle\Form\BusType
        arguments: ['@command_bus']
        tags:
            - { name: form.type }

```
Then, in your controller, you can do something like the following:

```php
<?php

use PUGX\BusFormBundle\Form\BusType;
use MyDomain\Command\DoSomethingCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FooController  extends Controller
{
    public function doSomethingAction()
    {
        $form = $this->createForm(BusType::class, new DoSomethingCommand());
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('some_route');
        }

        return $this->render('do/something.html.twig', ['form' => $form->createView()]);
    }
}

```
