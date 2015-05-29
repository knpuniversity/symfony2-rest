# Taking Control of the Serializer

The serializer is... *mostly* working. But we have some test failures:

    Error reading property "tagLine" from available keys (id, nickname
    avatarNumber, powerLevel)

Huh. Yea, if you look at the response, `tagLine` is mysteriously absent!
Where did you go dear tagLine???

So the serializer works like this: you give it an object, and it serializes
every property on it. Yep, you *can* control that - just hang on a few minutes.
But, if any of these properties is `null`, instead of returning that key
with `null`, it omits it entirely.

Fortunately, that's easy to change. Go into `BaseController`. In `serialize()`
create a new variable called `$context` and set that to a `new SerializationContext()`.
Call `setSerializeNull()` on this and pass it `true`. To finish this off,
pass that `$context` as the third argument to `serialize()`:

TODO CODE

Think of the `SerializationContext` as serialization configuration. It doesn't
do a lot of useful stuff - but it *does* let us tell the serializer to actually
return null fields.

So run the *whole* test suite again and wait impatiently:

```bash
phpunit -c app
```

ZOMG! They're passing!

## Serialization Annotations

But something extra snuck into our Response - let me show you. In `testGETProgrammer()`,
at the end, add `$this->debugResponse()`. Copy that method name and run just
it:

```bash
phpunit -c app --filter testGETProgrammer
```

Ah, the `id` field snuck into the JSON. Before, we only serialized the other
four fields. So what if we *didn't* want `id` or some property to be serialized?

The solution is so nice. Go back to the homepage of the bundle's docs. There's
one documentation gotcha: the bundle is a small wrapper around the JMS Serializer
library, and most of the documentation lives there. Click the
[documentation](http://jmsyst.com/libs/serializer) link to check it out,.

This has a *great* page called [Annotations](http://jmsyst.com/libs/serializer/master/reference/annotations):
it's a reference of *all* of the ways that you can control serialization.

### @VirtualProperty and @SerializedName

One useful annotation is [@VirtualProperty](http://jmsyst.com/libs/serializer/master/reference/annotations#virtualproperty).
This lets you create a method and have its return value serialized. If you
use that with `@SerializedName`, you can control the serialized property
name for this or anything.

For controlling *which* fields are returned, we'll use
[@ExclusionPolicy](http://jmsyst.com/libs/serializer/master/reference/annotations#exclusionpolicy).
Scroll down to the `@AccessType` code block and copy that `use` statement.
Open the `Programmer` entity, paste this on top, but remove the last part
and add `as Serializer`:

TODO CODE

This will let us say things like `@Serializer\ExclusionPolicy`. Add that
on top of the class, with `"all"`.

TODO CODE

This says: "Hey serializer, don't serialize *any* properties by default,
just hang out in your pajamas". Now we'll use `@Serializer\Expose()` to 
whitelist the stuff we *do* want. We don't want `id` - so leave that. 
Above the `$name` property, add `@Serializer\Expose()`. Do this same thing 
above `$avatarNumber`, `$tagLine` and `$powerLevel`:

TODO CODE

And my good buddy PhpStorm is telling me I have a syntax error up top. Whoops,
I doubled my `use` statements - get rid of the extra one:

With this, the `id` field *should* be gone from the response. Run the test!

```bash
phpunit -c app --filter testGETProgrammer
```

No more `id`! Take out the `debugResponse()`. Phew! Congrats! We only have
one resource, but our API is kicking butt! We've built a system that let's
us serialize things easily, create JSON responses and update data via forms.
We also have a killer test setup that let's us write tests first without
any headache. We could just keep repeating what we have here to make a bigger
API.

But, there's more to cover! In episode 2, we'll talk about errors: a fascinating
topic for API's and something that can make or break how usable your API
will be.

Ok, seeya next time!
