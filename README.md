# Search Symfony Bundle

> This repository is part of the ApiSearch project. To get more information
> about it, please visit http://apisearch.io. This a project created with love
> by [Puntmig Development SLU](http://puntmig.com)

This library aims to provide to any Symfony >=3.0 developer a nice configuration
way to create, configure and inject Apisearch php repositories. Check the 
[PHP library documentation](http://github.com/puntmig/php-search) for plain PHP
documentation.

- [Repository](#repository)
- [Event Repository](#event-repository)
- [Transformers](#transformers)
- [Twig macros](#twig-macros)
- [Filter values](#filter-values)
- [Reset index command](#reset-index-command)

## Repository

In this chapter we will see how to create a new repository instance in order to
create a nice connection with the remote server. This repository will be always
created by the Symfony container, so we only have to define some parameters to
make it happen.

Let's open the configuration file.

```yml
search_bundle:
    repositories:
        search:
            secret: 789437
            endpoint: http://api.endpoint.xyz
```

By default, this configuration creates a TransformableRepository instance,
wrapping a HttpRepository instance configured by given endpoint and api secret.

This repository is retrievable by using the service named
`apisearch.repository_search`. The name will always follow this pattern, so
the last `_search` will be always filled with the key of the configured
repository This service is public so can be asked to the container from any
controller or command.

```yml
services:

    my_service:
        class: My\Service\Namespace
        arguments:
            - "@apisearch.repository_search"
```

You can disable HTTP clients if you're not going to work with the HTTP layer.
That will be great if you only work with InMemory repositories.

```yml
search_bundle:
    repositories:
        search_test:
            http: false
            secret: 789437
            endpoint: http://api.endpoint.xyz
```

From now, this documentation will talk about using HTTP layer.
You can create a test client by telling through configuration. If the repository
is created with a test environment, then a special HttpClient will be created to
work with the testing client provided by Symfony client. Of course, in that
case, we don't need any endpoint.

```yml
search_bundle:
    repositories:
        search_test:
            secret: 789437
            test: true
```

You can use as well another Repository implementation. Yours for example. This
service must be defined in the container, and you should define the service name
without the `@` symbol.

```yml
search_bundle:
    repositories:
        search:
            secret: 789437
            endpoint: http://api.endpoint.xyz
            search:
                repository_server: my_repository_service
```

## Event Repository

You can use the event repository as well. By default, and with the previous
default configuration snippet, one event repository will be created and added in
the container with name `apisearch.event_repository_search`. As well, the
last `_search` is just appended, given the repository key.

```yml
services:

    my_service:
        class: My\Service\Namespace
        arguments:
            - "@apisearch.event_repository_search"
```

You can use as well another Event Repository implementation. Yours for example.
This service must be defined in the container, and you should define the service
name without the `@` symbol.

```yml
search_bundle:
    repositories:
        search:
            secret: 789437
            endpoint: http://api.endpoint.xyz
            event:
                repository_server: my_event_repository_service
```

## Transformers

You can actually subscribe some Transformers in order to be able to use the
TransformableRepository. If you don't have any, don't worry, this Repository
class allows you as well to work with native Item methods.

Let's work with the ProductTransformer, so when we index or delete any object by
using the advanced model-agnostic methods, the Transformer is enabled and used.
As you will see, creating a transformer is too simple to make this part of the
documentation much longer.

```yml
services:

    product_transformer:
        class: App\Transformer\ProductTransformer
        tags:
            - { name: apisearch.write_transformer }
            - { name: apisearch.read_transformer }
```

That's it. The first one to subscribe this transformer as a WriteTransformer and
the second one to subscriber it as a ReadTransformer. Remember to implement both
interfaces if needed.

## Twig macros

This package provides you as well a set of basic macros for your aggregations.
Let's imagine that our controller makes a great Query with 2 aggregations and
gets from the repository a Result object. We have aggregated our repository by
color and by size.

```php
/**
 * Our controller
 */
class SearchController extends Controller
{
    /**
     * Search action
     *
     * @return Response
     */
    public function searchAction() : Response
    {
        $query = Query::createMatchAll()
            ->aggregateBy('size', 'size', Filter::AT_LEAST_ONE)
            ->aggregateBy('color', 'color', Filter::AT_LEAST_ONE)
            
        $result = $this
            ->get('apisearch.repository_search')
            ->query($query);
            
        return $this->render('MyBundle:Search:search.html.twig', [
            'result' => $result,
        ]);
    }
}
```

Then, this Result object is passed to the view, and we want a basic aggregation
print, in order to check that good results are being printed properly.

If you go to the PHP documentation and check how a Result object is actually
built internally, you'll notice that, in fact, any kind of view can be build on
top of that object. You can take this base macros as an example as well.

```jinja
{% import "ApisearchBundle:Macros:aggregations.html.twig" as _aggregations %}

{{ _aggregations.printAggregation(result, 'size') }}
{{ _aggregations.printAggregation(result, 'color') }}
```

That simple macro will print something like that

```
Size
[ ] M   (10)
[ ] L   (12)
[ ] XL  (6)

Color
[ ] Blue (1)
[ ] Red  (2)
```

Each line will create the right url, with parameters applied or removed.

## Filter values

As you can see, any filter is applied in the last example, and this is because,
even if we applied a filter clicking by one of these links, filters are not
retrieved from the request and added in the query.

Let's fix it by changing our controller.

```php
/**
 * Our controller
 */
class SearchController extends Controller
{
    /**
     * Search action
     *
     * @param Request $request
     *
     * @return Response
     */
    public function searchAction(Request $request) : Response
    {
        $requestQuery = $request->query;
        $query = Query::createMatchAll()
            ->filterBy('size', 'size', $requestQuery->get('size', []), Filter::AT_LEAST_ONE)
            ->aggregateBy('size', 'size')
            ->filterBy('color', 'color', $requestQuery->get('color', []), Filter::AT_LEAST_ONE)
            ->aggregateBy('color', 'color')
            
        $result = $this
            ->get('apisearch.repository_search')
            ->query($query);
            
        return $this->render('MyBundle:Search:search.html.twig', [
            'result' => $result,
        ]);
    }
}
```

By default, the filter/aggregation name will be the name of the parameter, so if
you add a HTTP query parameter called color with value an array with value
`blue`, then the Query object will take `blue` filter. Then, your aggregations
will look like this, and all your results will contain, minimum, color blue.

```
Size
[ ] M   (10)
[ ] L   (12)
[ ] XL  (6)

Color
[x] Blue (1)
[ ] Red  (2)
```

## Reset index command

By default this bundle enables to a pre-configured command, so you can reset any
of your configured repositories by only adding as argument the repository name.

```bash
Usage:
  puntmig:search:reset-index <repository> [<language>]

Arguments:
  repository            Repository name
  language              Language base for the repository

Options:
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  ...
  
Help:
  Reset your search index. Prepared a clean instance of the index and remove 
  existing objects
```

as you can see, you can define as well the language.

```bash
php bin/console puntmig:search:reset-index search
php bin/console puntmig:search:reset-index search ca
```