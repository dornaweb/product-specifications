import type { Page } from '@playwright/test';
import { test, expect } from '../fixtures';
import type { Catalog, CreatedSpecification } from '../catalog';

const PROCESSOR = 'Octa-core 3.2 GHz';

async function submitProductValue(
	page: Page,
	productId: number,
	setTitle: string,
	specification: CreatedSpecification,
	value: string
) {
	await page.goto( `/wp-admin/post.php?post=${ productId }&action=edit` );

	const panel = page.getByRole( 'region', {
		name: 'Product specifications',
	} );
	await panel
		.getByRole( 'combobox', { name: 'Specification set' } )
		.selectOption( { label: setTitle } );
	await panel
		.getByRole( 'button', { name: 'Assign a specification set' } )
		.click();
	await panel.getByLabel( specification.name, { exact: true } ).fill( value );
	await page.locator( '#publish' ).click();

	return panel;
}

async function productWithSavedValue( page: Page, catalog: Catalog ) {
	const specification = await catalog.specification( 'Processor' );
	const set = await catalog.specificationSet( 'Laptops', [
		{ label: 'Performance', items: [ specification ] },
	] );
	const product = await catalog.product( 'Laptop' );

	await submitProductValue(
		page,
		product.id,
		set.title,
		specification,
		PROCESSOR
	);
	await page.waitForURL( /message=1/ );

	return { specification, product };
}

function specificationValue( page: Page, name: string ) {
	return page
		.locator( '.ps-product-specifications__row' )
		.filter( { has: page.getByRole( 'rowheader', { name } ) } )
		.locator( '.ps-product-specifications__value' );
}

test.describe( 'Product specifications', () => {
	test( 'values saved on the product show in the storefront tab', async ( {
		page,
		catalog,
	} ) => {
		const { specification, product } = await productWithSavedValue(
			page,
			catalog
		);

		await page.goto( product.permalink );
		await page.getByRole( 'tab', { name: 'Specifications' } ).click();

		await expect(
			page.getByRole( 'columnheader', { name: 'Performance' } )
		).toBeVisible();
		await expect(
			specificationValue( page, specification.name )
		).toHaveText( PROCESSOR );
	} );

	test( 'the shortcode renders the same table on any page', async ( {
		page,
		catalog,
	} ) => {
		const { specification, product } = await productWithSavedValue(
			page,
			catalog
		);
		const link = await catalog.page(
			`[specs-table post_id="${ product.id }"]`
		);

		await page.goto( link );

		await expect(
			specificationValue( page, specification.name )
		).toHaveText( PROCESSOR );
	} );

	test( 'a value outside its bounds stops the product save', async ( {
		page,
		catalog,
		requestUtils,
	} ) => {
		const specification = await catalog.specification(
			'Memory slots',
			'unit',
			{ max: 4 }
		);
		const set = await catalog.specificationSet( 'Desktops', [
			{ label: 'Memory', items: [ specification ] },
		] );
		const product = await catalog.product( 'Desktop' );

		const panel = await submitProductValue(
			page,
			product.id,
			set.title,
			specification,
			'8'
		);

		await expect(
			panel.getByText( 'Enter a number of at most 4.' )
		).toBeVisible();

		const stored = await requestUtils.rest< {
			meta_data: Array< { key: string } >;
		} >( { path: `/wc/v3/products/${ product.id }` } );

		expect( stored.meta_data.map( ( meta ) => meta.key ) ).not.toContain(
			'product_specifications_set'
		);
	} );
} );
