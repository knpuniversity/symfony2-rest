# GET one Programmer

Creating a programmer check! Next let's add the endpoint to return a single
programmer. Add a `public function showAction()`. And even though it *technically*
could be anything we dream up, the URL for this will follow a predictable
pattern: `/api/programmers/` and then identifier. This might be an `{id}`,
but for us each programmer has a unique `nickname`, so we'll use that instead.
Don't forget the `@Method("GET")`:

[[[ code('236e6cdddc') ]]]

A client will use this to GET this programmer resource.

Add the `$nickname` argument and kick things off just by returning a new
`Response` that says hi to our programmer:

[[[ code('cddcef40ce') ]]]

## Testing this Endpoint

Every time we create a new endpoint, we're going to add a new test. Ok, we
don't *really* have tests yet, but we will soon! Right now, go back to
`testing.php` and make a second request. The first is a POST to create the
programmer, and the second is a GET to fetch that programmer.

Change the method to `get()` and the URI will be `/api/programmers/` plus
the random `$nickname` variable. And we don't need to send any request body:

[[[ code('75e7d6b32d') ]]]

Alright, let's try it!

```
php testing.php
```

Well Hello ObjectOrienter564 and Hello also ObjectOrienter227. Nice to meet
both of you.

## Returning the Programmer in JSON

Instead of saying Hello, it would probably be more helpful if we sent the
Programmer back in JSON. So let's get to work. First, we need to query for
the Programmer: `$this->getDoctrine()->getRepository('AppBundle:Programer')`
and I already have a custom method in there called `findOneByNickname()`:

[[[ code('b6c34606ff') ]]]

Don't worry about 404'ing on a bad nickname yet. To turn the entity object
into JSON, we'll eventually use a tool called a serializer. But for now,
keep it simple: create an array and manually populate it: a `nickname` key
set to `$programmer->getNickname()` and `avatarNumber => $programmer->getAvatarNumber()`.
Also set a `powerLevel` key - that's the energy you get or lose when powering
up - and finish with the `tagLine`:

[[[ code('dff076babf') ]]]

Return whatever fields you want to: it's your API, but consistency is king.

Now all we need to do is `json_encode` that and give it to the Response:

[[[ code('e95d6b9a62') ]]]

Our tools *will* get more sophisticated and fun than this. But keep this
simple controller in mind: if things get tough, you can always back up to
creating an array manually and json_encoding the results.

Let's try the whole thing:

```bash
php testing.php
```

Hey, nice JSON.
