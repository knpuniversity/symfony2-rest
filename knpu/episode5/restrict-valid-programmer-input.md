# Security: Restrict the programmer Input

When we want to successfully create a battle, we also want to add some
validation because the project ID and programmer ID fields being sent up here
are required. Now, our validation system is air tight. The code we're using in
our controller is all reasonable code, so I'm not really that worried about
validation working or not working. I'd definitely add validation, and we're
going to, but I might not in reality this time write a test for it. However, we
are going to do a twist on validation, so I am actually going to write a new
test in the bottom of battle control test for validation.

So, how about a new public function test post battle validation errors. I'll
copy the first bits of the previous function – that's create my data and make
my request – but create the project, delete the project, and instead send up
null for the project ID. Now that should be invalid, so we're going to want to
add this arrow assert equals that the status code is equal to 400. This is the
exact same thing we did before in programmer controller test.

As a reminder, we have this really cool error format called API problem. If you
look in programmer controller test, and search for errors, you'll see us
testing for that error format. You get back a JSON response, which has an
errors key. And every field under that that has an error is represented, and
that even has an array of fields under it. Technically, a single field might
have multiple validation errors.

So in our case, I'm first going to say this error asserter – arrow, assert
response property exists. We're going to look and assert that there is an
errors.projectid field because that should have a validation error. Next, I'll
do this error asserter – arrow, assert response property equals, and
specifically errors.projectid0 – so the first and only error for this field
should be equal to the message this value should not be blank. Why that
specific message? That's the default message from Symphony's validation system
when you make a field required. We could customize that message if we want to,
but when we add the not blank validator, that's the default message for it. I
just know it's going to be that way.

Before we implement that, copy that method name and run a test
vendor/bin/phpunit--filter, past the method, and there we go. It explodes with
a 500 error because the battle manager battle is missing in the instance of a
project because we're missing the project ID. So that's what we expected. We do
not want that 500 error.

Fixing this is really simple. Go to battle model – remember, this is what
handles the input for our API, so this is where we want to add the rules.
Again, we add validation via annotations and any time we add annotations we
need a use statement for those annotations. You'll look that up in the
documentation or cheat by saying use and then just saying one of the annotation
classes you know exists – like not blank – and then deleting the last part and
saying as assert. That's typically how we alias those things.

Now above project we can say add assert not blank and the same thing above
programmer – not blank. Perfect. We run the test and now it passes. That was
super normal. We did the same exact type of thing with programmer. But now I
have a question for you. In battle controller test, at the very beginning of
the function, I want you to create a new user in the database called someone
else. So far, every programmer that we've created has been owned by Weaver
Ryan, which is a user that we build at the beginning of every method. We did
that because I'm only allowed to start battles with programmers that I created.
I can't start a battle with a programmer that you created.

So now let's create a programmer that's owned by someone else. The problem is,
we're authenticating ourselves as Weaver Ryan. So basically, we're about to try
to start a battle with a programmer that we don't own. When that happens, we
should get a validation exception. In this instance, this programmer ID should
be invalid. In the same way that a blank programmer ID is invalid, passing
somebody else's programmer ID is also invalid. I want to pretend like that
programmer ID doesn't even exist.

So how do we do this? Adding not blank is easy, but how do you say, "I need
this programmer to be a real programmer." It turns out you don't do this with
annotations. This is going to be handled by the form itself. Built into this
entity type is a little bit of validation that says, "Hey, you need to pass me
a valid ID for programmer and project."

Let me show you what this is going to look like. First, let's make sure we do
get an error on the project ID field. I'll do assert response property equals
programmer ID zero and this should be equal to some error message. Right now,
some error message. If you run the test now, it fails because, as you can see,
we do not have a programmer ID error message in there. So far, we're missing a
bit of security in our application. We are allowing other people to create
battles using other people's programmers.

It turns out fixing this is pretty easy. Ultimately, all we need to do in
battle type is restrict the programmers that are valid to programmers that are
owned by me. By default, the entity type is going to allow any programmer IDs
in there. We just need to shrink that list to be smaller. We do that by passing
a custom query builder to this entity type that will return the smaller array
of results that our programmers owned by my user.

So, a couple of quick, small steps. First, in battle controller, we haven't
added it yet, but clearly we need to be logged in before we start a battle. So
add this arrow, deny access unless granted, role user – which is a role that
all of my users have. Second, since we're going to need to filter our
programmers down by programmers that are owned by my user, we're going to need
to pass the currently authenticated user into the form. So add a third argument
to create form, which is a little known options array, and we can actually just
invent a new option. This is not a core feature of Symphony – I'm just
inventing this – called user. We'll set that to this arrow, get user.

Now if you want to pass on a custom option to your form like that, you can. But
in battle type, in configure options; you need to say that this option is
allowed. The way you do that is by saying resolver arrow set required user.
That basically means that whoever uses this form must pass in a user option.
You can even go on to specify that needs to be a user object, but we'll just
trust that it is. Once you've done those two things in build form, we're now
able to access the user object by saying user equals options, left square
bracket, user. So that has nothing to do with APIs. That's just a way to pass
on options to your form.

Cool. On a programmer, let's filter this. Add a query builder option and set
that to an anonymous function with the programmer repository as the first
argument. All that repo – and also use the user objects. We have access to it
from inside this function. This needs to return a query builder that will only
return programmers owned by my user. I could just create the query builder
right here and return it, but I like to keep my queries inside my programmer
repository. So instead, I'm going to call a new method under repository, called
create query builder for user and pass up the user object.

Copy that method name, hold command, click program repository, and let's create
that. Public function, create query builder for user, which accepts user
arguments, and return this arrow, create query builder, and we will alias it to
programmer. And then very simple end ware – programmer.user equals : user and
we'll call set parameter user, and that's it.

The whole flow is that in our controller we pass the user to the form and the
form creates a custom query builder using that user, which ultimately calls out
to our repository to create that new filtered query. We want the query here to
keep things organized. The end result of this is that you can no longer just
pass any programmer ID to this field. You have to pass a programmer ID that
matches a programmer owned by my user. If you don't, this will cause a
validation error automatically. There is security built into this.

So let's try it out to see. Go back to the terminal, rerun the test, and it
fails. But that's because we don't have the real message in there yet. It fails
with this value is not valid. That is the standard error message you get if you
submit a value to a form field which is completely invalid – for example, a
programmer ID that is not in a valid list of programmers. Another common way
this fails is if you have a number type and you pass a string to it. That's
just not a valid value.

So take that string, paste that into our test because that is what we're
expecting and then go back and run it. So the moral of the story is, use
validation on your class like normal, but also realize that Symphony's got your
back. It has some sanity validation on your individual form fields and those
will go right into your normal form errors like everything else.
