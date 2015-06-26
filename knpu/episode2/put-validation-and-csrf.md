# PUT Validation and CSRF Tokens

Validation for `newAction()`, check! Now let's repeat for `updateAction`. And that's
not much work - we just need to add the whole `if (!$form->isValid())` block. I know
you hate duplication, so copy the inside of that `if` statement, head to the bottom
of the class, and add a new `private function createValidationErrorResponse()`. We'll
pass it the `$form` object, and we should type-hint that argument with `FormInterface`
because we're good programmers! Paste the stuff here:

[[[ code('18d9fd5cc5') ]]]

Cool! Any time we have a form, we can pass it here and get back a perfectly consistent
validation error response. Go back up to `newAction()` and use this:
`return $this->createValidationErrorResponse()` and pass it the `$form` object:

[[[ code('bf62921592') ]]]

Copy those three lines and repeat in `updateAction()`:

[[[ code('d6657cc64f') ]]]

We *could* write a test for this, but we've centralized everything so well, that
I'm confident that if it works in `newAction`, it works in `updateAction()`. Basically,
I think that's overkill. But we *should* run-run our test:

```bash
bin/phpunit -c app --filter testValidationErrors
```

*All* good. Now re-run *all* the tests:

```bash
bin/phpunit -c app
```

Oh! They break immediately! The POST is failing with a 400 response: invalid CSRF
token - we saw this a few minutes ago. Every endpoint is failing because we're never
sending a CSRF token.

Symfony forms *always* expect a token. But because we're building a stateless, or
session-less API, we don't need CSRF tokens. You *would* need it if you have a JavaScript
frontend that's relying on cookies to authenticate, but you don't need it if your
API doesn't store the user in the session.

Let's turn it off. Inside `ProgrammerType`, in `setDefaultOptions()` - or `configureOptions()`
if you're on a newer version of Symfony - set `csrf_protection` to false:

[[[ code('986d2e6da5') ]]]

That'll do it! Try the tests:

```bash
bin/phpunit -c app
```

Back to green! If you're using your form types for HTML pages *and* on your API,
you won't want to set `csrf_protection` to false inside the class - that'll remove
it everywhere. Instead, you can pass `csrf_protection` in as an option in the third
argument to `createForm()` in your controller. Of you can do something fancier like
a [Form Type Extension](http://symfony.com/doc/current/cookbook/form/create_form_type_extension.html)
and control this option on a global basis.

FOSRestBundle does something similar to this. In the [View Layer](http://symfony.com/doc/current/bundles/FOSRestBundle/2-the-view-layer.html#csrf-validation)
part of their docs, they show a configuration option that disables CSRF protection
based on a role the user has. The idea is that only users that are authenticated
via the sessionless-API would have the role you put here. Cool idea.
