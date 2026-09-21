/**
 * WordPress dependencies
 */

import { createReduxStore, register } from '@wordpress/data';

import reducer from './reducer';
import * as selectors from './selectors';
import * as actions from './actions';
import * as resolvers from './resolvers';

import { STORE_NAME, DEFAULT_STATE as initialState} from './constants';



/**
 * Block editor data store configuration.
 *
 * @see https://github.com/WordPress/gutenberg/blob/HEAD/packages/data/README.md#registerStore
 */
export const storeConfig = {
	reducer,
	selectors,
	actions,
	resolvers,
	initialState
};


/**
 * Store definition for the block editor namespace.
 *
 * @see https://github.com/WordPress/gutenberg/blob/HEAD/packages/data/README.md#createReduxStore
 */
export const store = createReduxStore( STORE_NAME, storeConfig );

export const registerGovpackStore = () => {
	register( store );
}
