import { test, expect, adminScreen } from '../fixtures';

test.describe( 'Import and export', () => {
	test( 'an exported specification comes back after an import', async ( {
		page,
		catalog,
		requestUtils,
	}, testInfo ) => {
		test.setTimeout( 180_000 );

		const specification = await catalog.specification( 'Color depth' );

		await page.goto( adminScreen( '/import-export' ) );
		await page
			.getByRole( 'checkbox', { name: 'Include product values' } )
			.uncheck();
		const [ download ] = await Promise.all( [
			page.waitForEvent( 'download' ),
			page.getByRole( 'button', { name: 'Download export' } ).click(),
		] );
		const exportFile = testInfo.outputPath( 'export.json' );
		await download.saveAs( exportFile );

		await requestUtils.rest( {
			method: 'DELETE',
			path: `/wp/v2/specifications/${ specification.id }`,
			params: { force: true },
		} );

		await page.goto( adminScreen( '/import-export' ) );
		await page.getByLabel( 'Package file' ).setInputFiles( exportFile );
		await page.getByRole( 'button', { name: 'Preview import' } ).click();
		await expect(
			page.getByText(
				'Nothing has been changed yet. This is what the import will do:'
			)
		).toBeVisible();
		await page.getByRole( 'button', { name: 'Start import' } ).click();

		await expect( async () => {
			await page.request.get( '/wp-cron.php' );
			await expect(
				page
					.locator( '#woocommerce-layout__primary' )
					.getByText( 'The import is complete.' )
			).toBeVisible( { timeout: 3_000 } );
		} ).toPass( { timeout: 120_000 } );

		const restored = await catalog.specificationBySlug(
			specification.slug
		);
		expect( restored?.name ).toBe( specification.name );
		catalog.adopt( `/wp/v2/specifications/${ restored?.id }` );

		await page.getByRole( 'button', { name: 'Done' } ).click();
	} );
} );
