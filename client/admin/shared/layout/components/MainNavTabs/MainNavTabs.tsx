import { __ } from '@wordpress/i18n';
import './styles.scss';
import { getNewPath, navigateTo, useQuery } from '@woocommerce/navigation';
import clsx from 'clsx';

// This component render a curated list of views as tabs in the header

interface Tab {
	name: string;
	title: string;
	path: string;
}

const TABS: Tab[] = [
	{
		name: 'specification-sets',
		title: __( 'Specification Sets', 'product-specifications' ),
		path: '/dwps-product-specifications',
	},
	{
		name: 'specification-attributes',
		title: __( 'Specification Attributes', 'product-specifications' ),
		path: '/dwps-product-specifications-aa',
	},
];

const MainNavTabs = () => {
	const query = useQuery();

	return (
		<nav className="product-specifications-layout__tabs">
			{ TABS.map( ( tab ) => (
				<a
					key={ tab.name }
					href={ getNewPath( {}, tab.path ) }
					className={ clsx( 'product-specifications-layout__tab', {
						'product-specifications-layout__tab--active':
							query.path === tab.path,
					} ) }
					onClick={ ( event ) => {
						event.preventDefault();
						navigateTo( { url: getNewPath( {}, tab.path ) } );
					} }
				>
					{ tab.title }
				</a>
			) ) }
		</nav>
	);
};

export default MainNavTabs;
