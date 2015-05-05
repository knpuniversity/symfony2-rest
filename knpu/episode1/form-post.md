# Handling data with a Form

So what's different between this API controller and one that handles
an HTML form submit? Really, not much. The biggest difference is that an
HTML form sends us POST parameters and an API sends us a JSON body. But once
we decode the JSON, both give us an array of submitted data. Then, everything
is the same: create a Programmer object and update it with the submitted
data. And you know who does this kind of work really well? Symfony forms.

Create a new directory called `Form/` and inside of that, a new class called
`ProgrammerType`. And I'll quickly make this into a form type by extending
`AbstractType` and implementing the `getName()` method - just to return,
how about, `programmer`.

Now, override the two methods we *really* care about - `setDefaultOptions()`
and `buildForm()`:

[[[ code('1676cfb7f6') ]]]

In Symfony 2.7, `setDefaultOptions()` is called `configureOptions()` - so
adjust that if you need to.

In `setDefaultOptions`, the one thing we want to do is `$resolver->setDefaults()`
and make sure the `data_class` is set so this form will definitely give us
an `AppBundle\Entity\Programmer` object:

[[[ code('b01172299a') ]]]

## Building the Form

In build form, let's see here, let's build the form! Just like normal
use `$builder->add()` - the first field is `nickname` and set it to a `text`
type. The second field is `avatarNumber`. In this case, the value will be
a number from 1 to 6. So we *could* use the `number` type. But instead, use
`choice`. For the `choices` option, I'll paste in an array that goes from
1 to 6:

[[[ code('a82fb34a07') ]]]

### Using the choice Type in an API

Why `choice` instead of `number` or `text`? Because it has built-in validation.
If the client sends something other than 1 through 6, validation will fail.

**TIP** To control this message, set the `invalid_message` option on the field.

For the API, we only care about the keys in that array: 1-6. The labels, like
"Girl (green)", "Boy" and "Cat" are meaningless. For a web form, they'd show
up as the text in the drop-down. But in an API, they do nothing and could
be set to anything.

Finish with an easy field: `tagLine` and make it a `textarea`, which in an
API context, does the exact same thing as a `text` type:

[[[ code('edcac6dba0') ]]]

So, there's our form.
Can you tell this form is being used in an API? Nope! So yes, you *can* re-use
forms for your API and web interface.

## Using the Form

Back in the controller, let's use it! `$form = $this->createForm()` passing
it a `new ProgrammerType` and the `$programmer` object. And now that the
form is handling `$data` for us, get rid of the `Programmer` constructor
arguments - they're optional anyways. Oh, and remove the `setTagLine` stuff,
the form will do that for us too:

[[[ code('e814aec3d3') ]]]

Normally, this is when we'd call `$form->handleRequest()`. But instead, call
`$form->submit()` and pass it the array of `$data`:

[[[ code('5ed5d4fd11') ]]]

Ok, this is really cool because it turns out that when we call `$form->handleRequest()`,
all *it* does is finds the form's POST parameters array and then passes that
to `$form->submit()`. With `$form->submit()`, you're doing the same thing
as normal, but working more directly with the form.

And that's all the code you need! So let's try it:

```bash
php testing.php
```

Yep! The server seems confident that still worked.

## Creating a Resource? 201 Status Code

On this create endpoint, there are 2 more things we need to do. First, whenever
you create a resource, the status code should be 201:

[[[ code('bdf0cc6912') ]]]

That's our first non-200 status code and we'll see more as we go. Try that:

```bash
php testing.php
```

Cool - the 201 status code is hiding up top.

## Creating a Resource? Location Header

Second, when you create a resource, best-practices say that you should set
a `Location` header on the response. Set the `new Response` line to a
`$response` variable and then add the header with `$response->headers->set()`.
The value should be the URL to the new resource... buuuut we don't have an
endpoint to view one Programmer yet, so let's fake it:

[[[ code('dfe7db9425') ]]]

We'll fix it soon, I promise! Don't forget to return the `$response`.

Try it once more:

```bash
php testing.php
```

We're on a roll!
