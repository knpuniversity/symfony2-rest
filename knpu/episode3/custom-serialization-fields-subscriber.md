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
that blank. We do need a tag for the JMS Serializer to use it. 

