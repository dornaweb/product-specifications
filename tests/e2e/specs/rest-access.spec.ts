import { request as playwrightRequest } from '@playwright/test';
import { test, expect } from '../fixtures';

test.describe( 'REST access', () => {
	test( 'visitors cannot read or change specification data', async ( {
		catalog,
		baseURL,
	} ) => {
		const specification = await catalog.specification( 'Panel type' );
		const set = await catalog.specificationSet( 'Monitors', [
			{ label: 'Display', items: [ specification ] },
		] );
		const visitor = await playwrightRequest.newContext( {
			baseURL,
			storageState: { cookies: [], origins: [] },
		} );

		const attempts = [
			visitor.post( '/wp-json/wp/v2/specifications', {
				data: { name: 'Intruder' },
			} ),
			visitor.post( `/wp-json/wp/v2/specification-sets/${ set.id }`, {
				data: { title: 'Changed' },
			} ),
			visitor.get(
				`/wp-json/wp/v2/specification-sets/${ set.id }?context=edit`
			),
			visitor.post( '/wp-json/product-specifications/v2/settings', {
				data: { settings: { tab_title: 'Changed' } },
			} ),
		];

		for ( const response of await Promise.all( attempts ) ) {
			expect( response.status(), response.url() ).toBe( 401 );
		}

		await visitor.dispose();
	} );
} );
