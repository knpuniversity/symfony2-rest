# Embedding Objects with Hal?

Check out the Hal example on their docs. There are actually *three* different sections
of the json: the actual data - like `currentlyProcessing`, `_links`
and `_embedded`.

## Relations: Links versus Embedding

Here's a cool idea: we know that it's nice to add links to a response. These are
called *relations*: they point to *related* resources. But there's *another* way
to add a relation to a response: *embed* the related resource right in the JSON.

Remember: the *whole* point of adding link relations is to make life easier for your
API clients. If embedding the data is *even* easier than advertising a link, do it.

In fact, let's pretend that when we return a `Battle` resource, we still want to
include a link to the related `Programmer`, but we also want to embed that `Programmer`
entirely.

## Adding an Embedded Relation

To do that, after `href`, add an `embedded="expr()"` with `object.getProgrammer()`:

[[[ code('d279be8d36') ]]]

Let's see what this looks like! Open `BattleControllerTest` and right at the bottom,
add our handy `$this->debugResponse($response)`:

[[[ code('64a366deaf') ]]]

Perfect! Copy that method name and run it:

```bash
./vendor/bin/phpunit --filter testPOSTCreateBattle
```

Oh, cool: we still have the relation in `_links`, but now we *also* have an entire
programmer resource in `_embedded`. So when you setup these `@Hateoas\Relation`
annotations:

[[[ code('bec8d50e6d') ]]]

You can choose whether you want this to be a link or an embedded object.

And OK, we cheated on this test by looking at it first, but *now* I guess we should
specifically have a test for it. Add: `$this->asserter()->assertResponsePropertyEquals()`
with `$response`. Look for `_embedded.programmer.nickname` to be equal to our friend
`Fred`:

[[[ code('042031728a') ]]]

Run that!

```bash
./vendor/bin/phpunit --filter testPOSTCreateBattle
```

It passes! Now let's customize how these links render.
