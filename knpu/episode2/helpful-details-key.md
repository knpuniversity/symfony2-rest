# Helpful Details Key

The end goal is to make our API easy to use so if something goes wrong our clients
can actually debug it without pulling their hair out or having to email us. 

Whenever you throw an exception in PHP there is going to be a message, like "No programmer
found for username". This message is usually just for us as developers. When we're in development
mode we see this message, our clients don't see that message. But, sometimes this message is
useful, like in this case. Having something in the response that says "No programmer found
for username" would help me as a client know that I have the right URL but that nickname I'm 
trying to use is missing.

There are other cases where we don't want to show the exception message. For example, if our
database credentials are incorrect and we're getting a 500 error, we don't want to tell our
client "invalid database credentials" -- that is a detail to hide.

Back in the spec, do we have a field for this? It's not supposed to be title, because that's
supposed to be the same for every type. We could always add our own but if you look there is
something called "detail" which is a "human readable explanation specific to *this* occurence
of the problem." That right there is perfect for our use case!

Back in `ProgrammerControllerTest` let's look for this exact message, "No programmer found for
username".  

At the bottom we'll say `$this->asserter()->assertResponsePropertyEquals();` we'll fill this in
so that when there's a 404 there will be a detail field and it should be set to "No programmer found
for username fake" because that's what's in the URL.

And if we try this out in our terminal, it looks good except it is failing in that spot because 
there is no detail property yet. But no worries, creating that is easy! It all happens inside
of our `ApiExceptionSubscriber`. 

Very simply, we say `ApiProblem->set()` since that allows us to put in new fields. And we'll pass that
`(detail and $e->getMessage());`. And that should do it, but don't do that...because that will expose
the exception message of every exception in our system which is *definitely* not what we want to do.

So there has to be some way for us to determine whether or not it is safe to show the message to the user.
There are a number of different ways to do this, FOSRestBundle has some options where you can whitelist on
a class by class basis. And that is something we could even do here with an if statement that indicates which
classes to show it for. 

Implementing your own interface is also an option! I'll do something simple here which may or may not work
for you, so do think about your project critically when you choose how to do this. I'll check to see
`if ($e instanceof httpExceptionInterface)`. This is used for 404 or 403 errors, so it's typically
things that we are in control of.  

And you can see here that our 404 error implements that interface which will allow it to be caught by that.

Head back to the terminal and test that guy out. Beautiful!

Now we have the opportuntiy to be more helpful to our users whenever we're doing a 404 or if you're creating
an ApiProblem by hand you can set the details field manually.
