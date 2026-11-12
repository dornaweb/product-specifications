import { visibleTabs, type AreaTab } from './AreaTabs';

const tabs: AreaTab[] = [
	{ path: '/sets', title: 'Specification sets' },
	{ path: '/specifications', title: 'Specifications' },
	{ path: '/settings', title: 'Settings', capability: 'manage_options' },
];

const registered = ( paths: string[] ) => ( path: string ) =>
	paths.includes( path );

describe( 'visibleTabs', () => {
	it( 'drops a tab whose route is not registered', () => {
		const shown = visibleTabs(
			tabs,
			[ 'manage_options' ],
			registered( [ '/sets', '/settings' ] )
		);

		expect( shown.map( ( tab ) => tab.path ) ).toEqual( [
			'/sets',
			'/settings',
		] );
	} );

	it( 'drops a tab whose capability the user does not hold', () => {
		const shown = visibleTabs(
			tabs,
			[],
			registered( [ '/sets', '/specifications', '/settings' ] )
		);

		expect( shown.map( ( tab ) => tab.path ) ).toEqual( [
			'/sets',
			'/specifications',
		] );
	} );

	it( 'keeps a tab that names no capability', () => {
		const shown = visibleTabs( tabs, [], registered( [ '/sets' ] ) );

		expect( shown ).toHaveLength( 1 );
	} );

	it( 'returns nothing when no route is registered', () => {
		expect(
			visibleTabs( tabs, [ 'manage_options' ], () => false )
		).toEqual( [] );
	} );
} );
