import { useState } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { SPECIFICATION_TAXONOMY } from '../../constants';
import { mapRecord } from '../mapRecord';
import type { SpecificationItem, SpecificationRecord } from '../mapRecord';
import { messageOf, slugConflictFrom, type SlugConflict } from '../key';

interface Actions {
	saveEntityRecord: (
		kind: string,
		name: string,
		record: Record< string, unknown >,
		options?: { throwOnError?: boolean }
	) => Promise< SpecificationRecord >;
}

// Creating one specification, without the list around it. The library screen
// owns a collection; a screen that only needs the new record does not.
export function useCreateSpecification() {
	const [ isSaving, setIsSaving ] = useState( false );
	const [ error, setError ] = useState< string >();
	const [ slugConflict, setSlugConflict ] = useState< SlugConflict | null >(
		null
	);
	const { saveEntityRecord } = useDispatch( coreStore ) as unknown as Actions;

	return {
		isSaving,
		error,
		slugConflict,
		clearSlugConflict: () => setSlugConflict( null ),
		create: async (
			record: Record< string, unknown >
		): Promise< SpecificationItem | null > => {
			setIsSaving( true );
			setError( undefined );
			setSlugConflict( null );

			try {
				const saved = await saveEntityRecord(
					'taxonomy',
					SPECIFICATION_TAXONOMY,
					record,
					{ throwOnError: true }
				);

				return mapRecord( saved );
			} catch ( failure ) {
				const conflict = slugConflictFrom( failure );

				if ( conflict ) {
					setSlugConflict( conflict );
				} else {
					setError(
						messageOf(
							failure,
							__(
								'Specification could not be created.',
								'product-specifications'
							)
						)
					);
				}

				return null;
			} finally {
				setIsSaving( false );
			}
		},
	};
}
