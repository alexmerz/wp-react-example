# Tutorial: How to use an React app in the WordPress backend with authorized access to the Rest API

Using React and the WP Rest API in the WordPress backend is a great way to improve the user experience and also makes developement faster.
But it is also a little bit tricky to implement, because the WP architecture is still somewhat old school in some parts and assumes you are always rendering
static web pages on the server side. So what we have to learn and handle in this tutorial are the following things:

* Everytime you rebuild a React Apps the file names change. So we need to find a way to dynamically
  include the JS and CSS files in our backend page.
* The Rest API works without any authorization by default - but only gives us access to published data. That's fine for the frontend, but not for the backend.
  For actual backend action we need authorization. That is done by a *nonce* as part of the request. As noted above, WP assumes we render on the server side,
  so we need to find a way to pass that nonce to the React app.

## About the example code

The exampe code in the repository shows you the minimal steps to get a React app running in the
WP backend with authorization for the Rest API.

It will just display a page in the WP backend, that will show a list of posts in draft status using
the Rest API. Draft posts can be fetched via the Rest API only by authorized users.

That sounds simple, but we have these challenges to solve:

1. How to fetch the nonce for the Rest API?
2. How to pass the nonce to the React app?
3. How to integrate the React app into the WP backend page?

## Install the example

1. Clone this repository into your WordPress plugins folder.
2. Run `npm install` in the `wp-example` folder.
3. Run `npm run build` in the `wp-example` folder.
4. Activate the plugin in the WP backend.
5. Done!

## Usage

Select the "React Example" menu item in the WP backend. It will you just display a list of draft posts using React app.

## How does it work? The PHP plugin part

First the plugin registers a new menu item in the WP backend and adds a new page template via `plugin_menu()`. In the page template (`react_page()`)
we add the React App JS and CSS files dynamically instead of hard coding these files name. This avoids constantly
updating the file names in the plugin after every build of the app. The actual generated HTML in the page template
is just a div element with the ID *root*. This ID is used by the React app to mount the app. This is everything we need to do to put the React app 
into the backend.

Passing a nonce to the React app is a little bit tricky. We fetch the nonce in the page template with `wp_create_nonce('wp_rest')`, the *wp_rest* value
is required by the built-in Rest API. It is then passed to the React app
via a global JS variable using `wp_add_inline_script()`. This variable is assigned in front of the first included JS file, the rendered output will look
like this:

```html
<script id='wp-react-2c5618fe9c5a006af94033b0ac661381-js-before'>
document.WpReact = { "nonce" :  "f4fbbf1b53"};
</script>
<script src='http://example.com/wp-content/plugins/wp-react-example/wp-example/build/static/js/main.0d3963f6.js' id='wp-react-2c5618fe9c5a006af94033b0ac661381-js'></script>
```

The React app can then access this variable whenever it is needed.

Notice: The generated nonce is speciffic for the user. If you have trouble with the Rest API, check if the user has the correct permissions to actually do a specific action.

## How does it work? The React app part

The React app was created via `npx create-react-app` and is located in the `wp-example` folder of the plugin. Bootstrap is used for styling.

Important: You need to set the `homepage` property in the `package.json` to the path of the plugin folder like this, or React will not find its assets:

```json
    "homepage": "wp-content/plugins/wp-react-example/wp-example/build",
```

The React app is a simple app with a very short App Component (`App.js`) file that includes the `MyListComponent` component. All the magic happens in that component.
We use the `componentDidMount()` method to fetch the draft posts from the Rest API.

```js
    componentDidMount() {
        window.fetch('/wp-json/wp/v2/posts?status=draft',
            {
                headers: {
                    'X-WP-Nonce': document.WpReact.nonce
                }
            })
            ...
```	

You will notice that we pass the nonce as part of the request header. The value for the nonce is directly taken from the global JS variable we created in the PHP plugin part. 
When the request was sucessful, the posts are stored in a state variable and then rendered in the `render()` method:

```js
    render() {    
        if(!this.state.posts || this.state.posts.length === 0) { 
            return (<div>No posts!</div>);
        }    
            
        const posts = this.state.posts.map((post) => <ListGroup.Item>{post.title.rendered}</ListGroup.Item>);
        return (<ListGroup variant="flush">
            {posts}
        </ListGroup>)
    }
```

That's all!

## Summary

To use React in the WP backend comfortable you need to do the following things:

* Don't hardcode your JS and CSS file names in the plugin. Use `glob()` to find the files and then include them dynamically with
 `wp_enqueue_script()` and `wp_enqueue_style()`.
* Both functions require a handle, use a `md5()` hash of the file name for the handle.
* Create a nonce with `wp_create_nonce('wp_rest')` and generate a string with JavaScript code that assigns the nonce to a global JS variable
with `wp_add_inline_script()`.
* Your React app can now safely assume that the nonce is available in that global JS variable.
* In your Rest API calls add the nonce either as a query parameter *_wpnonce* or as an request header with the name *X-WP-Nonce*.

*Author:* *Alexander Merz, last updated 02-01-2023*
