# Super Custom Serialization Fields

## The Serialization Visitor

Back in the subscriber, create a new variable called `$visitor` and set it to
`$event->getVisitor()`. The visitor is kind of in charge of the serialization
process. And since we *know* we're serializing to JSON, this will be an instance
of `JsonSerializationVisitor`. Write an inline doc for that and add a use statement
up top. That will give us autocompletion:

[[[ code('57d519aef2') ]]]

Oh, hey, look at this - that class has a method on it called `addData()`. We can use
it to add whatever cool custom fields we want. Add that new `uri` field, but just set
it to the classic `FOO` value for now:

[[[ code('784ba0f349') ]]]

## Registering the Subscriber

The *last* thing we need to do -  which you can probably guess - is register this
as a service. In `services.yml`, add the service - how about `link_serialization_subscriber`.
Add the class and skip `arguments` - we don't have any yet. But we *do* need a tag
so that the JMS Serializer knows about our class. Set the tag name to `jms_serializer.event_subscriber`:

[[[ code('96c4d737eb') ]]]

Ok, try the test! Copy the method name, head to the terminal and run:

```bash
./bin/phpunit -c app --filter testGETProgrammer
```

and then paste in the name. This method name matches a few tests, so we'll see more
than just our *one* test run. Yes, it fails... but in a good way!

> `FOO` does not match `/api/programmers/UnitTester`.

Above, we *do* have the new, custom `uri` field.

## Making the URI Dynamic

This means we're *almost* done. To generate the real URI, we need the router. Add
the `__construct()` method with a `RouterInterface` argument. I'll use the `option`+`enter`
shortcut to create that property and set it:

[[[ code('898d8d20a2') ]]]

In `onPostSerialize()` say `$programmer = $event->getObject();`. Because of our configuration
below, we *know* this will only be called when the object is a `Programmer`. Add
some inline documentation for the programmer and plug in its use statement:

[[[ code('71c390151c') ]]]

Finally, for the data type `$this->router->generate()` and pass it `api_programmers_show`
and an array containing `nickname` set to `$programmer->getNickname()`:

[[[ code('468c1adfeb') ]]]

Cool! Now, go back to `services.yml` and add an `arguments` key with just `@router`:

[[[ code('bfbef6517b') ]]]

Ok, moment of truth! Run the test!

```bash
./bin/phpunit -c app --filter testGETProgrammer
```

And... it's failing. Ah, the URL has `?nickname=UnitTester`. Woh woh. I bet that's
my fault. The name of the route in `onPostSerialize()` should be  `api_programmers_show`:

[[[ code('6c31e9407e') ]]]

Re-run the test:

```bash
./bin/phpunit -c app --filter testGETProgrammer
```

It's still failing, but for a new reason. This time it doesn't like the `app_test.php`
at the beginning of the link URI. Where's that coming from?

The test class extends an `ApiTestCase`: we made this in an earlier episode. This
app already has a `test` environment and it configures a *test* database connection.
If we can force every URL through `app_test.php`, it'll use that test database, and
we'll be really happy:

[[[ code('f03afe7d9f') ]]]

We did a cool thing with Guzzle to accomplish this: automatically prefixing our requests
with `app_test.php`. But because of that, when we generate URLs, they will also have
`app_test.php`. That's a good thing in general, just not when we're comparing URLs in a test.

Copy that path and create a helper function at the bottom of `ApiTestCase` called
`protected function adjustUri()`. Make this return `/app_test.php` plus the `$uri`.
This method can help when comparing expected URI's:

[[[ code('61e32576b6') ]]]

Now, in `ProgrammerControllerTest`, just wrap the expected URI in `$this->adjustUri()`:

[[[ code('a6583d9a29') ]]]

This isn't a particularly incredible solution, but now we can properly test things.
Run the tests again...

```bash
./bin/phpunit -c app --filter testGETProgrammer
```

And... It's green! Awesome!

## Method 2: Adding Custom Fields

One last thing! I mentioned that there are *two* ways to add super-custom fields
like `uri`. Using a serializer subscriber is the first. But sometimes, your API
representation will look *much* different than your entity. Imagine we had some
crazy endpoint that returned info about a Programmer mixed with details about their
last 3 battles, the last time they fought and the current weather in their hometown.

Can you imagine trying to do this? You'll need multiple `@VirtualProperty` methods
and probably some craziness inside an event subscriber. It might work, but it'll
look ugly and be confusing.

In this case, there's a much better way: create a new class with the exact properties
you need. Then, instantiate it, populate the object in your controller and serialize
it. This class isn't an entity - it's just there to model your API response. I *love*
this approach and recommend it as soon as you're doing more than just a few serialization
customizations to a class.
