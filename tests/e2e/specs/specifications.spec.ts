import type { Page } from '@playwright/test';
import { test, expect, adminScreen } from '../fixtures';

const keyOf = ( name: string ) => name.toLowerCase().replace( /\s+/g, '-' );

async function findSpecification( page: Page, name: string ) {
	await page
		.getByRole( 'searchbox', { name: 'Search specifications' } )
		.fill( name );

	return page.getByRole( 'row' ).filter( { hasText: keyOf( name ) } );
}

test.describe( 'Specifications library', () => {
	test( 'creates a specification with a key that follows its name', async ( {
		page,
		catalog,
	} ) => {
		const name = catalog.unique( 'Screen size' );
		const key = keyOf( name );

		await page.goto( adminScreen( '/specifications' ) );
		await page.getByRole( 'button', { name: /Add specification/ } ).click();
		await page
			.getByRole( 'combobox', { name: 'Type' } )
			.selectOption( 'Text' );
		await page.getByRole( 'textbox', { name: 'Name' } ).fill( name );

		await expect(
			page.getByRole( 'textbox', { name: 'Key' } )
		).toHaveValue( key );

		await page.getByRole( 'button', { name: /Save and close/ } ).click();

		await expect( await findSpecification( page, name ) ).toBeVisible();

		const created = await catalog.specificationBySlug( key );
		expect( created?.name ).toBe( name );
		catalog.adopt( `/wp/v2/specifications/${ created?.id }` );
	} );

	test( 'refuses a key that another specification already holds', async ( {
		page,
		catalog,
	} ) => {
		const existing = await catalog.specification( 'Weight' );

		await page.goto( adminScreen( '/specifications' ) );
		await page.getByRole( 'button', { name: /Add specification/ } ).click();
		await page
			.getByRole( 'combobox', { name: 'Type' } )
			.selectOption( 'Text' );
		await page
			.getByRole( 'textbox', { name: 'Name' } )
			.fill( catalog.unique( 'Mass' ) );
		await page
			.getByRole( 'textbox', { name: 'Key' } )
			.fill( existing.slug );

		await expect(
			page.getByText(
				`“${ existing.slug }” is already the key of “${ existing.name }”.`
			)
		).toBeVisible();
		await expect(
			page.getByRole( 'button', { name: /Save and close/ } )
		).toBeDisabled();
	} );

	test( 'fast entry adds several specifications in a row', async ( {
		page,
		catalog,
	} ) => {
		const names = [ catalog.unique( 'Width' ), catalog.unique( 'Height' ) ];

		await page.goto( adminScreen( '/specifications' ) );
		await page.getByRole( 'button', { name: /Add specification/ } ).click();

		for ( const name of names ) {
			await page
				.getByRole( 'combobox', { name: 'Type' } )
				.selectOption( 'Text' );
			await page.getByRole( 'textbox', { name: 'Name' } ).fill( name );
			await page
				.getByRole( 'button', { name: /Save and add another/ } )
				.click();
			await expect(
				page.getByRole( 'textbox', { name: 'Name' } )
			).toHaveValue( '' );
		}

		await expect(
			page.getByText( 'New specification · 2 added' )
		).toBeVisible();

		for ( const name of names ) {
			const created = await catalog.specificationBySlug( keyOf( name ) );
			expect( created?.name ).toBe( name );
			catalog.adopt( `/wp/v2/specifications/${ created?.id }` );
		}
	} );

	test( 'a rename keeps the key that was saved first', async ( {
		page,
		catalog,
	} ) => {
		const specification = await catalog.specification( 'Display size' );
		const renamed = catalog.unique( 'Screen diagonal' );

		await page.goto( adminScreen( '/specifications' ) );
		const row = await findSpecification( page, specification.name );
		await row.getByRole( 'button', { name: 'Edit' } ).click();

		const key = page.getByRole( 'textbox', { name: 'Key' } );
		await expect( key ).toHaveValue( specification.slug );
		await expect( key ).not.toBeEditable();

		await page.getByRole( 'textbox', { name: 'Name' } ).fill( renamed );
		await page.getByRole( 'button', { name: /^Save/ } ).click();

		await expect
			.poll(
				async () =>
					( await catalog.specificationBySlug( specification.slug ) )
						?.name
			)
			.toBe( renamed );
	} );

	test( 'deleting a specification shows its impact and removes it everywhere', async ( {
		page,
		catalog,
	} ) => {
		const specification = await catalog.specification( 'Resolution' );
		const kept = await catalog.specification( 'Panel type' );
		const set = await catalog.specificationSet( 'Monitors', [
			{ label: 'Display', items: [ specification, kept ] },
		] );
		const product = await catalog.product( 'Monitor', {
			set,
			values: { [ specification.slug ]: '4K', [ kept.slug ]: 'IPS' },
		} );

		await page.goto( adminScreen( '/specifications' ) );
		const row = await findSpecification( page, specification.name );
		await row.getByRole( 'button', { name: 'Actions' } ).click();
		await page.getByRole( 'menuitem', { name: 'Delete' } ).click();

		const dialog = page.getByRole( 'dialog', {
			name: `Delete “${ specification.name }”?`,
		} );
		await expect(
			dialog.getByText( '1 set uses this specification.' )
		).toBeVisible();
		await expect( dialog.getByText( set.title ) ).toBeVisible();
		await expect(
			dialog.getByText( '1 product holds a value for it.' )
		).toBeVisible();

		await dialog.getByRole( 'button', { name: 'Delete' } ).click();
		await expect( dialog ).toBeHidden();
		await expect( row ).toBeHidden();

		expect(
			await catalog.specificationBySlug( specification.slug )
		).toBeUndefined();
		expect( ( await catalog.structureOf( set.id ) ).groups ).toEqual( [
			expect.objectContaining( {
				items: [ expect.objectContaining( { slug: kept.slug } ) ],
			} ),
		] );
		expect( await catalog.storedValuesOf( product.id ) ).toEqual( {
			[ kept.slug ]: 'IPS',
		} );
	} );
} );
