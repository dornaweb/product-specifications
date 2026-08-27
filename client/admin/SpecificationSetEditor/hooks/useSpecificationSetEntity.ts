import { store as coreStore } from '@wordpress/core-data';
import { useDispatch, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { SPECIFICATION_SET_POST_TYPE } from '../../constants';
import type { SpecificationSetGeneralState } from '../state';
import {
	createSpecificationSetPostRecord,
	type SpecificationSetEntityRecord,
	type SpecificationSetPostRecord,
} from '../mapping/specificationSetRecord';

const EDIT_CONTEXT_QUERY = { context: 'edit' };

interface CoreDataSelectors {
	getEntityRecord: (
		kind: string,
		name: string,
		key?: number,
		query?: typeof EDIT_CONTEXT_QUERY
	) => SpecificationSetEntityRecord | null | undefined;
	isResolving: ( selectorName: string, args: unknown[] ) => boolean;
	hasFinishedResolution: (
		selectorName: string,
		args: unknown[]
	) => boolean;
	hasResolutionFailed: ( selectorName: string, args: unknown[] ) => boolean;
	getResolutionError: (
		selectorName: string,
		args: unknown[]
	) => unknown;
}

interface CoreDataActions {
	saveEntityRecord: (
		kind: string,
		name: string,
		record: SpecificationSetPostRecord,
		options?: { throwOnError?: boolean }
	) => Promise< SpecificationSetEntityRecord >;
	invalidateResolutionForStoreSelector: (
		selectorName: string
	) => Promise< void >;
}

interface SaveSpecificationSetArgs {
	postId: number | null;
	general: SpecificationSetGeneralState;
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

			const selectors = select( coreStore ) as CoreDataSelectors;
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
		useDispatch( coreStore ) as CoreDataActions;

	const saveSpecificationSet = async ( {
		postId,
		general,
	}: SaveSpecificationSetArgs ): Promise< SpecificationSetEntityRecord > => {
		const savedRecord = await saveEntityRecord(
			'postType',
			SPECIFICATION_SET_POST_TYPE,
			createSpecificationSetPostRecord( postId, general ),
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
