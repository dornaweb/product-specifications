import type { Page } from '@playwright/test';
import { test, expect, adminScreen } from '../fixtures';

function specificationsTable( page: Page ) {
	return page.locator( '.ps-product-specifications__table' );
}

function specificationValue( page: Page, name: string ) {
	return page
		.locator( '.ps-product-specifications__row' )
		.filter( { has: page.getByRole( 'rowheader', { name } ) } )
		.locator( '.ps-product-specifications__value' );
}

test.describe( 'Storefront table', () => {
	test( 'hidden groups and specifications stay off the table', async ( {
		page,
		catalog,
	} ) => {
		const shown = await catalog.specification( 'Panel type' );
		const hiddenItem = await catalog.specification( 'Supplier code' );
		const inHiddenGroup = await catalog.specification( 'Warehouse bin' );
		const set = await catalog.specificationSet( 'Monitors', [
			{
				label: 'Display',
				items: [ shown, { ...hiddenItem, visible: false } ],
			},
			{ label: 'Internal', visible: false, items: [ inHiddenGroup ] },
		] );
		const product = await catalog.product( 'Monitor', {
			set,
			values: {
				[ shown.slug ]: 'IPS',
				[ hiddenItem.slug ]: 'SUP-1',
				[ inHiddenGroup.slug ]: 'B-12',
			},
		} );

		await page.goto( product.permalink );

		await expect( specificationValue( page, shown.name ) ).toHaveText(
			'IPS'
		);
		const table = specificationsTable( page );
		await expect( table ).not.toContainText( hiddenItem.name );
		await expect( table ).not.toContainText( 'Internal' );
		await expect( table ).not.toContainText( inHiddenGroup.name );
	} );

	test( 'WooCommerce product data is read from the product', async ( {
		page,
		catalog,
	} ) => {
		const sku = catalog.unique( 'MON' ).replace( ' ', '-' );
		const specification = await catalog.specification(
			'Article number',
			'woocommerce',
			{ source: 'sku' }
		);
		const set = await catalog.specificationSet( 'Monitors', [
			{ label: 'Identity', items: [ specification ] },
		] );
		const product = await catalog.product( 'Monitor', { sku, set } );

		await page.goto( product.permalink );

		await expect(
			specificationValue( page, specification.name )
		).toHaveText( sku );
	} );

	test( 'a product without a set shows no specifications tab', async ( {
		page,
		catalog,
	} ) => {
		const product = await catalog.product( 'Cable' );

		await page.goto( product.permalink );

		await expect(
			page.getByRole( 'heading', { level: 1, name: /Cable/ } )
		).toBeVisible();
		await expect(
			page.getByRole( 'tab', { name: 'Specifications' } )
		).toHaveCount( 0 );
		await expect( specificationsTable( page ) ).toHaveCount( 0 );
	} );

	test( 'the tab title follows the settings', async ( { page, catalog } ) => {
		await catalog.keepSettings();
		const specification = await catalog.specification( 'Panel type' );
		const set = await catalog.specificationSet( 'Monitors', [
			{ label: 'Display', items: [ specification ] },
		] );
		const product = await catalog.product( 'Monitor', {
			set,
			values: { [ specification.slug ]: 'IPS' },
		} );

		await page.goto( adminScreen( '/settings' ) );
		await page
			.getByRole( 'textbox', { name: 'Specifications tab title' } )
			.fill( 'Technical data' );
		await page.getByRole( 'button', { name: 'Save settings' } ).click();
		await expect(
			page.getByRole( 'button', { name: 'Save settings' } )
		).toBeDisabled();

		await page.goto( product.permalink );

		await expect(
			page.getByRole( 'tab', { name: 'Technical data' } )
		).toBeVisible();
	} );
} );
