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

Back in `ProgrammerController` 
