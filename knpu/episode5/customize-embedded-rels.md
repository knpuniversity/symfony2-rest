## Customizing (making less ugly) Embeddeds!

Let me show you something else I don't really like. In `BattleControllerTest`, we're
checking for the embedded programmer. Right now it's hidden under this `_embedded` key:

[[[ code('b68c5134f7') ]]]

Hal does this so that a client knows which data is for the `Battle`, and which
data is for the embedded programmer. But what if it would be more convenient for
our client if the data was *not* under an `_embedded` key? What if they want the
data on the root of the object like it was before?

Well, that's fine! Just stop using the embedded functionality from the bundle.
Delete the assert that looks for the string and instead assert that the
`programmer.nickname` is equal to `Fred`:

[[[ code('8c80b72267') ]]]

In other words, I want to change the root `programmer` key from a string to the whole object.
And we'll eliminate the `_embedded` key entirely.

In `Battle.php`, remove the `embedded` key from the annotation:

[[[ code('bd3e0919a8') ]]]

OK, `_embedded` is gone! Next, on the `programmer` property, add `@Expose`:

[[[ code('4bf16256aa') ]]]

The serializer will serialize that whole object. We originally *didn't* expose
that property because we added this cool `@VirtualProperty` above the `getProgrammerNickname()`
method:

[[[ code('29588ad46e') ]]]

Get rid of that entirely.

In `BattleControllerTest`, let's see if this is working. First dump the response.
Copy the method name, and give this guy a try:

```bash
./vendor/bin/phpunit --filter testPOSTCreateBattle
```

Ah! It explodes!

> Warning: `call_user_func_array()` expects parameter 1 to be a valid callback.
  Class `Battle` does not have a method `getProgrammerNickname()`.


Whoops! I think I was too aggressive. Remember, at the top of `Battle.php`, we have an
expression that references this method:

[[[ code('526bca9f7c') ]]]

So... let's undo that change: put back `getProgrammerNickname()`, but remove
the `@VirtualProperty`:

[[[ code('606765fda6') ]]]

All right, try it again:

```bash
./vendor/bin/phpunit --filter testPOSTCreateBattle
```

It passes! And the response looks exactly how *we* want: no more `_embedded` key.

## We're not HAL-JSON'ing Anymore

But guess what, guys! We're breaking the rules of Hal! And this means that we are
*not* returning HAL responses anymore. And that's OK: I want you to feel the freedom
to make this choice.

We *are* still returning a consistent format that I want my users to know about,
it's just not HAL. To advertise this, change the `Content-Type` to
`application/vnd.codebattles+json`:

[[[ code('b97e843e5c') ]]]

This tells a client that this is still JSON, but it's some custom vendor format.
If we want to make friends, we should add some extra documentation to *our* API
that explains how to expect the links and embedded data to come back.

Copy that and go into `ProgrammerControllerTest` and update our `assertEquals()` that's
checking for the content type property:

[[[ code('c80e524555') ]]]

Finally, copy the test method name and let's make sure everything is looking good:

```bash
./vendor/bin/phpunit --filter testPOSTProgrammerWorks
```

All green!

I really love this HATEOAS library because it's so easy to add links to your API.
But it doesn't mean that you have to live with HAL JSON. You can use a different
official format or invent your own.
