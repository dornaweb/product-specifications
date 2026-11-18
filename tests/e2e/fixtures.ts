import { test as base, expect } from '@wordpress/e2e-test-utils-playwright';
import { Catalog } from './catalog';

export function adminScreen( path: string ): string {
	return `/wp-admin/admin.php?page=wc-admin&path=${ encodeURIComponent(
		`/product-specifications${ path }`
	) }`;
}

export const test = base.extend< { catalog: Catalog } >( {
	catalog: async ( { requestUtils }, provide, testInfo ) => {
		const catalog = new Catalog(
			requestUtils,
			`${ Date.now().toString( 36 ) }${ testInfo.workerIndex }`
		);

		await provide( catalog );
		await catalog.dispose();
	},
} );

export { expect };
