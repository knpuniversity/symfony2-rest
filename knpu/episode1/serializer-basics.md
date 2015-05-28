# Using a Serializer

We're turning Programmers into JSON by hand inside `serializeProgrammer()`:

TODO CODE

That's pretty ok with just one resource, but this will be a pain when we
have a lot more - especially when resources start having relations to other
resources. To make this way more fun, we'll use a serializer library: code
that's really good at turning objects into an array, or JSON or XML.

The one we'll use is called "JMS Serializer" and there's a bundle for it called
[JMSSerializerBundle](http://jmsyst.com/bundles/JMSSerializerBundle). This
is a *fanstatic* library and incredibly powerful. It *can* get complex in
a few cases, but we'll cover those. You should also know that this library
is not maintained all that well anymore and you'll see a little bug that we'll
have to work around. But it's been around for years, it's really stable and
has a lot of users.

Symfony itself ships with a serializer, and in Symfony 2.7, it'll finally
have a lot of features that JMS Serializer has. There's a push inside Symfony
to make it eventually replace JSM Serialize for most use-cases. So, keep an
eye on that. Oh, and JMS Serializer is licensed under Apache2, which is a
little bit less permissive than MIT, which is Symfony's license. If that
worries you, look into it further.

With all that out of the way, let's get to work. Copy the `composer require`
line and paste it into the terminal:

```bash
composer require jms/serializer-bundle
```

While we're waiting, copy the bundle line and add this into our `AppKernel`:

TODO CODE

This gives us a new service calld `jms_serializer`, which can turn any object
into JSON or XML. So in the controller, rename `serializeProgrammer` to
`serialize` and make the argument `$data`, so you can pass it anything.
And inside, just return `$this->container->get('jms_serializer')` and call
`serialize()` on that, passing it `$data` and `json`:

TODO CODE

PhpStorm is angry, just because composer hasn't finished downloading yet:
we're working ahead.

Find everywhere we used `serializeProgrammer()` and change those. The only
trick is that it's not returning an array anymore, it's returning JSON. So
I'll say `$json = $this->serialize($programmer)`. And we can't use `JsonResponse`
anymore, or it'll encode things twice. Create a regular `Response` instead.
Copy this and repeat the same thing in `showAction()`. Use a normal `Response`
here too:

TODO CODE

For `listAction`, life gets easier. We can't use `serialize()` inside the
loop anymore, or else we'll get a big array of JSON strings. But we *can*
just put the `$programmers` array inside the `$data` array and then pass
this big structure into the `serialize()` function:

TODO CODE

The serializer has no problem serializing arrays of things. Make the same
changes in `updateAction()`:

TODO CODE

Great! Let's check on Composer. It's done, so let's try our entire test
suite:

```bash
phpunit -c app
```

Ok, things are *not* going well. One of them says:

    Error reading property "avatarNumber" from available keys
    (id, nickname, avatar_number, power_level)

The responses on top show the same thing: all our properties are being underscored.
The JMS Serializer library does this by default... which I kinda hate. So
we're going to turn it off.

The library has something called a "naming strategy" - basically how it transforms
property names into JSON or XML keys. You can see some of this inside the
bundle's configuration. They have a built-in class for doing nothing: it's
caled the "identical" naming strategy. Unfortunately, the bundle has a bug
in its configuration that makes this not configurable in the normal way.
Instead, we need to go kung-foo on it.

Open up `config.yml`. I'll paste a big long ugly new parameter here:

TODO CODE

This creates a new parameter called `jms_serializer.camel_case_naming_strategy.class`.
I'm setting this to `JMS\Serializer\Naming\IdenticalPropertyNamingStrategy`.
That is a total hack - I only know to do this because I went deep enough
into the bundle to find this. If you want to know how this works, check out
our [Journey to the Center of Symfony: Dependency Injection](https://knpuniversity.com/screencast/symfony-journey-di)
screencast: it's good nerdy stuff. The important thing for us is that this
will leave our property names alone.

So now if we run the test:

```bash
phpunit -c app
```

we still have failures. But in the dumped response, our property names are
back!

