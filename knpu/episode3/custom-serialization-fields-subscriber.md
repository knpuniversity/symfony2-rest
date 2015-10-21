# Custom Serialization Fields Subscriber

One of the things that makes doing API's in Symfony so powerful is the seralizer
that we've been using. We can just give it objects, whether they are entities or
something else, and it just seralizes all of their properties. Of course we have
control over which ones get serialized with the exclusion policy and different
annotations to rename what a property is called when we expose it through the API.

We can even add virtual properties, we could make a function inside of our class
and by adding `virtualProperty` on it we can add another field to our JSON response 
that's not actually a property on the class. That's great, but it still leaves the last
1% which are cases when you can't use the virtual property because what you need to
return requires access to some other service.

A perfect example of this is if we want to include the URL to the programmer in the 
representation of it. We can't just create a public function inside of this programmer
class and tag it as a virtual property because we need the router service in order to 
generate the URL. This can be a really frustrating thing, where you say "Nevermind, I'm
not using the serializer anymore!" and stomp off to your bedroom to play video games.
But this is really easy to overcome with an event subscriber on the serializer.

In `AppBundle` create a new directory called `Serializer` and inside of there create a
new PHP class called `LinkSeralizationSubscriber` and we'll put it in the `AppBundle\Serializer`
namespace. In order to have subscriber we just need to implement `EventSubscriberInterface`
and make sure you're getting the one from `JMS\Serializer`. There's also a core one and they
unfortunately have the exact same name. 

In PHPStorm head into the implement methods menu from generate and we only need the 
`getSubscribedEvents`. Inside of here we'll return an array of all of the events that we
want to subscribe to. Each one of those events is going to be an array as well. We'll need
a few keys in here, the first is for which even we want to subscribe to. There are two for
seralization, there is `serializer.pre_serialize` and `serializer.post_serialize`, we want
the second one, this allows us to modify the data that is being returned in the JSON.
The second thing we'll say is that the method to call is `onPostSerialize` which we'll create in
a second. And the format is JSON. We're only serializing to JSON but if we were also doing it
to XML it wouldn't call this particular subscriber. We'd want to create another subscriber that
handled this for XML because the formats are just that different. The last thing we'll have in
here is a class key which says "Hey! Only call this `onPostSerialize` for programmer classes!".

Create a new `public function onPostSerialize` and like core Symfony events it's going to be passed
an object, which in this case is `ObjectEvent`.

Before we go any further with this, let's head over to our test and think about how we want this
to look. In `testGETProgrammer` add a new assert that asserts that we have some uri property 
that's equal to `/api/programmers/UnitTester`.

In our subscriber, when we serialize a programmer this method is going to be called and it has a
really important object on it called `$visitor` which we can get off the event with `$event->getVisitor();`.
We're only serializing JSON so we know that this visitor will be an instance of `JsonSerializationVisitor`.
Write an inline documentation for that and add a use statement up top. 

This is all really cool because that has a method on it called `addData` where we can add whatever we want
to our JSON. This is the key to being able to add custom fields.

Ok, let's see if we can get this working! The last thing we need to do, which you can probably guess, is go
to `services.yml` and register this as a new service `link_serialization_subscriber`, this name isn't important
but the class that we'll fill in next is! And we don't have any arguments to add on this yet, so just leave
that blank. We do need a tag for the JMS Serializer to use it, in this case the tag name is `jms_serializer.event_subscriber`.

Ok, let's try the test! Copy the method name, head over to the terminal and run `./bin/phpunit -c app --filter`
and then paste in the name. This method name actually matches a few things so we'll see a couple of tests run here.
It fails because foo does not match Api Programmer's UnitTester. Up here we see `"uri":"FOO"`, we are actually
modifying our response!

This means we're most of the way there, we just need to hook all this up which is pretty easy. To generate
URL's we need a router so make a `__constructor`. Pass it `$routerInterface` use the keyboard shortcut option+enter
to initialize the field. 

In `onPostSerialize` say `$programmer = $event->getObject();` which is the object being serialized.  We know that's
going to be a programmer because this is only being called for them. So we can throw some inline documentation
for the programmer and plug in its use statement. 

Finally, for the data type `$this->router->generate`, give it `api_programmers_collection` and an array of
`nickname` that is set to `$programmer->getNickname()`. There we go! Now we just need to make sure that our
router is being injected in as an argument. You can see that PhpStorm is angry with me because it sees that
I'm missing an argument there so pass in the `@router`. 

Back to the terminal for the moment of truth! Run the test, and it's failing. I see that this URL has a
`?nickname=UnitTester` which is my fault. The name of our route in `onPostSerialize` should be 
`api_programmers_show`. Rerun the test again. It's still failing, but for a new reason. This time it doesn't
like the `app_test.php` at the beginning of our URL. Since we're testing right now when the API generates
URLs it's putting this controller in front of it which is giving us a false test failure. Let me tell you
where this is coming from.

Our test class is extending an `ApiTestCase` which is something that we made and when we use our API we 
want it to use the test database. We want to force every single URL through `app_test.php`. We did a cool
thing with guzzle here by making sure that every url was prefixed with `app_test.php` but because of that
Symfony is also generating urls with this prefix. This is a good thing in general, but not for this test here.

Copy that path and create a helper function at the bottom of `ApiTestCase` called `protected function adjustUri`
which returns `/app_test.php` plus the `$uri`. This method helps when comparing expected URI's.

In `ProgrammerControllerTest` when we compare uri's we'll just wrap it in this, `$this->adjustUri` which
will then add the `app_test.php` for us. This isn't perfect, but it will stay out of our way so we can properly
test things. Running our test now we see that ... it's green! Awesome!
