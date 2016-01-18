# Evaluation the Link Expression

Before we fix the expression stuff, remove the `class` option from `getSubscribedEvents()`
because we want this to be called for *all* classes.

## Avoiding Duplicated _links

When you do that, things still work. But now: clear your cache in the terminal:

```bash
./app/console cache:clear`
``` 

That's not normally something you need to do - but the JMSSerializerBundle doesn't
properly update its cache when you change this option. Refresh again.

Ah, huge error! Apparently there is already data for `_links`!? That's a bit weird.

Ah, but wait: one of the things we're serializing is the paginated collection itself,
which already has a `_links` property.

For better or worse, the JMS Serializer library doesn't let you overwrite data on
a field. To fix this, add an `if` statement that only adds `_links` if we found some
on the object.

There's an even better fix - but I'll leave it to you. That woulb be to go into
`PaginatedCollection` and replace its links with the `@Link` annotation.
This would be a little bit of work, but I believe in you!

## Evaluting the Expression

Refresh the browser again. Things look good! Time to evaluate the expression.

To use the expression language, we just need to create an `ExpressionLanguage` object.
We *could* register this as a service, but I'll take a shortcut and instantiate a
new expression language right inside the constructor: `$this->expressionEngine = new ExpressionLanguage`.
That class lives in the `ExpressionLanguage` component.

Rename that property to `expressionLanguage`. Later, if I *do* want to register this
as a service - maybe to add caching - and inject it instead of creating it new, that'll
be really easy.

Wrap `$annotation->params` in a call to `$this->resolveParams()` and pass it the
params *and* the object, since we'll need to pass that into the expression itself.

Add the new `private function resolveParams()` and then loop over `$params`:
`foreach ()$params as $key => $param)`. For each param, we'll replace it with
`$this->expressionLanguage ->evaluate()`. Pass it `$param` - that'ss the expression.
Next, since the expressions are expecting a variable called `object`, pass an array
as the second argument with an `object` key set to `$object`. And let's not forget
our `$object` argument to this method!

Finally, wrap this up `return $params;`. Now, each parameter is evaluated through
the expression language, which is a lot like Twig.

Ok, back to the browser. There it is! How about our test? Hey, they're passing too!
Amazing!

In just a few short minutes, we made an entirely resuable linking system. I will
admit that this idea was stolen from a library called [Hateoas](https://github.com/willdurand/Hateoas).
Now, don't you feel dangerous?
