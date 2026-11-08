import { lazy } from 'react';
import { __ } from '@wordpress/i18n';
import { MIGRATION_PATH } from './shared/areas';
import { registerAdminPages } from './shared/platform';

import './shared/ui/styles.scss';

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

const SpecificationsPage = lazy(
	() => import( /* webpackChunkName: "specifications" */ './Specifications' )
);

const SpecificationSetEditorPage = lazy(
	() =>
		import(
			/* webpackChunkName: "specification-set-editor" */ './SpecificationSetEditor'
		)
);

const SettingsPage = lazy(
	() => import( /* webpackChunkName: "settings" */ './Settings' )
);

const MigrationPage = lazy(
	() => import( /* webpackChunkName: "migration" */ './Migration' )
);

const ImportExportPage = lazy(
	() => import( /* webpackChunkName: "import-export" */ './ImportExport' )
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
	{
		container: SpecificationsPage,
		path: '/dwps-product-specifications/specifications',
		breadcrumbs: [ __( 'Specifications', 'product-specifications' ) ],
		capability: MIN_CAPABILITY,
	},
	{
		container: ImportExportPage,
		path: '/dwps-product-specifications/import-export',
		breadcrumbs: [ __( 'Import / Export', 'product-specifications' ) ],
		capability: MIN_CAPABILITY,
	},
	{
		container: SettingsPage,
		path: '/dwps-product-specifications/settings',
		breadcrumbs: [ __( 'Settings', 'product-specifications' ) ],
		capability: window.dwspecsAdmin?.settingsCapability ?? MIN_CAPABILITY,
	},
	// Only while there is a migration to run, so the tab disappears with it.
	...( window.dwspecsAdmin?.migrationRequired
		? [
				{
					container: MigrationPage,
					path: MIGRATION_PATH,
					breadcrumbs: [
						__( 'Migration', 'product-specifications' ),
					],
					capability: MIN_CAPABILITY,
				},
		  ]
		: [] ),
] );
