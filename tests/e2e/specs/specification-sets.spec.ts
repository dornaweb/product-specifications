import type { Page } from '@playwright/test';
import { test, expect, adminScreen } from '../fixtures';

async function openNewSetWithGroup( page: Page, label: string ) {
	await page.goto( adminScreen( '/specification-set/new' ) );
	await page.getByRole( 'tab', { name: 'Specifications' } ).click();
	await page.getByRole( 'button', { name: 'Add group' } ).click();
	await page.getByRole( 'textbox', { name: 'Group label' } ).fill( label );
	await page.getByRole( 'button', { name: /Create group/ } ).click();
}

async function saveSet( page: Page ): Promise< number > {
	await page.getByRole( 'button', { name: 'Save set' } ).click();
	await expect(
		page
			.locator( '#woocommerce-layout__primary' )
			.getByText( 'Specification set saved.' )
	).toBeVisible();
	await expect( page ).toHaveURL( /specification-set%2Fedit.*id=\d+/ );

	return Number( new URL( page.url() ).searchParams.get( 'id' ) );
}

test.describe( 'Specification sets', () => {
	test( 'a new set opens on the General tab', async ( { page } ) => {
		await page.goto( adminScreen( '/specification-set/new' ) );

		await expect(
			page.getByRole( 'tab', { name: 'General' } )
		).toHaveAttribute( 'aria-selected', 'true' );
		await expect(
			page.getByRole( 'textbox', { name: 'Title' } )
		).toBeVisible();
	} );

	test( 'a set without a title is not saved', async ( { page } ) => {
		await openNewSetWithGroup( page, 'Display' );

		await page.getByRole( 'button', { name: 'Save set' } ).click();

		await expect(
			page
				.locator( '#woocommerce-layout__primary' )
				.getByText(
					'Enter a title for this specification set on the General tab.'
				)
		).toBeVisible();
		await expect( page ).toHaveURL( /specification-set%2Fnew/ );
	} );

	test( 'builds and publishes a set from the library', async ( {
		page,
		catalog,
		requestUtils,
	} ) => {
		const specification = await catalog.specification( 'Panel type' );

		await page.goto( adminScreen( '/specification-set/new' ) );
		await page
			.getByRole( 'textbox', { name: 'Title' } )
			.fill( catalog.unique( 'Monitors' ) );
		await page
			.getByRole( 'combobox', { name: 'Status' } )
			.selectOption( 'publish' );
		await page.getByRole( 'tab', { name: 'Specifications' } ).click();
		await page.getByRole( 'button', { name: 'Add group' } ).click();
		await page
			.getByRole( 'textbox', { name: 'Group label' } )
			.fill( 'Display' );
		await page.getByRole( 'button', { name: /Create group/ } ).click();

		await page
			.getByRole( 'searchbox', { name: 'Find a specification' } )
			.fill( specification.name );
		await page
			.getByRole( 'listitem' )
			.filter( { hasText: specification.name } )
			.getByRole( 'button', { name: 'Add to a group' } )
			.click();
		await page.getByRole( 'menuitem', { name: 'Display' } ).click();

		const setId = await saveSet( page );
		catalog.adopt( `/wp/v2/specification-sets/${ setId }` );

		const saved = await requestUtils.rest< { status: string } >( {
			path: `/wp/v2/specification-sets/${ setId }`,
			params: { context: 'edit' },
		} );

		expect( saved.status ).toBe( 'publish' );
		expect( ( await catalog.structureOf( setId ) ).groups ).toEqual( [
			expect.objectContaining( {
				label: 'Display',
				items: [
					expect.objectContaining( { slug: specification.slug } ),
				],
			} ),
		] );
	} );

	test( 'a specification created inside the editor joins the group', async ( {
		page,
		catalog,
	} ) => {
		const name = catalog.unique( 'Refresh rate' );
		const slug = name.toLowerCase().replace( /\s+/g, '-' );

		await openNewSetWithGroup( page, 'Display' );
		await page.getByRole( 'button', { name: 'New specification' } ).click();
		await page
			.getByRole( 'combobox', { name: 'Type' } )
			.selectOption( 'Text' );
		await page.getByRole( 'textbox', { name: 'Name' } ).fill( name );
		await page.getByRole( 'button', { name: /Create and add/ } ).click();

		await expect(
			page.getByRole( 'button', { name: `Move ${ name }` } )
		).toBeVisible();

		const created = await catalog.specificationBySlug( slug );
		expect( created?.name ).toBe( name );
		catalog.adopt( `/wp/v2/specifications/${ created?.id }` );

		await page.getByRole( 'tab', { name: 'General' } ).click();
		await page
			.getByRole( 'textbox', { name: 'Title' } )
			.fill( catalog.unique( 'Monitors' ) );
		const setId = await saveSet( page );
		catalog.adopt( `/wp/v2/specification-sets/${ setId }` );

		expect( ( await catalog.structureOf( setId ) ).groups ).toEqual( [
			expect.objectContaining( {
				items: [ expect.objectContaining( { slug } ) ],
			} ),
		] );
	} );

	test( 'leaving with unsaved changes asks first', async ( { page } ) => {
		await openNewSetWithGroup( page, 'Display' );

		await page
			.getByRole( 'button', { name: 'Cancel', exact: true } )
			.click();
		const dialog = page.getByRole( 'dialog', {
			name: 'Leave without saving?',
		} );
		await dialog
			.getByRole( 'button', { name: 'Stay and keep editing' } )
			.click();

		await expect( dialog ).toBeHidden();
		await expect( page.getByText( 'Unsaved changes' ) ).toBeVisible();

		await page
			.getByRole( 'button', { name: 'Cancel', exact: true } )
			.click();
		await dialog
			.getByRole( 'button', { name: 'Leave and discard' } )
			.click();

		await expect( page ).toHaveURL( /specification-sets$/ );
	} );

	test( 'deleting a set trashes it and keeps its product values', async ( {
		page,
		catalog,
		requestUtils,
	} ) => {
		const specification = await catalog.specification( 'Panel type' );
		const set = await catalog.specificationSet( 'Monitors', [
			{ label: 'Display', items: [ specification ] },
		] );
		const product = await catalog.product( 'Monitor', {
			set,
			values: { [ specification.slug ]: 'IPS' },
		} );

		await page.goto( adminScreen( '/specification-sets' ) );
		await page
			.getByRole( 'searchbox', { name: 'Search sets' } )
			.fill( set.title );
		const row = page.getByRole( 'row' ).filter( { hasText: set.title } );
		await row.getByRole( 'button', { name: 'Actions' } ).click();
		await page.getByRole( 'menuitem', { name: 'Delete' } ).click();
		await page
			.getByRole( 'dialog' )
			.getByRole( 'button', { name: 'Delete' } )
			.click();

		await expect(
			page
				.locator( '#woocommerce-layout__primary' )
				.getByText( 'Specification sets deleted.' )
		).toBeVisible();
		await expect( row ).toBeHidden();

		const trashed = await requestUtils.rest< { status: string } >( {
			path: `/wp/v2/specification-sets/${ set.id }`,
			params: { context: 'edit' },
		} );
		expect( trashed.status ).toBe( 'trash' );
		expect( await catalog.storedValuesOf( product.id ) ).toEqual( {
			[ specification.slug ]: 'IPS',
		} );
	} );
} );
