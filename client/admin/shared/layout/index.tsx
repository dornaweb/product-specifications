import { type ReactNode } from 'react';
import Header from './components/Header/Header';
import { __ } from '@wordpress/i18n';

import './styles.scss';

type Props = {
	children: ReactNode;
	title?: string;
};

const Layout = ( { children, title }: Props ) => {
	return (
		<div className="product-specifications-layout">
			<Header
				title={
					title ??
					__( 'Product Specifications', 'product-specifications' )
				}
			/>

			<div className="product-specifications-layout__content">
				{ children }
			</div>
		</div>
	);
};

export default Layout;
