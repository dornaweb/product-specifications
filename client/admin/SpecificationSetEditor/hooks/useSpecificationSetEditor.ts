import { useEffect, useRef } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { currentPath, useAdminQuery } from '../../shared/platform';
import {
	store as editorStore,
	type SpecificationSetEditorNotice,
	type SpecificationSetGeneralState,
	type SpecificationSetStatus,
} from '../state';
import {
	createEmptySpecificationSetGeneralState,
	createSpecificationSetGeneralStateFromPostRecord,
} from '../mapping/specificationSetRecord';
import { useSpecificationSetEntity } from './useSpecificationSetEntity';
import { useUnsavedChangesWarning } from './useUnsavedChangesWarning';

const EDIT_PATH = '/dwps-product-specifications/specification-set/edit';

export type SpecificationSetEditorMode = 'create' | 'edit';

interface StoreSelectors {
	getPostId: () => number | null;
	getGeneral: () => SpecificationSetGeneralState;
	hasUnsavedChanges: () => boolean;
	isSaving: () => boolean;
	getNotice: () => SpecificationSetEditorNotice | null;
}

interface StoreActions {
	initializeEditor: (
		postId: number | null,
		general: SpecificationSetGeneralState
	) => void;
	updateGeneralField: (
		field: keyof SpecificationSetGeneralState,
		value: SpecificationSetGeneralState[ keyof SpecificationSetGeneralState ]
	) => void;
	startSaving: () => void;
	finishSave: (
		postId: number,
		general: SpecificationSetGeneralState,
		notice: SpecificationSetEditorNotice
	) => void;
	failSave: ( notice: SpecificationSetEditorNotice ) => void;
	dismissNotice: () => void;
}

export interface SpecificationSetEditorData {
	mode: SpecificationSetEditorMode;
	postId: number | null;
	general: SpecificationSetGeneralState;
	hasUnsavedChanges: boolean;
	isSaving: boolean;
	isLoading: boolean;
	loadErrorMessage: string | null;
	notice: SpecificationSetEditorNotice | null;
	updateTextField: (
		field: keyof Omit< SpecificationSetGeneralState, 'status' >
	) => ( value: string | undefined ) => void;
	updateStatus: ( status: SpecificationSetStatus | undefined ) => void;
	save: () => Promise< void >;
	dismissNotice: () => void;
}

function getSaveErrorMessage( error: unknown ): string {
	if (
		error &&
		typeof error === 'object' &&
		'message' in error &&
		typeof error.message === 'string'
	) {
		return error.message;
	}

	return __(
		'Specification set could not be saved.',
		'product-specifications'
	);
}

function parsePostId( value: unknown ): number | null {
	if ( Array.isArray( value ) ) {
		return parsePostId( value[ 0 ] );
	}

	const postId = Number( value );

	return Number.isInteger( postId ) && postId > 0 ? postId : null;
}

function getLoadErrorMessage( error: unknown ): string {
	if (
		error &&
		typeof error === 'object' &&
		'message' in error &&
		typeof error.message === 'string'
	) {
		return error.message;
	}

	return __(
		'Specification set could not be loaded.',
		'product-specifications'
	);
}

export function useSpecificationSetEditor(): SpecificationSetEditorData {
	const query = useAdminQuery();
	const mode: SpecificationSetEditorMode =
		currentPath() === EDIT_PATH ? 'edit' : 'create';
	const requestedPostId = mode === 'edit' ? parsePostId( query.id ) : null;
	const { postId, general, hasUnsavedChanges, isSaving, notice } = useSelect(
		( select ) => {
			const selectors = select( editorStore ) as StoreSelectors;

			return {
				postId: selectors.getPostId(),
				general: selectors.getGeneral(),
				hasUnsavedChanges: selectors.hasUnsavedChanges(),
				isSaving: selectors.isSaving(),
				notice: selectors.getNotice(),
			};
		},
		[]
	);
	const {
		initializeEditor,
		updateGeneralField,
		startSaving,
		finishSave,
		failSave,
		dismissNotice,
	} = useDispatch( editorStore ) as StoreActions;
	const {
		record,
		isResolving,
		hasResolved,
		loadError,
		saveSpecificationSet,
	} = useSpecificationSetEntity( requestedPostId );
	const initializedRouteKey = useRef< string | null >( null );

	useUnsavedChangesWarning( hasUnsavedChanges );

	useEffect( () => {
		if ( mode !== 'create' ) {
			return;
		}

		const routeKey = 'create';

		if ( initializedRouteKey.current === routeKey ) {
			return;
		}

		initializedRouteKey.current = routeKey;
		initializeEditor( null, createEmptySpecificationSetGeneralState() );
	}, [ initializeEditor, mode ] );

	useEffect( () => {
		if ( mode !== 'edit' || ! requestedPostId || ! record ) {
			return;
		}

		const routeKey = `edit:${ requestedPostId }`;

		if (
			initializedRouteKey.current === routeKey ||
			postId === requestedPostId
		) {
			initializedRouteKey.current = routeKey;
			return;
		}

		initializedRouteKey.current = routeKey;
		initializeEditor(
			requestedPostId,
			createSpecificationSetGeneralStateFromPostRecord( record )
		);
	}, [ initializeEditor, mode, postId, record, requestedPostId ] );

	const updateTextField =
		( field: keyof Omit< SpecificationSetGeneralState, 'status' > ) =>
		( value: string | undefined ) => {
			updateGeneralField( field, value ?? '' );
		};

	const updateStatus = ( status: SpecificationSetStatus | undefined ) => {
		updateGeneralField( 'status', status ?? 'draft' );
	};

	const save = async () => {
		if ( ! hasUnsavedChanges || isSaving ) {
			return;
		}

		startSaving();

		try {
			const savedRecord = await saveSpecificationSet( {
				postId,
				general,
			} );

			finishSave(
				savedRecord.id,
				createSpecificationSetGeneralStateFromPostRecord( savedRecord ),
				{
					status: 'success',
					message: __(
						'Specification set saved.',
						'product-specifications'
					),
				}
			);
		} catch ( error ) {
			failSave( {
				status: 'error',
				message: getSaveErrorMessage( error ),
			} );
		}
	};

	return {
		mode,
		postId,
		general,
		hasUnsavedChanges,
		isSaving,
		isLoading:
			mode === 'edit' &&
			!! requestedPostId &&
			( isResolving || ! hasResolved ),
		loadErrorMessage:
			mode === 'edit' && ! requestedPostId
				? __(
						'Invalid specification set id.',
						'product-specifications'
				  )
				: loadError
				? getLoadErrorMessage( loadError )
				: mode === 'edit' &&
				  requestedPostId &&
				  hasResolved &&
				  ! isResolving &&
				  ! record
				? __(
						'Specification set was not found.',
						'product-specifications'
				  )
				: null,
		notice,
		updateTextField,
		updateStatus,
		save,
		dismissNotice,
	};
}
