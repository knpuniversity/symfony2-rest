# Link from Battle to Programmer

The Battle representation includes the username of the programmer that fought. If
you want more information about Fred, you can make a request to `/api/programmers/Fred`:

[[[ code('89fcbdc341') ]]]

That's something we'll document.

But! Wouldn't it be even more convenient if we added a link to that URL inside of
the Battle representation? Then, instead of needing to go look up and hardcode the
URL, the client could simply read and follow the link.

Whenever a link like this would be helpful, add it! First, look for it in the test:
`$this->asserter()->assertResponsePropertyEquals()`. For consistency, we decided to
put links under an `_links` key. So, look for `_links.programmer`. This should equal
`$this->adjustUri('/api/programmers/Fred)`:

[[[ code('b9a76680e4') ]]]

All this method does is help account for the extra `app_test.php` that's in the URL
when testing:

[[[ code('ff8f815122') ]]]

Perfect! Now, let's go add that link. First, open up the `Programmer` entity. We
added the `self` link earlier via a cool annotation system we created:

[[[ code('589832becb') ]]]

In `Battle`, add something similar: `@Link` - let that auto-complete for the `use` statement.
Set the name - or `rel` - of the link to `programmer`. This is the significance of
the link: it could be anything, as long as you consistently use `programmer` when
linking to a programmer:

[[[ code('9fbe262f84') ]]]

For the route, use `api_programmers_show`: the route name to a single programmer:

[[[ code('fd8b508030') ]]]

Finally, add `params`: the wildcard parameters that need to be passed to the route.
This route has a `nickname` wildcard. Set it to an expression: `object.getProgrammerNickname()`:

[[[ code('735b8c1b7a') ]]]

That's the method we created down below earlier:

[[[ code('6f5a3e7486') ]]]

And that's all we need. Copy the method name again - `testPostCreateBattle()` - and run
the test:

```bash
./vendor/bin/phpunit --filter testPostCreateBattle
```

And it works.

Now, let me show you an awesome library that makes adding links even easier. In fact,
I stole the `@Link` annotation idea from it.
