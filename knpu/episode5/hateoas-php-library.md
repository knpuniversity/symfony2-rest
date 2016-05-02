# The Great Hateoas PHP Library

Google for "HATEOAS PHP" to find a fun library that a friend of mine made. This library
has a bundle that integrates it into Symfony: so click to view the BazingaHateoasBundle
and go straight to its docs. Before we talk about what it does: get it installed.

## Installing BazingaHateoasBundle

Copy the composer require statement and then flip over to your terminal and
paste that: 

```bash
composer require willdurand/hateoas-bundle
```

This is a bundle, so grab the new bundle statement, open `AppKernel` and pop that
at the bottom.

Perfect. Currently, we have our own super sweet annotation system for adding links.
In `Battle`, we use `@Link` to create a `programmer` link.

Guess what! I completely stole that idea from this library. But now, to make our app
a little simpler and to get some new features, let's replace our `@Link` code with
*this* library.

## Adding the HATEOAS Annotation Links

Go back to the library itself, and scroll down to the first coding example. This
uses an annotation system that looks pretty similar to ours. Copy the `use`
statement on top, open `Battle` and paste that.

Next, change the annotation to `@Hateoas\Relation`. Keep `programmer`: that will still
be the link's `rel`. But add `href=@Hateoas\Route` and pass that the name of the
route: `api_programmers_show`. Update `params` to `parameters`, and inside, set
`nickname` equal, and wrap the expression in `expr()`.

That translates our annotation format to the one used by the bundle. And the result
is *almost* the same. Open `BattleControllerTest` and copy the first method name:
we have a test for a link near the bottom of this. Change over to the terminal and,
as long as Composer is done, run:

```bash
vendor/bin/phpunit --filter testPOSTCreateBattle
```

Check it out! It fails - but *barely*. This library *still* puts links under an
`_links` key, but instead of listing the URLs directly, it wraps each inside an object
with an `href` key. That's causes the failure.

Ok, fair enough. Let's fix that by updating the test to look for
`_links.programmer.href`. Run the test again:

```bash
vendor/bin/phpunit --filter testPOSTCreateBattle
```

And now we're green.

## Holy Toledo Batman: This is HAL JSON!

But guess what? It's no accident that this library used this exact format: with an
`_links` key and an `href` below that. This is a semi-official standard format called
HAL JSON.
