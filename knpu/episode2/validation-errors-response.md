# Sending back Validation Errors

Time to add validation errors and get this test passing. First, add the validation.
Open the `Programmer` class. There's no validation stuff here yet. Before adding
some validation annotations, we need a `use` statement. I'll `use` the `NotBlank`
class directly, let PhpStorm auto-complete that for me, then remove the last part
and add `as Assert`:

[[[ code('cfda552043') ]]]

That's a little shortcut to get the `use` statement you'll find in the validation
docs.

Now, above `username`, add the `@Assert\NotBlank` part with a `message` option. Go
back and copy the clever message and paste it:

[[[ code('c0149566a5') ]]]

## Handling Validation in the Controller

Ok! That was step 1. Step 2 is to go into the controller and send the validation
errors back to the user. We're using forms, so handling validation is going to look
pretty much identical to how it looks in traditional web forms.

Check out that `processForm()` function we created in episode 1:

[[[ code('cd77c21b43') ]]]

All this does is `json_decode` the input and call `$form->submit()`. That does the
same thing as `$form->handleRequest()`, which you're probably familiar with. So
after this function is called, the form processing has happened. After it, add an
`if` statement with the normal `!$form->isValid()`:

[[[ code('669057db31') ]]]


