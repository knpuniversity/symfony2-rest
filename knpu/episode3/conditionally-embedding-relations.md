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
and call the new one `TestGETProgrammerDeep`. 
