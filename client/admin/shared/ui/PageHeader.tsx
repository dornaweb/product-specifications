import type { ReactNode } from 'react';
import { __ } from '@wordpress/i18n';
import { Button, Icon } from '@wordpress/components';
import { chevronLeft, blockTable } from '@wordpress/icons';
import { adminPath, goToAdminPath } from '../platform';

export interface Breadcrumb {
	label: string;
	path: string;
	onNavigate?: () => void;
}

interface Props {
	title: string;
	description?: string;
	breadcrumb?: Breadcrumb;
	actions?: ReactNode;
	status?: ReactNode;
	tabs?: ReactNode;
}

// The band that names the plugin, so a screen reads as ours inside wp-admin
// without the page title having to carry the plugin's name as well.
const ProductLabel = () => (
	<div className="ps-product-label">
		<span className="ps-product-label__mark">
			<Icon icon={ blockTable } size={ 18 } />
		</span>
		{ __( 'Product Specifications', 'product-specifications' ) }
	</div>
);

// The screen states its title once, here. Nothing below repeats it.
const PageHeader = ( {
	title,
	description,
	breadcrumb,
	actions,
	status,
	tabs,
}: Props ) => (
	<header className="ps-page-header">
		<div className="ps-page-header__main">
			<ProductLabel />
			<div className="ps-page-header__titles">
				{ breadcrumb && (
					<nav className="ps-breadcrumb">
						<Button
							variant="link"
							icon={ chevronLeft }
							href={ adminPath( breadcrumb.path ) }
							onClick={ ( event: React.MouseEvent ) => {
								event.preventDefault();
								if ( breadcrumb.onNavigate ) {
									breadcrumb.onNavigate();
									return;
								}

								goToAdminPath( breadcrumb.path );
							} }
						>
							{ breadcrumb.label }
						</Button>
					</nav>
				) }
				<h1 className="ps-page-header__title">{ title }</h1>
				{ description && (
					<p className="ps-page-header__description">
						{ description }
					</p>
				) }
			</div>
			{ ( status || actions ) && (
				<div className="ps-page-header__actions">
					{ status }
					{ actions }
				</div>
			) }
		</div>
		{ tabs }
	</header>
);

export default PageHeader;
