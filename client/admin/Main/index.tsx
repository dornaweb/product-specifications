import { __ } from '@wordpress/i18n';
import Layout from '../shared/layout';
import { Placeholder } from '@wordpress/components';
import { useEffect } from 'react';
import { goToAdminPath } from '../shared/platform';

const MainPage = () => {
	useEffect( () => {
		goToAdminPath( '/dwps-product-specifications/specification-sets' );
	}, [] );

	return (
		<Layout title="Product Specifications">
			<Placeholder>
				{ __(
					'Redirecting to Specification Sets',
					'product-specifications'
				) }
			</Placeholder>
		</Layout>
	);
};

export default MainPage;
