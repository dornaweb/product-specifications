import type { ReactNode } from 'react';
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { chevronLeft } from '@wordpress/icons';
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
const ProductMark = () => (
	<svg
		className="ps-product-label__mark"
		viewBox="0 0 128 128"
		aria-hidden="true"
		focusable="false"
	>
		<rect x="4" y="4" width="120" height="120" rx="30" fill="#6D43C0" />
		<path
			d="M39 18h39l19 19v65a8 8 0 0 1-8 8H39a8 8 0 0 1-8-8V26a8 8 0 0 1 8-8Z"
			fill="#FFFFFF"
		/>
		<path d="M78 18v11a8 8 0 0 0 8 8h11L78 18Z" fill="#BDF4E6" />
		<path d="m52 40 12-6 12 6-12 6-12-6Z" fill="#BDF4E6" />
		<path d="m52 40 12 6v14l-12-6V40Z" fill="#42C7A5" />
		<path d="m76 40-12 6v14l12-6V40Z" fill="#6D43C0" />
		<g fill="#42C7A5">
			<rect x="42" y="70" width="9" height="7" rx="3.5" />
			<rect x="42" y="84" width="9" height="7" rx="3.5" />
			<rect x="42" y="98" width="9" height="7" rx="3.5" />
		</g>
		<g fill="#6D43C0">
			<rect x="57" y="71" width="29" height="5" rx="2.5" />
			<rect x="57" y="85" width="29" height="5" rx="2.5" />
			<rect x="57" y="99" width="29" height="5" rx="2.5" />
		</g>
	</svg>
);

const ProductLabel = () => (
	<div className="ps-product-label">
		<ProductMark />
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
