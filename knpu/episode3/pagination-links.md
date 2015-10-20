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
`return $this->generateURL()` passing it the `$route` and we'll do an `array_merge()` of the `routeParams` we have. 

