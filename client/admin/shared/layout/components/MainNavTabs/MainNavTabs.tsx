import { __ } from '@wordpress/i18n';
import './styles.scss';
import { adminPath, goToAdminPath, useAdminQuery } from '../../../platform';
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
		name: 'specifications',
		title: __( 'Specifications', 'product-specifications' ),
		path: '/dwps-product-specifications-aa',
	},
];

const MainNavTabs = () => {
	const query = useAdminQuery();

	return (
		<nav className="product-specifications-layout__tabs">
			{ TABS.map( ( tab ) => (
				<a
					key={ tab.name }
					href={ adminPath( tab.path ) }
					className={ clsx( 'product-specifications-layout__tab', {
						'product-specifications-layout__tab--active':
							query.path === tab.path,
					} ) }
					onClick={ ( event ) => {
						event.preventDefault();
						goToAdminPath( tab.path );
					} }
				>
					{ tab.title }
				</a>
			) ) }
		</nav>
	);
};

export default MainNavTabs;
