import { __, sprintf } from '@wordpress/i18n';
import type { SpecificationItem } from '../mapRecord';

export function deletionResultMessage(
	deleted: SpecificationItem[],
	failed: SpecificationItem[]
): string {
	if ( deleted.length === 0 ) {
		return sprintf(
			/* translators: %s: specifications that could not be deleted. */
			__(
				'Nothing was deleted. Not deleted: %s.',
				'product-specifications'
			),
			failed.map( ( item ) => item.name ).join( ', ' )
		);
	}

	return sprintf(
		/* translators: 1: deleted specifications. 2: specifications that could not be deleted. */
		__( 'Deleted: %1$s. Not deleted: %2$s.', 'product-specifications' ),
		deleted.map( ( item ) => item.name ).join( ', ' ),
		failed.map( ( item ) => item.name ).join( ', ' )
	);
}
