# Pagination Design and Test

Hey, Guys! Welcome to Episode 3 of our REST in Symfony Series. In this episode,
we're going to cover some really important details we haven't talked about yet, like
pagination, filtering, and taking the serializer and doing really cool and custom
things with it.

If you're following along with me, use the same project we've been building. If you're
just joining, where have you been? Ah, it's fine: download the code from this page
and move into the `start/` directory. Start up the built-in PHP web server to get
things running.

## Designing how Pagination should Work

Let's talk about pagination first, because the `/api/programmers` endpoint doesn't
have it. Eventually, once someone talks about our cool app on Reddit, we're going
to have a lot of programmers here: too many to return all at once. First, think about
pagination on the web. How does it work?  Usually, it's done with query parameters:
something like `?page=1`, `?page=2`, and so on. Sometimes, it's done in the URL - like
`/products/1` and `/products/2`. For API's, query parameters is better.

Second, on the web, we don't make the user guess those URLs: we give them links,
like "next" and "previous", and maybe even "first" and "last".

So why would building an API be any different? Let's use query parameters and include
links to help the API client get around.

## Adding a Test

Like always, we're gonna start with a test because it's the easiest way to try things
out *and* it helps us think about the API's design. In `ProgrammerControllerTest`,
find the `testProgrammersCollection` method and copy this to make a new test for
pagination.

To make this interesting, we need more programmers - like 25. Add a `for` loop to
do this: `for i=0; i<25; i++`. In each loop, create a programmer with the super
creative name of Programmer plus the i value. This means that we'll have programmers
zero through 24. The `avatarNumber` is required, but we don't care about its value.

Keep the same URL and the 200 status code assertion. Below, start basic with a sanity
check for page 1: assert that the programmer with index 5 is equal to `Programmer5`.
I'll use multiple lines to keep things clear. Index 5 is actually the 6th programmer,
but since we start with Programmer0, this should definitely be Programmer5. 

## Adding count and total

It might also be useful to tell the API client how many results are on *this* page
and how many results there are in total. I want to show 10 results per page in the
API so add a line that looks for a new property called `count` that's set to 10. Let's
also have another property called `total`. That'll be the *total* number of results.
In this case, that should be 25.

## Adding Links

Finally, the API response needs to have those links! And by "links", I mean that I
want to add a new field - maybe called "next" - whose value will be the URL to get
the next page of results. Use the asserter again and change this to
`assertResponsePropertyExists()`. Let's assert that there is an `_links.next` key,
which means the JSON will have an `_links` key and a `next` key under that. By
moving things under `_links`, it makes it a little more obvious that `next` isn't
a property of a programmer, but something different: a link.

Oh, and you probably saw my mistake above: change the line above to `total`, not `count`.

## Following Links

And here's where things get really cool. In our test, we need to make a request to
page 2 and make sure we see the next 10 programmers. Instead of hardcoding the URL,
we can *read* the next link and use that for the next request. It's like the API
version of clicking links!

Use `$this->asserter()` and then a method called `readResponseProperty()` to read
the `_links.next` property. Now, add `$response = $this->client->get($nextUrl)` to
go to the next page.

Ok, let's test page 2! Copy some of the asserts that we just wrote. This time, the
programmer with index 5 should be `Programmer15` because we're looking at results
11 through 20. Next, the `count` should still be 10, and the `total` still 25 - but
let's save a little code and remove that line.

The `next` link is nice. But we can do even more by *also* having a `first` link,
a `last` link and a `prev` link unless we're on page 1. Copy the code from earlier
that clicked the `next` link. Ooh, and let me fixing my formatting!

This time, use the `_links.last` key and update the variable to be `$lastUrl`. When
we make a request to the final page, `programmers[4]` will be the last programmer
because we started with index 0. The name should be `Programmer24`. And on this last
page, `count` should be just 5. I'm also going to use the asserter with
`assertResponsePropertyDoesNotExist` to make sure that there is *no* programmer here
with index 5. Specifically, check for no `programmers[5].name` path: There's a small
bug in my asserter code: if I just check for `programmers[5]`, it thinks it exists
but is set to `null`. That's why I'm checking for the `name` key.

That's it! Our pagination system is now *really* well-defined. Next, we'll bring
this all to life.
