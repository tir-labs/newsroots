import { addQueryArgs } from '@wordpress/url';

export const usePostEditURL = ( postId ) => {

	if ( ! postId ) {
		return null;
	}

	const editURL = addQueryArgs( 'post.php' , {
		post: postId,
		action: 'edit',
	} );

	return editURL;
};