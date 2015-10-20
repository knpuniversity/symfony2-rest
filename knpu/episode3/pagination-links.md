# Pagination Links

Our program's response is returning a paginated list, we have extra properties for `count`
and `total`. Now we need to add our `next`, `previous`, `first` and `last` links. The whole
response is now serialization of this paginated collection. Adding an `_links` is really easy.
Just add a new `private $_links = array();`. 

In order to add links throw in a new function called `public function addLink()`. This gets
two arguments, the `$ref` which is the name of the link and the `$url` to the link.
`$this_links[$ref] = $url;`. That's enough to get our response to look correct. In our controller
we just need to make this happen!

Let's generate some URLs by first getting some information together about the route we want to create.
The name of the route that we want to link to is the route for this controller, which doesn't have a name,
so let's give it one called `api_programmers_collection`. Copy that new name and set it to our route variable.

Next up is `$routeParams` which represent any parameters that we need to fill in for our route. There are no
curly braces in this route so we'll set this to an empty array. We're writing this way because we'll be able to
use this logic in a second. 

Next, we're going to need to be able to generate links to several different pages. To do this, create a new variable
that's an anonymouse function, `$createLinkUrl = function ()` which will take in the `$targetPage` you want to link to.
And by using the `$route` and `$routeParams` this will be able to generate that url. We can do that by saying
`return $this->generateURL()` passing it the `$route` and we'll do an `array_merge()` of the `routeParams` we have,
which right now is none. And finally a new entry for the target page. Symfony's router will fill in any wildcards
we might have via `routeParams` and because it recognizes that there's no curly brace page in the route it will add a
`?page` to the end of that. 

Below we can reuse this to say, `$paginatedCollection->addLink()` which will first make link to the page that we're
on now. This might seem silly, but it's a pretty standard thing to do. And we'll pass in the `$page` variable. 
Copy this line twice. Change the second one to be `first` instead of `self` which should then take us to page 1. 
Change the 3rd one here to be `last` which will take us to `$pagerfanta->getNbPages()`. Nice work, the only things
we're still missing are the `next` and `previous` links.

But, we don't always have a next or a previous page, so we need to make this conditional. To do that create an
if statement, `if($pagerfanta->hasNextPage())` well then of course we want to generate a link to whatever that next
page is with `$pagerfanta->getNextPage()` and make sure the key on that one is `next`.

I'll do this same thing for the `previous` page. `if($pagerfanta->hasPreviousPage())` we'll get it and call that
and call it `prev`. Phew!

With any luck that should be enough to get our test to pass. Rerun it aaaannnddd perfect! This all came together 
so quickly that I would like a little more celebration from Sebastian Bergmann or at least PHPUnit. Remember from
our tests we're actually walking from page 1 to page 2 to page 3 in here and asserting things and it's all working
so nicely!

One last things about the keys `self`, `first`, `last`, `next` and `prev`; these are called the `rels` of the link
and they identify the meaning of the link itself. As long as your client understands that `first` means the first
page of results and `next` means the next page of results then you can communicate to them and they understand 
what the significance of those links are. 

In this case these five are official IANA, meaning that there is a recognized standard of how you should name your
links when you are paginating. 

I would also like to mention that we're going to talk about links a lot more in a future episode where we'll cover
hypermedia, hateoas and having tons of links in an API. 

The last thing I want to do with pagination is make this reusable because there's a big chunk of code here that we're
going to want every single time we paginate. 

Create a new service, inside of the pagination directory create a new PHP class called `PaginationFactory`. Inside
of here add a new `public function createCollection()` which will create the entire final paginated collection
for a resource. There are a few things that we'll need to create this paginated collection resource, like the
`QueryBuilder` and the `$request` object which we'll use that to get what the current page. We'll also need to
know what route we're linking to and any `$routeParams` that we have. And that is it! 

Head over to `ProgrammerController` and copy the logic, remove it and put it into `PaginationFactory.php`. 

To clean things up here we need a few use statements, I'll let these autocomplete on `DoctrineORMAdapter` and
`Pagerfanta`. I can delete the `$route` and `$routeParams` because those should now be passed in now. And
we'll also delete the `$qb` because that's also getting passed in. In fact, I'll move that back to our 
`ProgrammerController` because we'll need it in a second. 

The only other thing that's highlighting bad here is `$this->generateUrl` because we don't have a `generateUrl`
function inside of here. We are going to need to generate urls so we'll need the router. Add a `__construct` function
up here, and we'll inject the `RouterInterface`. I'm using the alt+enter PHPStorm shortcut to initialize that field.
And finally just delete a few of these empty lines here. 

Back down here, change `$this->generateUrl` to `$this->router->generate` and that should take care of our work here.

Next, we'll need to register this fine piece of code as a service. In `app/config/services.yml` add a new key
called `pagination_factory` which will have a class of `PaginationFactory` and the arguments only include `@router`.
We can copy that key and go into our `ProgrammerController` and hook this whole thing up. 

`$paginatedCollection = $this->get('pagination_factory')` `->createCollection()`, pass it the `$qb`, the `$request`,
the name of our route `api_programmers_collection` and we don't have any `routeParams` in this case so let's head 
back over to `PaginationFactory` and make that optional by defaulting it to an empty array. Now, this should look
happy, but it doesn't. It looks more like someone stole it's icecream. Ah, because I forgot to `return $paginatedCollection`
in `PaginationFactory`. The error was complaining that it didn't look like this had a return value. . . and it was
right.

Let's check our test to see if we broke anything. And it still passes! If you want some sweet pagination 
for a resource just create a `queryBuilder`, pass it into the `PaginationFactory`, pass it a create API response
and you'll have a full paginated thing with links. That is awesome!
