import { DEFAULT_STATE } from './default-state';
import type {
	SpecificationSetEditorAction,
	SpecificationSetEditorState,
	StructureGroup,
	StructureItem,
} from './types';

const moved = < T >( entries: T[], index: number, offset: number ): T[] => {
	const target = index + offset;

	if ( index < 0 || target < 0 || target >= entries.length ) {
		return entries;
	}

	const next = [ ...entries ];
	const [ entry ] = next.splice( index, 1 );

	if ( entry ) {
		next.splice( target, 0, entry );
	}

	return next;
};

const withGroups = (
	state: SpecificationSetEditorState,
	groups: StructureGroup[]
): SpecificationSetEditorState => ( {
	...state,
	hasUnsavedChanges: true,
	notice: null,
	form: {
		...state.form,
		structure: { ...state.form.structure, groups },
	},
} );

// A position of null appends, which is what every keyboard path wants; a drop
// carries the index it landed on.
const insertedAt = < T >(
	entries: T[],
	entry: T,
	position: number | null
): T[] => {
	if ( position === null ) {
		return [ ...entries, entry ];
	}

	const next = [ ...entries ];

	next.splice( Math.max( 0, Math.min( position, next.length ) ), 0, entry );

	return next;
};

const mapGroup = (
	groups: StructureGroup[],
	groupKey: string,
	change: ( group: StructureGroup ) => StructureGroup
): StructureGroup[] =>
	groups.map( ( group ) =>
		group.key === groupKey ? change( group ) : group
	);

const mapItems = (
	groups: StructureGroup[],
	groupKey: string,
	change: ( items: StructureItem[] ) => StructureItem[]
): StructureGroup[] =>
	mapGroup( groups, groupKey, ( group ) => ( {
		...group,
		items: change( group.items ),
	} ) );

export const reducer = (
	state = DEFAULT_STATE,
	action: SpecificationSetEditorAction
): SpecificationSetEditorState => {
	const { groups } = state.form.structure;

	switch ( action.type ) {
		case 'INITIALIZE_EDITOR':
			return {
				...state,
				postId: action.postId,
				hasUnsavedChanges: false,
				isSaving: false,
				notice: null,
				form: {
					general: action.general,
					structure: action.structure,
				},
			};
		case 'UPDATE_GENERAL_FIELD':
			return {
				...state,
				hasUnsavedChanges: true,
				notice: null,
				form: {
					...state.form,
					general: {
						...state.form.general,
						[ action.field ]: action.value,
					},
				},
			};
		case 'ADD_GROUP':
			return withGroups(
				state,
				insertedAt( groups, action.group, action.position )
			);
		case 'ADD_ITEM': {
			// A specification belongs to a set once. The library column marks
			// one already in the set rather than hiding it, so a second drop of
			// the same row is a gesture the editor will make.
			const alreadyHeld = groups.some( ( group ) =>
				group.items.some( ( item ) => item.slug === action.item.slug )
			);

			if ( alreadyHeld ) {
				return state;
			}

			return withGroups(
				state,
				mapItems( groups, action.groupKey, ( items ) =>
					insertedAt( items, action.item, action.position )
				)
			);
		}
		case 'UPDATE_GROUP':
			return withGroups(
				state,
				mapGroup( groups, action.groupKey, ( group ) => ( {
					...group,
					[ action.property ]: action.value,
				} ) )
			);
		case 'MOVE_GROUP':
			return withGroups(
				state,
				moved(
					groups,
					groups.findIndex(
						( group ) => group.key === action.groupKey
					),
					action.offset
				)
			);
		case 'REMOVE_GROUP': {
			const removed = groups.find(
				( group ) => group.key === action.groupKey
			);
			const remaining = groups.filter(
				( group ) => group.key !== action.groupKey
			);

			if ( ! removed || ! action.keepItemsIn ) {
				return withGroups( state, remaining );
			}

			return withGroups(
				state,
				mapItems( remaining, action.keepItemsIn, ( items ) => [
					...items,
					...removed.items,
				] )
			);
		}
		case 'UPDATE_ITEM':
			return withGroups(
				state,
				mapItems( groups, action.groupKey, ( items ) =>
					items.map( ( item ) =>
						item.slug === action.slug
							? { ...item, [ action.property ]: action.value }
							: item
					)
				)
			);
		case 'MOVE_ITEM':
			return withGroups(
				state,
				mapItems( groups, action.groupKey, ( items ) =>
					moved(
						items,
						items.findIndex(
							( item ) => item.slug === action.slug
						),
						action.offset
					)
				)
			);
		case 'MOVE_ITEM_TO_GROUP': {
			const item = groups
				.find( ( group ) => group.key === action.fromGroupKey )
				?.items.find( ( entry ) => entry.slug === action.slug );

			if ( ! item ) {
				return state;
			}

			const without = mapItems( groups, action.fromGroupKey, ( items ) =>
				items.filter( ( entry ) => entry.slug !== action.slug )
			);

			return withGroups(
				state,
				mapItems( without, action.toGroupKey, ( items ) =>
					insertedAt( items, item, action.position )
				)
			);
		}
		case 'REMOVE_ITEM':
			return withGroups(
				state,
				mapItems( groups, action.groupKey, ( items ) =>
					items.filter( ( item ) => item.slug !== action.slug )
				)
			);
		case 'START_SAVING':
			return { ...state, isSaving: true, notice: null };
		case 'FINISH_SAVE':
			return {
				...state,
				postId: action.postId,
				hasUnsavedChanges: false,
				isSaving: false,
				notice: action.notice,
				form: {
					general: action.general,
					structure: action.structure,
				},
			};
		case 'FAIL_SAVE':
			return { ...state, isSaving: false, notice: action.notice };
		case 'DISMISS_NOTICE':
			return { ...state, notice: null };
		default:
			return state;
	}
};
