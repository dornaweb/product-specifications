import { __ } from '@wordpress/i18n';
import { Placeholder, Spinner } from '@wordpress/components';
import { useEffect } from 'react';
import { goToAdminPath } from '../shared/platform';
import { SPECIFICATION_SETS_PATH } from '../shared/paths';

const MainPage = () => {
	useEffect( () => {
		goToAdminPath( SPECIFICATION_SETS_PATH );
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
