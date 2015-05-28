# PATCH is (also) for Updating (basically)

The main HTTP methods are: GET, [POST, PUT](http://knpuniversity.com/screencast/rest/put-versus-post)
and DELETE. There's another one you hear a lot about: PATCH.

The simple, but not entirely accurate definition of PATCH is this: it's just
like PUT, except you don't need to send up the entire resoure body. If you
just want to update `tagLine`, just send that field.

So really, PATCH is a bit nicer to work with than PUT, and we'll support
both. Start with the test - `public function testPATCHProgrammer()`:

TODO CODE

Copy the inside of the PUT test: they'll be almost identical.

If you follow the rules with PUT, then if you don't send `tagLine`, the
server should nullify it. Symfony's form system works like that, so our PUT
is acting right. Good PUT!

But for PATCH, let's *only* send `tagLine` with a value of `bar`. When we
do this, we expect `tagLine` to be `bar`, but we also expect `avatarNumber`
is still equal to 5. We're not sending `avatarNumber`, which means: don't
change it. And change the method from `put()` to `patch()`:

TODO CODE

In reality, PATCH can be more complex than this, and we talk about that
in our other REST screencast (see [The Truth Behind PATCH](http://knpuniversity.com/screencast/rest/patch#the-truth-behind-patch)).
But *most* API's make PATCH work like this.

Make sure the test fails - filter it for `PATCH` to run just this one:

```bash
phpunit -c app --filter PATCH
```

Yep: 405, method not allowed. Time to fix that!

## Support PUT and PATCH

Since PUT and PATCH are *so* similar, we can handle them in the same action.
Just change the `@Method` annotation to have a curly-brace with `PUT` *and*
`PATCH` inside of it:

TODO CODE

Now, this route accepts PUT or PATCH. Try the test again:

```bash
phpunit -c app --filter PATCH
```

Woh, 500 error! Integrity constraint: `avatarNumber` cannot be null. It *is*
hitting our endpoint and because we're not sending `avatarNumber`, the form
framework *is* nullifying it, which eventually makes the database yell at us.

The work of passing the data to the form is done in our private `processForm()`
method. And when it calls `$form->submit()`, there's a *second* argument
called `$clearMissing`. It's default value - `true` - means that any missing
fields are nullified. But if you set it to `false`, those fields are ignored.
That's perfect PATCH behavior. Create a new variable above this line called
`$clearMissing` and set it to `$request->getMethod() != 'PATCH'`:

TODO CODE

In other words, clear all the missing fields, *unless* the request method
is PATCH. Pass this as the second argument:

TODO CODE

Head back, get rid of the big error message and run things again:

```bash
phpunit -c app --filter PATCH
```

Boom! So `PUT` and `PATCH` support with about 2 lines of code. 
