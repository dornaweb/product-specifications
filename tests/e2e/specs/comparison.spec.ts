import { request as playwrightRequest, type Page } from '@playwright/test';
import { test, expect, adminScreen } from '../fixtures';
import type {
	Catalog,
	CreatedProduct,
	CreatedSpecification,
	CreatedSpecificationSet,
} from '../catalog';

interface Laptops {
	processor: CreatedSpecification;
	weight: CreatedSpecification;
	laptops: CreatedSpecificationSet;
	alpha: CreatedProduct;
	beta: CreatedProduct;
	gamma: CreatedProduct;
	comparePage: string;
}

async function laptops( catalog: Catalog ): Promise< Laptops > {
	const processor = await catalog.specification( 'Processor' );
	const weight = await catalog.specification( 'Weight' );
	const laptopSet = await catalog.specificationSet(
		'Laptops',
		[ { label: 'Performance', items: [ processor, weight ] } ],
		{ comparison: true }
	);
	const alpha = await catalog.product( 'Alpha laptop', {
		set: laptopSet,
		values: { [ processor.slug ]: 'Core i7', [ weight.slug ]: '1.4 kg' },
	} );
	const beta = await catalog.product( 'Beta laptop', {
		set: laptopSet,
		values: { [ processor.slug ]: 'Core i5' },
	} );
	const gamma = await catalog.product( 'Gamma laptop', {
		set: laptopSet,
		values: { [ processor.slug ]: 'Ryzen 7' },
	} );
	const page = await catalog.pageRecord(
		'<!-- wp:product-specifications/comparison /-->'
	);

	await catalog.changeSettings( {
		compare_page: page.id,
		compare_button_placement: 'after_add_to_cart',
		compare_max_products: '3',
	} );

	return {
		processor,
		weight,
		laptops: laptopSet,
		alpha,
		beta,
		gamma,
		comparePage: page.link,
	};
}

function compareLink( base: string, ids: number[] ): string {
	return `${ base }${ base.includes( '?' ) ? '&' : '?' }compare=${ ids.join(
		','
	) }`;
}

function columnTitles( page: Page ) {
	return page.locator( '.ps-comparison__title' );
}

function cellsOf( page: Page, name: string ) {
	return page
		.locator( '.ps-comparison__row' )
		.filter( { has: page.getByRole( 'rowheader', { name } ) } )
		.locator( '.ps-comparison__value' );
}

