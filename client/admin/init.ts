import { lazy } from 'react';
import { __ } from '@wordpress/i18n';
import { registerAdminPages } from './shared/platform';
import {
	ADMIN_ROOT,
	EDIT_SPECIFICATION_SET_PATH,
	IMPORT_EXPORT_PATH,
	MIGRATION_PATH,
	NEW_SPECIFICATION_SET_PATH,
	SETTINGS_PATH,
	SPECIFICATIONS_PATH,
	SPECIFICATION_SETS_PATH,
} from './shared/paths';

import './shared/ui/styles.scss';

const MIN_CAPABILITY =
	window.productSpecificationsAdmin?.capability ?? 'manage_woocommerce';

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
		path: ADMIN_ROOT,
		breadcrumbs: [
			__( 'Product Specifications', 'product-specifications' ),
		],
		capability: MIN_CAPABILITY,
	},
	{
		container: SpecificationSetsPage,
		path: SPECIFICATION_SETS_PATH,
		breadcrumbs: [ __( 'Specification Sets', 'product-specifications' ) ],
		capability: MIN_CAPABILITY,
	},
	{
		container: SpecificationSetEditorPage,
		path: NEW_SPECIFICATION_SET_PATH,
		breadcrumbs: [
			__( 'Product Specifications', 'product-specifications' ),
		],
		capability: MIN_CAPABILITY,
	},
	{
		container: SpecificationSetEditorPage,
		path: EDIT_SPECIFICATION_SET_PATH,
		breadcrumbs: [
			__( 'Product Specifications', 'product-specifications' ),
		],
		capability: MIN_CAPABILITY,
	},
	{
		container: SpecificationsPage,
		path: SPECIFICATIONS_PATH,
		breadcrumbs: [ __( 'Specifications', 'product-specifications' ) ],
		capability: MIN_CAPABILITY,
	},
	{
		container: ImportExportPage,
		path: IMPORT_EXPORT_PATH,
		breadcrumbs: [ __( 'Import / Export', 'product-specifications' ) ],
		capability: MIN_CAPABILITY,
	},
	{
		container: SettingsPage,
		path: SETTINGS_PATH,
		breadcrumbs: [ __( 'Settings', 'product-specifications' ) ],
		capability:
			window.productSpecificationsAdmin?.settingsCapability ??
			MIN_CAPABILITY,
	},
	// Only while there is a migration to run, so the tab disappears with it.
	...( window.productSpecificationsAdmin?.migrationRequired
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
