import React from 'react';
import ListGroup from 'react-bootstrap/ListGroup';


class MyListComponent extends React.Component {
    constructor(props) {
        super(props);
        this.state = {posts: []}; // the darft post are stored here
    }

    componentDidMount() {
        // fetch the posts from the REST API
        window.fetch('/wp-json/wp/v2/posts?status=draft',
            {
                headers: {
                    'X-WP-Nonce': document.WpReact.nonce    // nonce is required for REST API calls to be authorized
                }                                           // the value was declared globally
            })
            .then(response => response.json())
            .then(
                (posts) => {
                    this.setState({posts: posts}); // store the posts in the state
                },
                (error) => {
                    console.log(error);
                }
            );
    }

    render() { // if we have posts, render them, otherwise render a message
        if(!this.state.posts || this.state.posts.length === 0) { 
            return (<div>No posts!</div>);
        }    
            
        const posts = this.state.posts.map((post) => <ListGroup.Item>{post.title.rendered}</ListGroup.Item>);
        return (<ListGroup variant="flush">
            {posts}
        </ListGroup>)
    }
}

export default MyListComponent;