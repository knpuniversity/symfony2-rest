# Validation Errors Test

Errors! A lot of things can go wrong - like 500 errors when your database server
is on fire, 404 errors, validation errors, authentication errors and errors in
judgement, like wearing a khaki shirt with khaki shorts, unless you're on Safari.

In your API, handling all of these errors correctly ends up taking some effort.
So that's why we're devoting an entire episode on getting a beautiful, robust error
system into your Symfony API.

First, we'll talk about what most people think of when you mention errors: validation
errors! In episode 1, we created this cool `ProgrammerControllerTest` class where
we can test all of our endpoints for creating a programmer, updating a programmer
deleting a programmer, etc etc. We don't have validation on any of these endpoints
yet.

## Test for a Required Username

So let's add some: when we POST to create, we really need to make sure that the
username isn't blank. That would be crazy! Copy `testPOST()`. Down at the bottom,
paste that, and rename it to `testValidationErrors()`. Get rid of the `nickname`
data field and most of the asserts:

[[[ code('b8e6f27458') ]]]

### Validation Error Status Code: 400

Ok, design time! Writing the test is our time to *think* about how each endpoint
should work. Since we're not sending a `username`,  what status code should the endpoint
return? Use 400:

[[[ code('5d079054ca') ]]]

There are a few other status codes that you could use and we
[talk about them in our original REST series](http://knpuniversity.com/screencast/rest/errors#writing-the-test).
But 400 is a solid choice.

### Validation Errors Response Body

Next, what should the JSON content of our response hold? Let me suggest a format.
Trust me for now - I'll explain why soon. We need to tell the client what went wrong -
a validation error - and what the validation errors are.

Use `$this->asserter()->assertResponsePropertiesExist()` to assert that the response
will have 3 properties. The first is `type` - a string error code - the second is
`title` - a human description of what went wrong - and the third is `errors` - an
array of all of the validation errors:

[[[ code('f8ee2c5c5d') ]]]

Don't forget to pass the `$response` as the first argument. Now, think about that
`errors` array property. I'm thinking it'll be an associative array where the keys
are the fields that have errors, and the values are those errors. Use the
asserter again to say `assertResponsePropertyExists()` to assert that `errors.nickname`
exists:

[[[ code('5431e2a773') ]]]

Basically, we want to assert that there *is* some error on the nickname field, because
it should be required. Actually, go one step further and assert the exact validation
message. Use `assertResponsePropertyEquals` with `$response` as the first argument,
`errors.nickname[0]` as the second and, for the third, a nice message, how about
"Please enter a clever nickname":

[[[ code('1e1a0e1a14') ]]]

Why the `[0]` part? It won't be too common, but one field could have multiple errors,
like a username that contains invalid characters *and* is too short. So each field
will have an array of errors, and we're checking that the first is set to our clever
message.

And since we *are* sending a valid `avatarNumber`, let's make sure that there is
*no* error for it. Use `assertResponsePropertyDoesNotExist()` and pass it `errors.avatarNumber`:

[[[ code('4a319ea551') ]]]

Love it! We've just planned how we want our validation errors to work. That'll make
coding this a lot easier. 
