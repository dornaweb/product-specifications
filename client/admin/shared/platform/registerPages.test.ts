import { addFilter } from '@wordpress/hooks';
import { registerAdminPages, type AdminPage } from './registerPages';

jest.mock( '@wordpress/hooks', () => ( {
	addFilter: jest.fn(),
} ) );

describe( 'registerAdminPages', () => {
	it( 'keeps the Product Specifications submenu active on child routes', () => {
		const page: AdminPage = {
			container: () => null,
			path: '/dwps-product-specifications/import-export',
			breadcrumbs: [ 'Import / Export' ],
			capability: 'manage_woocommerce',
		};

		registerAdminPages( [ page ] );

		const registerPages = ( addFilter as jest.Mock ).mock.calls[ 0 ][ 2 ];

		expect( registerPages( [] ) ).toEqual( [
			{
				...page,
				isDefault: true,
				layout: { header: false },
				navArgs: {
					parentPath: '/dwps-product-specifications',
				},
				wpOpenMenu: 'toplevel_page_woocommerce',
			},
		] );
	} );
} );
