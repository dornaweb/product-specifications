import { lazy } from 'react';
import { __ } from '@wordpress/i18n';
import { registerAdminPages } from './shared/platform';

const MIN_CAPABILITY = window.dwspecsAdmin?.capability ?? 'manage_woocommerce';

// Page containers registered with the admin shell must be lazy-loaded.
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

registerAdminPages( [
	{
		container: MainPage,
		path: '/dwps-product-specifications',
		breadcrumbs: [
			__( 'Product Specifications', 'product-specifications' ),
		],
		capability: MIN_CAPABILITY,
	},
	{
		container: SpecificationSetsPage,
		path: '/dwps-product-specifications/specification-sets',
		breadcrumbs: [ __( 'Specification Sets', 'product-specifications' ) ],
		capability: MIN_CAPABILITY,
	},
	{
		container: SpecificationSetEditorPage,
		path: '/dwps-product-specifications/specification-set/new',
		breadcrumbs: [
			__( 'Product Specifications', 'product-specifications' ),
		],
		capability: MIN_CAPABILITY,
	},
	{
		container: SpecificationSetEditorPage,
		path: '/dwps-product-specifications/specification-set/edit',
		breadcrumbs: [
			__( 'Product Specifications', 'product-specifications' ),
		],
		capability: MIN_CAPABILITY,
	},
] );
