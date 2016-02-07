# Evaluating the Link Expression

Before we fix the expression stuff, remove the `class` option from `getSubscribedEvents()`
because we want this to be called for *all* classes:

[[[ code('6a0043e267') ]]]

## Avoiding Duplicated _links

When you do that, things still work. But now: clear your cache in the terminal:

```bash
./app/console cache:clear
``` 

That's not normally something you need to do - but the JMSSerializerBundle doesn't
properly update its cache when you change this option. Refresh again.

Ah, huge error! Apparently there is already data for `_links`!? That's a bit weird.

Ah, but wait: one of the things we're serializing is the paginated collection itself,
which already has a `_links` property.

For better or worse, the JMS serializer library doesn't let you overwrite data on
a field. To fix this, add an `if` statement that only adds `_links` if we found some
on the object:

[[[ code('faa2ae66fa') ]]]

There's an even better fix - but I'll leave it to you. That would be to go into
`PaginatedCollection` and replace its links with the `@Link` annotation.
This would be a little bit of work, but I believe in you!

## Evaluating the Expression

Refresh the browser again. Things look good! Time to evaluate the expression.

To use the expression language, we just need to create an `ExpressionLanguage` object.
We *could* register this as a service, but I'll take a shortcut and instantiate a
new expression language right inside the constructor: `$this->expressionEngine = new ExpressionLanguage()`:

[[[ code('202d414393') ]]]

That class lives in the `ExpressionLanguage` component.

Rename that property to `expressionLanguage`. Later, if I *do* want to register this
as a service instead of creating it new right here, that'll be really easy.

Wrap `$annotation->params` in a call to `$this->resolveParams()` and pass it the
params *and* the object, since we'll need to pass that into the expression itself:

[[[ code('522927233b') ]]]

Add the new `private function resolveParams()` and then loop over `$params`:
`foreach ($params as $key => $param)`:

[[[ code('f9697a62b5') ]]]

For each param, we'll replace it with `$this->expressionLanguage->evaluate()`.
Pass it `$param` - that's the expression. Next, since the expressions are expecting
a variable called `object`, pass an array as the second argument with an `object` key
set to `$object`. And let's not forget our `$object` argument to this method!

[[[ code('cb3fea6631') ]]]

Finally, wrap this up `return $params;`. Now, each parameter is evaluated through
the expression language, which is a lot like Twig:

[[[ code('9a9c2ddfdf') ]]]

Ok, back to the browser. There it is! How about our test?

```bash
./bin/phpunit -c app --filter testGETProgrammer
```

Hey, they're passing too! Amazing!

In just a few short minutes, we made an entirely reusable linking system. I will
admit that this idea was stolen from a library called [Hateoas][1]. Now, don't
you feel dangerous?


[1]: https://github.com/willdurand/Hateoas
