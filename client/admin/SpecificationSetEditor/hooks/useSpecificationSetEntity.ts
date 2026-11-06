import { store as coreStore } from '@wordpress/core-data';
import { useDispatch, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { SPECIFICATION_SET_POST_TYPE } from '../../constants';
import type {
	ResolutionActions,
	ResolutionSelectors,
} from '../../shared/storeResolution';
import type {
	SpecificationSetGeneralState,
	SpecificationSetStructure,
} from '../state';
import {
	createSpecificationSetPostRecord,
	type SpecificationSetEntityRecord,
	type SpecificationSetPostRecord,
} from '../mapping/specificationSetRecord';

const EDIT_CONTEXT_QUERY = { context: 'edit' };

interface CoreDataSelectors extends ResolutionSelectors {
	getEntityRecord: (
		kind: string,
		name: string,
		key?: number,
		query?: typeof EDIT_CONTEXT_QUERY
	) => SpecificationSetEntityRecord | null | undefined;
}

interface CoreDataActions extends ResolutionActions {
	saveEntityRecord: (
		kind: string,
		name: string,
		record: SpecificationSetPostRecord,
		options?: { throwOnError?: boolean }
	) => Promise< SpecificationSetEntityRecord >;
}

interface SaveSpecificationSetArgs {
	postId: number | null;
	general: SpecificationSetGeneralState;
	structure: SpecificationSetStructure;
}

export interface SpecificationSetEntityData {
	record: SpecificationSetEntityRecord | null | undefined;
	isResolving: boolean;
	hasResolved: boolean;
	loadError: unknown;
	saveSpecificationSet: (
		args: SaveSpecificationSetArgs
	) => Promise< SpecificationSetEntityRecord >;
}

export function useSpecificationSetEntity(
	postId: number | null = null
): SpecificationSetEntityData {
	const { record, isResolving, hasResolved, loadError } = useSelect(
		( select ) => {
			if ( ! postId ) {
				return {
					record: undefined,
					isResolving: false,
					hasResolved: true,
					loadError: null,
				};
			}

			const selectors = select(
				coreStore
			) as unknown as CoreDataSelectors;
			const resolutionArgs = [
				'postType',
				SPECIFICATION_SET_POST_TYPE,
				postId,
				EDIT_CONTEXT_QUERY,
			];

			return {
				record: selectors.getEntityRecord(
					'postType',
					SPECIFICATION_SET_POST_TYPE,
					postId,
					EDIT_CONTEXT_QUERY
				),
				isResolving: selectors.isResolving(
					'getEntityRecord',
					resolutionArgs
				),
				hasResolved: selectors.hasFinishedResolution(
					'getEntityRecord',
					resolutionArgs
				),
				loadError: selectors.hasResolutionFailed(
					'getEntityRecord',
					resolutionArgs
				)
					? selectors.getResolutionError(
							'getEntityRecord',
							resolutionArgs
					  )
					: null,
			};
		},
		[ postId ]
	);
	const { saveEntityRecord, invalidateResolutionForStoreSelector } =
		useDispatch( coreStore ) as unknown as CoreDataActions;

	const saveSpecificationSet = async ( {
		postId: savedPostId,
		general,
		structure,
	}: SaveSpecificationSetArgs ): Promise< SpecificationSetEntityRecord > => {
		const savedRecord = await saveEntityRecord(
			'postType',
			SPECIFICATION_SET_POST_TYPE,
			createSpecificationSetPostRecord( savedPostId, general, structure ),
			{ throwOnError: true }
		);

		if ( ! savedRecord.id ) {
			throw new Error(
				__(
					'WordPress did not return a saved specification set id.',
					'product-specifications'
				)
			);
		}

		await invalidateResolutionForStoreSelector( 'getEntityRecords' );

		return savedRecord;
	};

	return {
		record,
		isResolving,
		hasResolved,
		loadError,
		saveSpecificationSet,
	};
}
