import { useEffect, useRef } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { isKeyboardEvent } from '@wordpress/keycodes';
import {
	currentPath,
	discardUnsavedChangesAndGoToAdminPath,
	useAdminQuery,
	useUnsavedChangesGuard,
} from '../../shared/platform';
import {
	store as editorStore,
	type GroupProperty,
	type ItemProperty,
	type NoticeState,
	type SpecificationSetStructure,
	type StructureGroup,
	type StructureItem,
	type SpecificationSetGeneralState,
	type SpecificationSetStatus,
} from '../state';
import {
	createEmptySpecificationSetGeneralState,
	createSpecificationSetGeneralStateFromPostRecord,
	createSpecificationSetStructureFromPostRecord,
} from '../mapping/specificationSetRecord';
import {
	emptyStructure,
	groupKeyFrom,
} from '../mapping/specificationSetStructure';
import { useSpecificationSetEntity } from './useSpecificationSetEntity';

const EDIT_PATH = '/dwps-product-specifications/specification-set/edit';

export type SpecificationSetEditorMode = 'create' | 'edit';

// A group created by a drop is born holding what was dropped on it, and lands
// where the drop target sat rather than at the end.
export interface NewGroupOptions {
	position?: number | null;
	item?: StructureItem;
}

interface StoreSelectors {
	getPostId: () => number | null;
	getGeneral: () => SpecificationSetGeneralState;
	getStructure: () => SpecificationSetStructure;
	hasUnsavedChanges: () => boolean;
	isSaving: () => boolean;
	getNotice: () => NoticeState | null;
}

interface StoreActions {
	initializeEditor: (
		postId: number | null,
		general: SpecificationSetGeneralState,
		structure: SpecificationSetStructure
	) => void;
	addGroup: ( group: StructureGroup, position?: number | null ) => void;
	addItem: (
		groupKey: string,
		item: StructureItem,
		position?: number | null
	) => void;
	updateGroup: (
		groupKey: string,
		property: GroupProperty,
		value: StructureGroup[ GroupProperty ]
	) => void;
	moveGroup: ( groupKey: string, offset: number ) => void;
	removeGroup: ( groupKey: string, keepItemsIn?: string | null ) => void;
	updateItem: (
		groupKey: string,
		slug: string,
		property: ItemProperty,
		value: boolean
	) => void;
	moveItem: ( groupKey: string, slug: string, offset: number ) => void;
	moveItemToGroup: (
		fromGroupKey: string,
		toGroupKey: string,
		slug: string,
		position?: number | null
	) => void;
	removeItem: ( groupKey: string, slug: string ) => void;
	updateGeneralField: (
		field: keyof SpecificationSetGeneralState,
		value: SpecificationSetGeneralState[ keyof SpecificationSetGeneralState ]
	) => void;
	startSaving: () => void;
	finishSave: (
		postId: number,
		general: SpecificationSetGeneralState,
		structure: SpecificationSetStructure,
		notice: NoticeState
	) => void;
	failSave: ( notice: NoticeState ) => void;
	dismissNotice: () => void;
}

