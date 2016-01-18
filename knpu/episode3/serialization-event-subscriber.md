# Serialization Event Subscriber

I think the best part of doing API magic in Symfony is the serializer we've been
using. We just give it objects - whether those are entities or something else - and
it takes care of turning its properties into JSON. And we have control too: by using
the exclusion policy and other annotations like `@SerializedName` that lets us control
the JSON key a property becomes.

## When does the Serializer Fail?

Heck, we can even add virtual properties! Just add a function inside your class,
add the `VirtualProperty` annotation above it... and bam! You now have another field
in your JSON response that's not *actually* a property on the class. That's great!
It handles 100% of what we need! Right... right?

Ah, ok: there's still this last, nasty 1% of use-cases where virtual property won't
work. Why? Well, imagine you want to include the URL to the programmer in its JSON
representation. To make that URL, you need the `router` service. But can you access
services from within a method in `Programmer`? No! We're in trouble!

This is usually where I get really mad and say "Never mind, I'm not using the stupid
serializer anymore!" Then I stomp off to my bedroom to play video games.

But come on, we can definitely overcome this. In fact, there are *two* ways. The
more interesting is with an event subscriber on the serializer.

## Creating a Serializer Event Subscriber

In `AppBundle`, create a new directory called `Serializer` and put a fancy new class
inside called `LinkSerializationSubscriber`. Set the namespace to `AppBundle\Serializer`.

To create a subscriber with the JMSSerializer, you need to implement `EventSubscriberInterface`...
and make sure you choose the one from `JMS\Serializer`. There's also a core interface
that, unfortunately, has the exact same name.

In PHPStorm, I'll open the Generate shortcut and select "Implement Methods". This
will tell me all the methods that the interface requires. And, it's just one:
`getSubscribedEvents`.

Stop: here's the goal. Whenever we serialize something, there are a few events we
can hook into to customize that process. In this method, we'll tell the serializer
exactly which methods we want to hook into. One of those will allow us to *add* a
new field... which will be the URL to whatever Programmer is being serialized.

Return an array with another array inside: we'll need a few keys here. The first
is `event` - the event name we need to hook into. There are two for serialization:
`serializer.pre_serialize` and `serializer.post_serialize`. We need the second because
it lets you *change* the data that's being turned into JSON.

Add a `method` key and set it to `onPostSerialize` - we'll create that in a
second. Next, add `format` set to JSON. This means the method will *only* be called
when we're serializing into JSON... which is fine - that's all our API does.

Finally, add a `class` key set to `AppBundle\Entity\Programmer`. This says, "Hey!
Only call `onPostSerialize` for Programmer classes!".

## Adding a Custom Serialized Field

Setup, done! Create that `public function onPostSerialize`. Just like with core Symfony
events, you'll be passed an event argument, which in this case is an instance of
`ObjectEvent`. Now, we can start messing with the serialization process.

Before we go any further, go back to our test. To goal is for each Programmer to
have a new field that is a link it itself. In `testGETProgrammer`, add a new assert
that checks that we have a `uri` property that's equal to `/api/programmers/UnitTester`.

Ok, let's see how we can use the fancy subscriber to add this field automatically.
