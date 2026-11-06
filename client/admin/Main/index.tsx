import { __ } from '@wordpress/i18n';
import { Placeholder, Spinner } from '@wordpress/components';
import { useEffect } from 'react';
import { goToAdminPath } from '../shared/platform';

const MainPage = () => {
	useEffect( () => {
		goToAdminPath( '/dwps-product-specifications/specification-sets' );
	}, [] );

	return (
		<Placeholder>
			<Spinner />
			{ __(
				'Redirecting to specification sets…',
				'product-specifications'
			) }
		</Placeholder>
	);
};

export default MainPage;