export interface SpecificationSetEditorData {
	mode: SpecificationSetEditorMode;
	postId: number | null;
	general: SpecificationSetGeneralState;
	structure: SpecificationSetStructure;
	hasUnsavedChanges: boolean;
	isSaving: boolean;
	isLoading: boolean;
	loadErrorMessage: string | null;
	notice: NoticeState | null;
	updateTextField: (
		field: keyof Omit< SpecificationSetGeneralState, 'status' >
	) => ( value: string | undefined ) => void;
	updateStatus: ( status: SpecificationSetStatus | undefined ) => void;
	addGroup: ( label: string, options?: NewGroupOptions ) => void;
	addItem: (
		groupKey: string,
		item: StructureItem,
		position?: number | null
	) => void;
	updateGroup: (
		groupKey: string,
		property: GroupProperty,
		value: StructureGroup[ GroupProperty ]
	) => void;
	moveGroup: ( groupKey: string, offset: number ) => void;
	removeGroup: ( groupKey: string, keepItemsIn?: string | null ) => void;
	updateItem: (
		groupKey: string,
		slug: string,
		property: ItemProperty,
		value: boolean
	) => void;
	moveItem: ( groupKey: string, slug: string, offset: number ) => void;
	moveItemToGroup: (
		fromGroupKey: string,
		toGroupKey: string,
		slug: string,
		position?: number | null
	) => void;
	removeItem: ( groupKey: string, slug: string ) => void;
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
	const { postId, general, structure, hasUnsavedChanges, isSaving, notice } =
		useSelect( ( select ) => {
			const selectors = select( editorStore ) as StoreSelectors;

			return {
				postId: selectors.getPostId(),
				general: selectors.getGeneral(),
				structure: selectors.getStructure(),
				hasUnsavedChanges: selectors.hasUnsavedChanges(),
				isSaving: selectors.isSaving(),
				notice: selectors.getNotice(),
			};
		}, [] );
	const {
		initializeEditor,
		updateGeneralField,
		addGroup,
		addItem,
		updateGroup,
		moveGroup,
		removeGroup,
		updateItem,
		moveItem,
		moveItemToGroup,
		removeItem,
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

	useUnsavedChangesGuard( hasUnsavedChanges );

	useEffect( () => {
		if ( mode !== 'create' ) {
			return;
		}

		const routeKey = 'create';

		if ( initializedRouteKey.current === routeKey ) {
			return;
		}

		initializedRouteKey.current = routeKey;
		initializeEditor(
			null,
			createEmptySpecificationSetGeneralState(),
			emptyStructure()
		);
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
			createSpecificationSetGeneralStateFromPostRecord( record ),
			createSpecificationSetStructureFromPostRecord( record )
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
				structure,
			} );

			finishSave(
				savedRecord.id,
				createSpecificationSetGeneralStateFromPostRecord( savedRecord ),
				createSpecificationSetStructureFromPostRecord( savedRecord ),
				{
					status: 'success',
					message: __(
						'Specification set saved.',
						'product-specifications'
					),
				}
			);

			// A set that exists is no longer being created. Without this the
			// route still says `new`, so the heading stays generic and a
			// reload opens an empty form for a set already saved.
			//
			// The guard is bypassed rather than answered: it reads a flag an
			// effect has not refreshed yet, so it would warn about changes this
			// navigation has just persisted.
			if ( mode === 'create' ) {
				initializedRouteKey.current = `edit:${ savedRecord.id }`;
				discardUnsavedChangesAndGoToAdminPath( EDIT_PATH, {
					id: savedRecord.id,
				} );
			}
		} catch ( error ) {
			failSave( {
				status: 'error',
				message: getSaveErrorMessage( error ),
			} );
		}
	};
	const saveRef = useRef( save );
	saveRef.current = save;

	// Bound on the document rather than the form: the browser's own save dialog
	// answers this shortcut from anywhere on the page, including inside a
	// dialog rendered in a portal outside the form.
	useEffect( () => {
		const onKeyDown = ( event: KeyboardEvent ) => {
			if ( ! isKeyboardEvent.primary( event, 's' ) ) {
				return;
			}

			event.preventDefault();
			saveRef.current();
		};

		document.addEventListener( 'keydown', onKeyDown );

		return () => document.removeEventListener( 'keydown', onKeyDown );
	}, [] );

	let loadErrorMessage: string | null = null;

	if ( mode === 'edit' ) {
		if ( ! requestedPostId ) {
			loadErrorMessage = __(
				'Invalid specification set id.',
				'product-specifications'
			);
		} else if ( loadError ) {
			loadErrorMessage = getLoadErrorMessage( loadError );
		} else if ( hasResolved && ! isResolving && ! record ) {
			loadErrorMessage = __(
				'Specification set was not found.',
				'product-specifications'
			);
		}
	}

	return {
		mode,
		postId,
		general,
		structure,
		hasUnsavedChanges,
		isSaving,
		isLoading:
			mode === 'edit' &&
			!! requestedPostId &&
			( isResolving || ! hasResolved ),
		loadErrorMessage,
		notice,
		updateTextField,
		updateStatus,
		addGroup: ( label: string, options: NewGroupOptions = {} ) =>
			addGroup(
				{
					key: groupKeyFrom(
						label,
						structure.groups.map( ( group ) => group.key )
					),
					label,
					description: '',
					visible: true,
					items: options.item ? [ options.item ] : [],
				},
				options.position ?? null
			),
		addItem,
		updateGroup,
		moveGroup,
		removeGroup,
		updateItem,
		moveItem,
		moveItemToGroup,
		removeItem,
		save,
		dismissNotice,
	};
}
