# Conditionally Serializing Fields with Groups

Once upon a time, I worked with a client that had a really interesting API requirement.
In fact, one that *totally* violate REST... but it's kinda cool. They said:

> When we have one object that relates to another object - like how our programmer
  relates to a user - *sometimes* we want to embed the user in the response
  and sometimes we don't. In fact, we want the API client to tell us via -
  a query parameter - whether or not they want embedded objects in the response.

Sounds cool...but it *totally* violates REST because you now have two different URLs
that return the same resource... each just returns a different *representation*.
Rules are great - but come on... if this is useful to you, make it happen.

## Testing the Deep Functionality

Let's start with a quick test: copy part of `testGETProgramer()` and name the new method
`testGETProgrammerDeep()`. Now, add a query parameter called `?deep`:

[[[ code('456bed9f1f') ]]]

The idea is simple: if the client adds `?deep=1`, then the API should expose more
embedded objects. Use the asserter to say `assertResponsePropertyExists()`, pass
that the `$response` and the property we'll expect, which is `user`. Since this
will be an entire user object, check specifically for `user.username`:

[[[ code('e172288684') ]]]

Very nice!

## Serialization Groups

If you look at this response in the browser, we definitely do *not* have a `user` field.
But there are only *two* little things we need to do to add it.

First, expose the `user` property with `@Serializer\Expose()`:

[[[ code('5623def751') ]]]

Of course, it can't be that simple: now the `user` property would *always* be included.
To avoid that, add `@Serializer\Groups()` and use a new group called `deep`:

[[[ code('dcdc57bdd2') ]]]

Here's the idea: when you serialize, each property belongs to one or more "groups".
If you don't include the `@Serializer\Groups` annotation above a property, then it
will live in a group called `Default` - with a capital `D`. Normally, the serializer
serializes *all* properties, regardless of their group. But you can also tell
it to serialize only the properties in a different group, or even in a set of groups.
We can use groups to serialize the `user` property under only *certain* conditions.

But before we get there - I just noticed that the `password` field is being exposed
on my `User`. That's definitely lame. Fix it by adding the `Expose` use statement,
removing that last part and writing `as Serializer` instead. That's a nice trick
to get that `use` statement:

[[[ code('0453dc5eff') ]]]

Now set `@Serializer\ExclusionPolicy()` above the class with `all` and add `@Expose`
above `username`:

[[[ code('2e55fa5f7a') ]]]

Back in `Programmer.php`, remove the "groups" code temporarily and refresh. OK good,
*only* the `username` is showing. Put that "groups" code back.

## Setting the SerializationGroup

Ok... so now, how can we serialize a specific set of groups? To answer that, open
`ProgrammerController` and find `showAction()`. Follow `createApiResponse()` into the
`BaseController` and find `serialize()`:

[[[ code('0952df49a0') ]]]

When we serialize, we create this `SerializationContext`, which holds a few options
for serialization. Honestly, there's not much you can control with this, but you *can*
set which *groups* you want to serialize.

First, get the `$request` object by fetching the `request_stack` service and adding
`getCurrentRequest()`. Next, create a new `$groups` variable and set it to only `Default`:
we *always* want to serialize the properties in this group:

[[[ code('1ae9bec6f5') ]]]

Now say `if ($request->query->get('deep'))` is true then add `deep` to `$groups`. 
Finish this up with `$context->setGroups($groups)`:

[[[ code('59c7ce31c3') ]]]

***SEEALSO
You could also use `$request->query->getBoolean('deep')` instead of `get()` to convert
the `deep` query parameter into a `boolean`. See [accessing request data][1] for other
useful methods.
***

And just like that, we're able to conditionally show fields. Sweet!

Re-run our test for `testGETProgrammerDeep()`:

```bash
./bin/phpunit -c app --filter testGETProgrammer
```

It passes! To really prove it, refresh the browser. Nope, no `user` property.
Now add `?deep=1` to the URL. That's a cool way to leverage groups. 

Wow, nice work guys! We've just taken another huge chunk out of our API with pagination,
filtering and a whole lot of cool serialization magic. Ok, now keep going with the
next episode!


[1]: http://symfony.com/doc/current/components/http_foundation/introduction.html#accessing-request-data