test.describe( 'Comparison', () => {
	test( 'the compare button opens the comparison and follows its placement setting', async ( {
		page,
		catalog,
	} ) => {
		const { alpha, comparePage } = await laptops( catalog );

		await page.goto( alpha.permalink );

		const button = page.locator( '.ps-compare-button' );
		await expect( button ).toHaveCount( 1 );
		await expect( button ).toHaveAttribute( 'rel', 'nofollow' );
		await expect( button ).toHaveAttribute(
			'href',
			compareLink( comparePage, [ alpha.id ] )
		);

		await catalog.changeSettings( {
			compare_button_placement: 'after_meta',
		} );
		await page.reload();
		await expect( page.locator( '.ps-compare-button' ) ).toHaveCount( 1 );

		await catalog.changeSettings( { compare_button_placement: 'none' } );
		await page.reload();
		await expect( page.locator( '.ps-compare-button' ) ).toHaveCount( 0 );
	} );

	test( 'no button for a set without comparison or a hidden product, values or not', async ( {
		page,
		catalog,
	} ) => {
		const { laptops: laptopSet, processor } = await laptops( catalog );
		const empty = await catalog.product( 'Empty laptop', {
			set: laptopSet,
		} );
		const closedSet = await catalog.specificationSet(
			'Closed',
			[ { label: 'General', items: [ processor ] } ],
			{ comparison: false }
		);
		const closed = await catalog.product( 'Closed laptop', {
			set: closedSet,
			values: { [ processor.slug ]: 'Core i3' },
		} );

		await page.goto( empty.permalink );
		await expect( page.locator( '.ps-compare-button' ) ).toHaveCount( 1 );

		await page.goto( closed.permalink );
		await expect( page.locator( '.ps-compare-button' ) ).toHaveCount( 0 );

		const hidden = await catalog.product( 'Hidden laptop', {
			set: laptopSet,
			values: { [ processor.slug ]: 'Core i9' },
			catalogVisibility: 'hidden',
		} );

		await page.goto( hidden.permalink );
		await expect( page.locator( '.ps-compare-button' ) ).toHaveCount( 0 );
	} );

	test( 'the first product decides the set and the rest is dropped or cut at the maximum', async ( {
		page,
		catalog,
	} ) => {
		const {
			alpha,
			beta,
			gamma,
			comparePage,
			processor,
			laptops: laptopSet,
		} = await laptops( catalog );
		const phones = await catalog.specificationSet(
			'Phones',
			[ { label: 'General', items: [ processor ] } ],
			{ comparison: true }
		);
		const phone = await catalog.product( 'Phone', {
			set: phones,
			values: { [ processor.slug ]: 'A18' },
		} );
		const hidden = await catalog.product( 'Hidden laptop', {
			set: laptopSet,
			catalogVisibility: 'hidden',
		} );

		await page.goto(
			`${ compareLink( comparePage, [
				gamma.id,
				phone.id,
				hidden.id,
				alpha.id,
			] ) },abc,0`
		);

		await expect( columnTitles( page ) ).toHaveText( [
			/Gamma laptop/,
			/Alpha laptop/,
		] );

		await page.goto(
			compareLink( comparePage, [
				alpha.id,
				beta.id,
				gamma.id,
				phone.id,
			] )
		);
		await expect( columnTitles( page ) ).toHaveCount( 3 );
		await expect( page.locator( '.ps-comparison__limit' ) ).toHaveText(
			'You can compare up to 3 products.'
		);
	} );

	test( 'a row one product has shows a dash for the others', async ( {
		page,
		catalog,
	} ) => {
		const { alpha, beta, weight, comparePage } = await laptops( catalog );

		await page.goto( compareLink( comparePage, [ alpha.id, beta.id ] ) );

		await expect( cellsOf( page, weight.name ) ).toHaveText( [
			'1.4 kg',
			/—\s*No value/,
		] );
	} );

	test( 'removing products rewrites the link down to the empty state', async ( {
		page,
		catalog,
	} ) => {
		const { alpha, beta, comparePage } = await laptops( catalog );

		await page.goto( compareLink( comparePage, [ alpha.id, beta.id ] ) );
		await page
			.getByRole( 'link', { name: /Remove .*Alpha laptop/ } )
			.click();

		await expect( page ).toHaveURL(
			compareLink( comparePage, [ beta.id ] )
		);
		await expect( columnTitles( page ) ).toHaveText( [ /Beta laptop/ ] );

		await page
			.getByRole( 'link', { name: /Remove .*Beta laptop/ } )
			.click();

		await expect( page.locator( '.ps-comparison__empty' ) ).toContainText(
			'Add products to compare from a product page.'
		);
		await expect(
			page.getByRole( 'link', { name: 'Go to the shop' } )
		).toBeVisible();
	} );

	test( 'add product finds products of the same set unless they are hidden, and adds the picked one', async ( {
		page,
		catalog,
	} ) => {
		const {
			alpha,
			gamma,
			laptops: laptopSet,
			processor,
			comparePage,
		} = await laptops( catalog );
		await catalog.product( 'Hidden laptop', {
			set: laptopSet,
			values: { [ processor.slug ]: 'Core i9' },
			catalogVisibility: 'hidden',
		} );
		await catalog.product( 'Search-only laptop', {
			set: laptopSet,
			values: { [ processor.slug ]: 'Core i3' },
			catalogVisibility: 'search',
		} );

		await page.goto( compareLink( comparePage, [ alpha.id ] ) );
		await page.getByRole( 'button', { name: 'Add product' } ).click();

		const search = page.getByRole( 'combobox', {
			name: 'Add a product to compare',
		} );
		await expect( search ).toBeFocused();
		await search.fill( catalog.unique( 'laptop' ).split( ' ' )[ 1 ] );

		const options = page.getByRole( 'option' );
		await expect( options ).toHaveCount( 3 );
		await expect(
			options.filter( { hasText: 'Beta laptop' } )
		).toHaveCount( 1 );
		await expect(
			options.filter( { hasText: 'Search-only laptop' } )
		).toHaveCount( 1 );

		const gammaPosition = ( await options.allTextContents() ).findIndex(
			( name ) => name.includes( 'Gamma laptop' )
		);
		expect( gammaPosition ).toBeGreaterThanOrEqual( 0 );

		for ( let step = 0; step <= gammaPosition; step++ ) {
			await search.press( 'ArrowDown' );
		}
		await search.press( 'Enter' );

		await expect( page ).toHaveURL(
			compareLink( comparePage, [ alpha.id, gamma.id ] )
		);
	} );

	test( 'a failed search offers a retry', async ( { page, catalog } ) => {
		const { alpha, comparePage } = await laptops( catalog );
		let failNext = true;

		await page.route( /comparison(%2F|\/)products/, ( route ) => {
			if ( failNext ) {
				failNext = false;
				return route.abort();
			}

			return route.continue();
		} );

		await page.goto( compareLink( comparePage, [ alpha.id ] ) );
		await page.getByRole( 'button', { name: 'Add product' } ).click();
		await page
			.getByRole( 'combobox', { name: 'Add a product to compare' } )
			.fill( 'laptop' );

		await expect(
			page.getByText( 'Couldn’t load products.' )
		).toBeVisible();

		await page.getByRole( 'button', { name: 'Retry' } ).click();

		await expect( page.getByRole( 'option' ).first() ).toBeVisible();
	} );

	test( 'the search endpoint answers without login, for a comparable set only', async ( {
		catalog,
		baseURL,
	} ) => {
		const {
			laptops: laptopSet,
			alpha,
			processor,
		} = await laptops( catalog );
		const closedSet = await catalog.specificationSet(
			'Closed',
			[ { label: 'General', items: [ processor ] } ],
			{ comparison: false }
		);
		const anonymous = await playwrightRequest.newContext( { baseURL } );
		const search = async ( params: Record< string, string > ) =>
			(
				await anonymous.get(
					'/wp-json/product-specifications/v2/comparison/products',
					{ params }
				)
			).json();

		const found = ( await search( {
			set: laptopSet.slug,
			search: 'laptop',
			exclude: String( alpha.id ),
		} ) ) as Array< { id: number; name: string } >;

		expect( found.map( ( result ) => result.name ) ).toEqual(
			expect.arrayContaining( [
				expect.stringMatching( /Beta laptop/ ),
				expect.stringMatching( /Gamma laptop/ ),
			] )
		);
		expect( found.map( ( result ) => result.id ) ).not.toContain(
			alpha.id
		);
		expect(
			await search( { set: closedSet.slug, search: 'laptop' } )
		).toEqual( [] );
		expect(
			await search( { set: 'no-such-set', search: 'laptop' } )
		).toEqual( [] );

		await anonymous.dispose();
	} );

	test( 'the compare page asks search engines not to index it', async ( {
		page,
		catalog,
	} ) => {
		const { comparePage } = await laptops( catalog );

		await page.goto( comparePage );

		await expect( page.locator( 'meta[name="robots"]' ) ).toHaveAttribute(
			'content',
			/noindex/
		);
	} );

	test( 'the shortcodes render the comparison and the button', async ( {
		page,
		catalog,
	} ) => {
		const { alpha, beta } = await laptops( catalog );
		const classic = await catalog.page(
			`[specs-compare-button id="${ alpha.id }"][specs-comparison]`
		);

		await page.goto( compareLink( classic, [ alpha.id, beta.id ] ) );

		await expect( page.locator( '.ps-compare-button' ) ).toHaveCount( 1 );
		await expect( columnTitles( page ) ).toHaveText( [
			/Alpha laptop/,
			/Beta laptop/,
		] );
	} );

	test( 'three products read side by side on a phone', async ( {
		page,
		catalog,
	} ) => {
		const { alpha, beta, gamma, comparePage } = await laptops( catalog );

		await catalog.changeSettings( { compare_max_products: '4' } );
		await page.setViewportSize( { width: 375, height: 812 } );
		await page.goto(
			compareLink( comparePage, [ alpha.id, beta.id, gamma.id ] )
		);

		await expect(
			page.getByRole( 'button', { name: 'Add product' } )
		).toBeInViewport();

		const tops = await columnTitles( page ).evaluateAll( ( titles ) =>
			titles.map( ( title ) =>
				Math.round( title.getBoundingClientRect().top )
			)
		);
		expect( new Set( tops ).size ).toBe( 1 );

		const scrollsSideways = await page.evaluate(
			() =>
				document.documentElement.scrollWidth >
				document.documentElement.clientWidth
		);
		expect( scrollsSideways ).toBe( false );
	} );

	test( 'the set editor offers comparison for a new set and saves it off', async ( {
		page,
		catalog,
	} ) => {
		const title = catalog.unique( 'Cameras' );

		await page.goto( adminScreen( '/specification-set/new' ) );
		await page.getByLabel( 'Title' ).fill( title );
		await page.getByRole( 'tab', { name: 'Comparison' } ).click();

		const toggle = page.getByRole( 'checkbox', {
			name: 'Enable comparison',
		} );
		await expect( toggle ).toBeChecked();

		await toggle.uncheck();
		await page.getByRole( 'button', { name: 'Save set' } ).click();
		await expect( page ).toHaveURL( /specification-set%2Fedit/ );

		const id = Number( new URL( page.url() ).searchParams.get( 'id' ) );
		catalog.adopt( `/wp/v2/specification-sets/${ id }` );

		await page.reload();
		await page.getByRole( 'tab', { name: 'Comparison' } ).click();
		await expect(
			page.getByRole( 'checkbox', { name: 'Enable comparison' } )
		).not.toBeChecked();
	} );
} );
