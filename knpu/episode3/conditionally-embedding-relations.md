# Conditionally Embedding Relations

I feel like we deserve a reward after that last chapter. So here it is, Once upon a time
I worked for a client that had a really interesting request which would totally violate REST
but I kinda liked the idea. They said, "When we have one object that relates to another
object [kinda like our programmer relates to this user here] sometimes we want to embed the
user in the response and sometimes we don't. In fact, we want our user [client] to tell us via
a query parameter, whether or not they want to embed related objects." This client's idea
violates REST because you now have two different urls that return the same resource, it's just
a different represenatation. So there's some rules that you are bending but if this is useful
for you then I say go for it. The code to implement this is almost nothing. 

We'll start by writing a quick test and I'll get that started by copying part of `TestGETProgramer`
and call the new one `TestGETProgrammerDeep`. Here add a query parameter called `?deep`, and if
you say `?deep=1` then we expose more embedded objects. And we're going to use the asserter
to say `assertResponsePropertyExists()`, pass that the `$response` and the property we're going to
do is the programmer user. So we should hav ea `user.username` property so that user should be a user
object. 

Looking over in our browser right now we definitely do not have that. There are only two things we need
to do to add it. First, we need to expose this with `Serializer\Expose()`, but if we do that it will
show up all the time which is not what we want. To avoid that add `@Serializer\Groups()` and add a new
group to that called `deep`. 

The idea here is that when you serialize every single property is assigned to one or more groups. If you don't
have the serialzer groups annotation then all of your properties are in a group automatically called `Default`
with a capital 'D'. Normally when you serialize you are serializing all the properties in the group `Default`,
but you could also serialize all properties in another group or a collection of groups. 

You may have also noticed that my password is getting exposed on my user entity object, I don't really care 
about that but it does bother me enough that I have to fix it. Add the use statement and get the `Expose`
part off of there and instead write `as Serializer;` and then add an `@Serializer\ExclusionPolicy()` above
our user for `all`. Then `Expose` the username. 

Back in `Programmer.php` if I take off this groups temporarily we will see just the username when we refresh
our browser. When I put it back and refresh we see nothing because we're now serializing in the `Default` group
so it won't serialize this user property. 

The last piece of the puzzle is, how do I serialize different groups? To answer that head over to the 
`ProgrammerController` find the `showAction` follow `createApiResponse` into the `BaseController` and find 
`serialize`. When you serialize we have this serialization context which are options for serialization,
there's not much on here but there is a way to control your groups here. First, get the `$request` object
by going out to the `request_stack` service and say `getCurrentRequest`. Now create a new `$groups` variable
and set that to `Default`, make sure you capitalize the 'D', because we always want to serialize at least
the `Default` group.

Now say ``if ($request->query->get('deep'))` is true then we just add our group `deep` to `$groups`. 
Finish this up with `$context->setGroups($groups)`. And just like that we're able to conditionally show
other fields. Sweet!

Rerun our test for `testGetProgrammerDeep` it passes! To really prove it refresh the browser and we won't
see the user unless we add `?deep=1` to the url. There you have a cool way to leverage groups. 
