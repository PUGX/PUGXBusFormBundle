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

In your forms that are abound to a Command, extends bundle's form instead of Symfony one.

Example:

```php
<?php

namespace AppBundle\Form;

use PUGX\BusFormBundle\AbstractBusType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use MyDomain\Command\DoSomethingCommand;

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
        if ($form->handleRequest($request)->isValid()) {
            $this->get('command_bus')->handle($command);

            return $this->redirectToRoute('some_route');
        }

        return $this->render('do/something.html.twig', ['form' => $form->createView()]);
    }

    // after
    public function doSomethingAction()
    {
        $form = $this->createForm(FooType::class, new DoSomethingCommand());
        if ($form->handleRequest($request)->isValid()) {
            // look ma, no command handling needed!
            return $this->redirectToRoute('some_route');
        }

        return $this->render('do/something.html.twig', ['form' => $form->createView()]);
    }
}

```

Also, if your handler is throwing a DomainException, such exception is caught and transformed
into a form error.


