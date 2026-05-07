// TODO: edit file name
import { lazy } from 'react';
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

const MIN_CAPABILITY = 'shop_manager';

// Page containers registered in woocommerce_admin_pages_list must be lazy-loaded.
// Do not statically import page containers.
// Static imports break code splitting and does not work with imported CSS files

const MainPage = lazy(
	() => import( /* webpackChunkName: "main" */ './Main' )
);

const SpecificationSetsPage = lazy(
	() =>
		import(
			/* webpackChunkName: "specification-sets" */ './SpecificationSets'
		)
);

const SpecificationSetEditorPage = lazy(
	() =>
		import(
			/* webpackChunkName: "specification-set-editor" */ './SpecificationSetEditor'
		)
);

addFilter(
	'woocommerce_admin_pages_list',
	'dwps-product-specifications',
	( pages ) => {
		pages.push( {
			container: MainPage,
			path: '/dwps-product-specifications',
			breadcrumbs: [
				__( 'Product Specifications', 'product-specifications' ),
			],
			isDefault: true,
			capability: MIN_CAPABILITY,
			layout: {
				header: false,
			},
			wpOpenMenu: 'toplevel_page_woocommerce',
		} );

		pages.push( {
			container: SpecificationSetsPage,
			path: '/dwps-product-specifications/specification-sets',
			breadcrumbs: [
				__( 'Specification Sets', 'product-specifications' ),
			],
			isDefault: true,
			capability: MIN_CAPABILITY,
			layout: {
				header: false,
			},
			wpOpenMenu: 'toplevel_page_woocommerce',
		} );

		pages.push( {
			container: SpecificationSetEditorPage,
			path: '/dwps-product-specifications/specification-set/new',
			breadcrumbs: [
				__( 'Product Specifications', 'product-specifications' ),
			],
			isDefault: true,
			capability: MIN_CAPABILITY,
			layout: {
				header: false,
			},
			wpOpenMenu: 'toplevel_page_woocommerce',
		} );

		return pages;
	}
);
