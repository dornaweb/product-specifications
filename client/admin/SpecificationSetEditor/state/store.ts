import { createReduxStore, register } from '@wordpress/data';
import { actions } from './actions';
import { reducer } from './reducer';
import { selectors } from './selectors';

export const STORE_NAME = 'product-specifications/specification-set-editor';

export const store = createReduxStore( STORE_NAME, {
	reducer,
	actions,
	selectors,
} );

register( store );
