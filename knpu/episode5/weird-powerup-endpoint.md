# Weird Endpoint: Command: Power-Up a Programmer

On our web interface, if you select a programmer, you can start a battle, *or* you
can hit this "Power Up" button. Sometimes our power goes up, sometimes it goes down.
And isn't that just like life.

The higher the programmer's power level, the more likely they will win future battles.

Notice: all we need to do is click one button: Power Up. We don't fill in a box with
the desired power level and hit submit, we just "Power Up"! And that makes this a
weird endpoint to build for our API.

Why? Basically, it doesn't easily fit into REST. We're not sending or editing
a resource. No, we're more issuing a command: "Power Up!".

Let's design this in a test: `public function testPowerUp()`:

[[[ code('1272f4c13c') ]]]

Grab the `$programmer` and `Response` lines from above, but replace `tagLine`
with a `powerLevel` set to 10:

[[[ code('c10f8ece02') ]]]

Now we know that the programmer *starts* with this amount of power.

## The URL Structure of a Command

From here, we have *two* decisions to make: what the URL should look like and what HTTP method
to use. Well, we're issuing a command for a specific programmer, so make the URL
`/api/programmers/UnitTester/powerup`:

[[[ code('8c98d220f4') ]]]

Here's where things get ugly. This is a new URI... so philosophically, this represents
a new resource. Following what we did with the tag line, we should think of this
as the "power up" resource. So, are we editing the "power up" resource... or are we
doing something different?

## The "Power Up?" Resource???

Are you confused? I'm kind of confused. It just doesn't make sense to talk about some
"power up" resource. "Power up" is *not* a resource, even though the rules of REST
want it to be. We just had to create *some* URL... and this made sense.

So if this isn't a resource, how do we decide whether to use PUT or POST? Here's
the key: when REST falls apart and your endpoint doesn't fit into it anymore, use
POST.

## POST for Weird Endpoints

Earlier, we talked about how PUT is idempotent, meaning if you make the same request
10 times, it has the same effect as if you made it just once. POST is *not* idempotent:
if you make a request 10 times, each request *may* have additional side effects.

Usually, this is how we decide between POST and PUT. And it fits here! The "power up"
endpoint is *not* idempotent: hence POST.

But wait! Things are *not* that simple. Here's the rule I want you to follow. *If*
you're building an endpoint that fits into the rules of REST: choose between POST
and PUT by asking yourself if it is idempotent.

But, if your endpoint does *not* fit into REST - like this one - always use POST.
So even if the "power up" endpoint *were* idempotent, I would use POST. In reality,
a PUT endpoint *must* be idempotent, but a POST endpoint is allowed to be either.

So, use `->post()`. And now, remove the `body`: we are not sending any data. This is
why `POST` fits better: we're not really *updating* a resource:

[[[ code('7fbb8f1702') ]]]

## And the Endpoint Returns....?

Assert that 200 matches the status code:

[[[ code('97051c40d5') ]]]

And now, what should the endpoint return?

We're not in a normal REST API situation, so it matters less. You could return nothing,
or you could return the power level. But to be as predictable as possible, let's
return the entire programmer resource. Read the new power level from this with
`$this->asserter()->readResponseProperty()` and look for `powerLevel`:

[[[ code('fc9341e3e7') ]]]

This *is* a property that we're exposing:

[[[ code('ac29492628') ]]]

We don't know what this value will be, but it *should* change. Use `assertNotEquals()`
to make sure the new `powerLevel` is no longer 10:

[[[ code('349744ca0c') ]]]

## Implement the Endpoint

Figuring out the URL and HTTP method was the hard part. Let's finish this. In
`ProgrammerController`, add a new `public function powerUpAction()`:

[[[ code('0ee5a053b1') ]]]

Add a route with `/api/programmers/{nickname}/powerup` and an `@Method` set to `POST`:

[[[ code('2be53541b2') ]]]

Once again, type-hint the `Programmer` argument:

[[[ code('158cf33d9c') ]]]

To power up, we have a service already made for this. Just say: `$this->get('battle.power_manager')`
`->powerUp()` and pass it the `$programmer`:

[[[ code('d7bf172007') ]]]

That takes care of everything. Now, return `$this->createApiResponse($programmer)`:

[[[ code('38d36b45da') ]]]

Done! Copy the `testPowerUp()` method name and run that test:

```bash
./vendor/bin/phpunit -—filter testPowerUp
```

Success!

And that's it - that's everything. I *really* hope this course will save you
from some frustrations that I had. Ultimately, don't over-think things, add links
when they're helpful and build your API for whoever will actually use it.

Ok guys - seeya next time!
