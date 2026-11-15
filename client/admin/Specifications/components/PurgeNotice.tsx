import { Notice } from '@wordpress/components';
import { __, _n, sprintf } from '@wordpress/i18n';
import type { PurgeEntry } from '../hooks/usePurgeStatus';

const PurgeNotice = ( { pending }: { pending: PurgeEntry[] } ) => {
	const running = pending.filter( ( entry ) => entry.failure === null );
	const failed = pending.filter( ( entry ) => entry.failure !== null );

	return (
		<>
			{ running.length > 0 && (
				<Notice status="info" isDismissible={ false }>
					{ sprintf(
						/* translators: %d: number of deleted specifications whose values are still being removed. */
						_n(
							'Removing the values of %d deleted specification from products…',
							'Removing the values of %d deleted specifications from products…',
							running.length,
							'product-specifications'
						),
						running.length
					) }
				</Notice>
			) }
			{ failed.map( ( entry ) => (
				<Notice
					key={ entry.slug }
					status="warning"
					isDismissible={ false }
				>
					{ sprintf(
						/* translators: 1: a specification key, 2: why its values could not be removed. */
						__(
							'The values of “%1$s” could not be removed from every product: %2$s Run “wp product-specifications purge run” to retry.',
							'product-specifications'
						),
						entry.slug,
						entry.failure ?? ''
					) }
				</Notice>
			) ) }
		</>
	);
};

export default PurgeNotice;
