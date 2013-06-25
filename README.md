# Backboned v2

Backboned v2 is the follow-up to [Backboned](https://github.com/herschel666/Backboned) — an AJAX-Wordpress-theme powered by Backbone.js. I decided to create a new repository for this one, as the differences in the concept and the code are too large.

## How does it work?

Backboned v2 is a mixture of [Require.js](http://requirejs.org) for modularization and script loading, [Backbone.js](http://documentcloud.github.io/backbone/) as a client-side MVC and [Mustache](http://mustache.github.io) for templating (client- and server-side). While using the History-API, Backbone.js fetches the JSON-data and renders it into the DOM. On the server-side a controller sends the data due to the request type. There are three different types of requests:

1. The classical, synchronous request gets the website frame (with templates inside the DOM), the javascripts and the styles.
2. The asynchronous request gets the page's data in JSON-format.
3. The request with $_GET['\_escaped\_fragment\_']-parameter set, gets a static version of the page. Mustache is used on the server-side for rendering the page.

With this approach, the hashbang-method for pointing crawlers to the static content is used without hashbang-URIs.

Older browsers, that don't support the History-API, always make a round-trip after each anchor click. In the moment I'm not planning to include a workaround with hash-fragment-URIs for the old ones.

## What is this \_escaped\_fragment_-shizzle?

It's a way to serve the static content of a client-side rendered Javascript-application and make it thereby possible for search engines to crawl the application's content ([read more about it here](https://developers.google.com/webmasters/ajax-crawling/docs/specification?hl=de)).

But in contrast to the specification Backboned v2 doesn't make use of hashbang-URIs. The `_escaped_fragment_`-parameter is just a vehicle for identifying requests made by search engine crawlers.

## How to use the theme?

Download the repo, put the folder into the `wp-content/themes/`-directory of your Wordpress-installation and make sure, the cache-folder is writeable. Otherwise Mustache will have to render a new template each time an `_escaped_fragment_`-request comes. This may really slow things down!

## Why not to use it?

Backboned v2 is in a really early state. Furthermore it's more like a proof-of-concept — is it possible to ajaxify a Wordpress-site with Backbone.js?! That's why you can't treat it like one of the fancy, blown-up themes from the official Wordpress-theme-directory (no disrespect!).

But if you're a developer and you're looking for this exact combination — Wordpress + Backbone.js — then you're in the right spot. Download it and take it as a base for your own work.

## Who built this?

Yeah, I like this question. Kind of &hellip;

I'm a frontend-developer from Hanover, Germany. You can follow me on [twitter (@Herschel_R)](http://twitter.com/Herschel_R) or [visit my blog](htpp://www.emanuel-kluge.de/). You can even do both if you're more like the danger-seeking kind of a person.

## Where is the interesting license part?

Right here!

Copyright 2013 Emanuel Kluge  
https://github.com/herschel666/Backboned-v2

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

