# Designing (Testing) the Create Battle Endpoint

In the controller test directory, create a new class: `BattleControllerTest`. Make
it extend the fancy `ApiTestCase` we've been working on. Start with
`public function testPostCreateBattle`.

Go steal some code from `ProgrammerControllerTest`. Copy the setup method that creates
a user so that we can send the Authorization header.

## Create a Proejct & Programmer

For this endpoint, there are only two pieces of information we need to send: which
programmer and which project will battle. So before we start, we need to create these.
Add `$this->createProject()` and give it a name: `my_project` - that doesn't matter.

If you open this method, this method simply creates a new `Project` and flushes it
to the database.

Next, create the programmer: `$this->createProgrammer()`. This takes an array of
information about that programmer. Hmm, let's call him `Fred`. Pass `weaverryan`
as the second argument: that will be the user who *owns* Fred. Eventually, we'll
need to restrict this endpoint so that we can only start battles with *our* programmers.

## Sending the Related Fields

Ok, let's send a POST request! In `ProgrammerControllerTest`, it was easy: we sent
3 scalar fields: `nickname`, `avatarNumber` and `tagLine`. But now, it's a little
different: we want to send data that identifies the related programmer and project
*resources*. Should we send the id? Or the programmer's nickname?

Well, like normal with API's... it doesn't matter. But sending the id's makes sense.
Create a new `$data` array with two fields: `project` set to `$project->getId()`
and `programmer` set to `$programmer->getId()`. I'm calling the keys `programmer`
and `project` for obvious reasons: but they could be anything. *We* are in charge
of naming the fields whatever we want. Just be sane and consistent: please don't
call the fields `bob` and `larry` - everyone will hate you.

Finally, make the request: `$response = $this->client->post()`. For programmers,
the URL is `/api/programmers`. Stay consistent with `/api/battles`. Pass an array
as the second argument with a `body` key set to `json_encode($data)` and a `headers`
key set to `$this->getAuthorizedHeaders('weaverryan')`. That will send a valid JSON
web token for the `weaverryan` user - we created that in the previous course.

## Asserts!

Whew, okay. That's it. So even though Battle is dependent on two other resources,
it works pretty much the same. Add some basic asserts: `$this->assertEquals()` that
the 201 will be the response status code. In `Battle`, one of the fields that should
be returned is `didProgrammerWin`. Make sure that exists with
`$this->asserter->responsePropertyExists` and look for the `didProgrammerWin`. 

I'll also add a todo to check for the `Location` header later. Remember, when you
create a resource, you're *supposed* to return a `Location` header to the URL where
the client can view the new resource. We don't have a GET endpoint for a battle yet,
so we'll skip this.

The hard work is done: we've designed the new endpoint. Let's bring this to life!
