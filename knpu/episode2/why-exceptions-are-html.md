# Request Format: Why Exceptions Return HTML

When you throw an exception in Symfony - even an HttpException - it returns an HTML
page. Notice the `Content-Type` header here of `text/html`. And in reality - my test
helpers are hiding it, but this is returning a full, giant HTML exception page.

Why is that? Why does Symfony default to the idea that if something goes wrong, it
should return HTML? 

## Request Format

Here's the answer: for every single request, Symfony has what's called a "request format",
and it defaults to `html`. But there are a number of different ways to say "Hey Symfony,
the user wants *json*, so if something goes wrong, give them that".

The easiest way to set the request format is in your routing. Open up `app/config/routing.yml`:

[[[ code('7396cbf967') ]]]

When we import the routes from our API controllers, we want *all* of them to have
a `json` request format. To do that, add a `defaults` key. Below that, set a magic
key called `_format` to `json`:

[[[ code('e549481e96') ]]]

For us, this is optional, because in a minute, we're going to *completely* take control
of exceptions for our API. But with just this, re-run the tests:

```bash
./bin/phpunit -c app --filter testInvalidJson
```

Yes! Now we get a `Content-Type` header of `application/json` and because we're in
the `dev` environment, it returns the stack trace as JSON in the body.

This is cool. But the JSON structure still won't be right. So let's take full control.
