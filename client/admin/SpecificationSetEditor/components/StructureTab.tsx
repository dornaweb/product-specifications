import { Button } from '@wordpress/components';
import { useMemo, useState } from '@wordpress/element';
import { __, sprintf, _n } from '@wordpress/i18n';
import { plus } from '@wordpress/icons';
import clsx from 'clsx';
import type { SpecificationSetEditorData } from '../hooks/useSpecificationSetEditor';
import {
	useSpecificationLibrary,
	type LibraryEntry,
} from '../hooks/useSpecificationLibrary';
import type { StructureItem } from '../state';
import LibraryColumn from './LibraryColumn';
import StructureGroup from './StructureGroup';
import GroupDialog from './GroupDialog';
import GroupDeleteDialog from './GroupDeleteDialog';
import NewSpecificationForm from './NewSpecificationForm';
import { acceptDrop } from './dragging';

const DEFAULT_GROUP_LABEL = __( 'New group', 'product-specifications' );

// What the pointer is carrying. A group drags to reorder; a specification drags
// from the library into a group, or from one group to another.
type Dragging =
	| { kind: 'library'; item: StructureItem }
	| { kind: 'item'; item: StructureItem; fromGroupKey: string }
	| { kind: 'group'; groupKey: string }
	| null;

// A group about to be created, holding what was dropped to create it.
interface PendingGroup {
	label: string;
	position: number | null;
	item?: StructureItem;
	from?: string;
}

const itemFrom = ( entry: LibraryEntry ): StructureItem => ( {
	term_id: entry.id,
	slug: entry.slug,
	visible: true,
	comparable: false,
} );

const StructureTab = ( { editor }: { editor: SpecificationSetEditorData } ) => {
	const { groups } = editor.structure;
	const [ dragging, setDragging ] = useState< Dragging >( null );
	const [ dropAt, setDropAt ] = useState< number | null >( null );
	const [ pendingGroup, setPendingGroup ] = useState< PendingGroup | null >(
		null
	);
	const [ editingGroupKey, setEditingGroupKey ] = useState< string | null >(
		null
	);
	const [ deletingGroupKey, setDeletingGroupKey ] = useState< string | null >(
		null
	);
	const [ isCreatingSpecification, setIsCreatingSpecification ] =
		useState( false );

	const held = useMemo(
		() => groups.flatMap( ( group ) => group.items ),
		[ groups ]
	);
	const library = useSpecificationLibrary(
		useMemo( () => held.map( ( item ) => item.slug ), [ held ] ),
		useMemo( () => held.map( ( item ) => item.term_id ), [ held ] )
	);

	const specificationCount = held.length;

	const dropInGroup = ( groupKey: string, position: number ) => {
		if ( ! dragging ) {
			return;
		}

		if ( dragging.kind === 'library' ) {
			editor.addItem( groupKey, dragging.item, position );
		}

		if ( dragging.kind === 'item' ) {
			editor.moveItemToGroup(
				dragging.fromGroupKey,
				groupKey,
				dragging.item.slug,
				position
			);
		}

		setDragging( null );
	};

	// Dropping between groups either places a new group there, or moves the
	// group being dragged into that gap.
	const dropBetweenGroups = ( position: number ) => {
		if ( ! dragging ) {
			return;
		}

		if ( dragging.kind === 'group' ) {
			const from = groups.findIndex(
				( group ) => group.key === dragging.groupKey
			);
			const landing = position > from ? position - 1 : position;

			editor.moveGroup( dragging.groupKey, landing - from );
		} else {
			setPendingGroup( {
				label: DEFAULT_GROUP_LABEL,
				position,
				item: dragging.item,
				from:
					dragging.kind === 'item'
						? dragging.fromGroupKey
						: undefined,
			} );
		}

		setDropAt( null );
		setDragging( null );
	};

	const createGroup = ( label: string ) => {
		if ( ! pendingGroup ) {
			return;
		}

		// The item leaves its old group first: a group born holding it would
		// otherwise put the same specification in the set twice.
		if ( pendingGroup.from && pendingGroup.item ) {
			editor.removeItem( pendingGroup.from, pendingGroup.item.slug );
		}

		editor.addGroup( label, {
			position: pendingGroup.position,
			item: pendingGroup.item,
		} );
		setPendingGroup( null );
	};

	const addToNewGroup = ( entry: LibraryEntry ) =>
		setPendingGroup( {
			label: DEFAULT_GROUP_LABEL,
			position: null,
			item: itemFrom( entry ),
		} );

	const insertTarget = ( position: number ) => ( {
		onDragOver: ( event: React.DragEvent ) => {
			acceptDrop( event );
			setDropAt( position );
		},
		onDragLeave: () => setDropAt( null ),
		onDrop: ( event: React.DragEvent ) => {
			event.preventDefault();
			dropBetweenGroups( position );
		},
	} );

	const editingGroup = groups.find(
		( group ) => group.key === editingGroupKey
	);
	const deletingGroup = groups.find(
		( group ) => group.key === deletingGroupKey
	);

	return (
		<div className="ps-composer">
			<LibraryColumn
				library={ library }
				groups={ groups }
				form={
					isCreatingSpecification && (
						<NewSpecificationForm
							onCreated={ ( entry ) => {
								const target = groups[ groups.length - 1 ];

								// A specification created here was created to
								// be used here, so it joins the set as well as
								// the library.
								if ( target ) {
									editor.addItem(
										target.key,
										itemFrom( entry )
									);
								} else {
									addToNewGroup( entry );
								}

								setIsCreatingSpecification( false );
							} }
							onCancel={ () =>
								setIsCreatingSpecification( false )
							}
						/>
					)
				}
				onDragStart={ ( entry ) =>
					setDragging( { kind: 'library', item: itemFrom( entry ) } )
				}
				onDragEnd={ () => setDragging( null ) }
				onAdd={ ( entry, groupKey ) =>
					editor.addItem( groupKey, itemFrom( entry ) )
				}
				onAddToNewGroup={ addToNewGroup }
				onNewSpecification={ () => setIsCreatingSpecification( true ) }
			/>
			<section className="ps-composer-panel ps-composer-panel--structure">
				<div className="ps-composer-panel__header">
					<div className="ps-composer-panel__title">
						<strong>
							{ __( 'Set structure', 'product-specifications' ) }
						</strong>
						<span>
							{ groups.length === 0
								? __(
										'No groups yet',
										'product-specifications'
								  )
								: sprintf(
										/* translators: 1: number of groups. 2: number of specifications. */
										__(
											'%1$s · %2$s',
											'product-specifications'
										),
										sprintf(
											/* translators: %d: number of groups. */
											_n(
												'%d group',
												'%d groups',
												groups.length,
												'product-specifications'
											),
											groups.length
										),
										sprintf(
											/* translators: %d: number of specifications. */
											_n(
												'%d specification',
												'%d specifications',
												specificationCount,
												'product-specifications'
											),
											specificationCount
										)
								  ) }
						</span>
					</div>
				</div>
				<div className="ps-composer-panel__body is-structure">
					{ groups.map( ( group, index ) => (
						<div key={ group.key }>
							{ index > 0 && (
								<div
									className={ clsx(
										'ps-composer-group-insert',
										{ 'is-over': dropAt === index }
									) }
									{ ...insertTarget( index ) }
								/>
							) }
							<StructureGroup
								group={ group }
								editor={ editor }
								otherGroups={ groups.filter(
									( other ) => other.key !== group.key
								) }
								nameOf={ ( slug ) =>
									library.nameOf( slug ) ?? slug
								}
								onDragStart={ ( groupKey ) =>
									setDragging( { kind: 'group', groupKey } )
								}
								onDragEnd={ () => setDragging( null ) }
								onItemDragStart={ ( slug ) => {
									const item = group.items.find(
										( entry ) => entry.slug === slug
									);

									if ( item ) {
										setDragging( {
											kind: 'item',
											item,
											fromGroupKey: group.key,
										} );
									}
								} }
								onDropInGroup={ dropInGroup }
								onEdit={ () => setEditingGroupKey( group.key ) }
								onDelete={ () =>
									setDeletingGroupKey( group.key )
								}
								onMoveItemToNewGroup={ ( groupKey, slug ) => {
									const item = group.items.find(
										( entry ) => entry.slug === slug
									);

									if ( item ) {
										setPendingGroup( {
											label: DEFAULT_GROUP_LABEL,
											position: null,
											item,
											from: groupKey,
										} );
									}
								} }
							/>
						</div>
					) ) }
					<div
						className={ clsx( 'ps-composer-drop is-new-group', {
							'is-over': dropAt === groups.length,
							'is-empty': groups.length === 0,
						} ) }
						{ ...insertTarget( groups.length ) }
					>
						{ groups.length === 0 ? (
							<span>
								<strong>
									{ __(
										'Drag a specification here to create your first group',
										'product-specifications'
									) }
								</strong>
								<small>
									{ __(
										'Or use Add group to make an empty one',
										'product-specifications'
									) }
								</small>
							</span>
						) : (
							__(
								'Drop here to create a group',
								'product-specifications'
							)
						) }
					</div>
					<div className="ps-composer-add-group">
						<Button
							variant="secondary"
							icon={ plus }
							onClick={ () =>
								setPendingGroup( {
									label: DEFAULT_GROUP_LABEL,
									position: null,
								} )
							}
						>
							{ __( 'Add group', 'product-specifications' ) }
						</Button>
					</div>
				</div>
			</section>
			{ pendingGroup && (
				<GroupDialog
					label={ pendingGroup.label }
					isNew
					onConfirm={ createGroup }
					onCancel={ () => setPendingGroup( null ) }
				/>
			) }
			{ editingGroup && (
				<GroupDialog
					label={ editingGroup.label }
					isNew={ false }
					onConfirm={ ( label ) => {
						editor.updateGroup( editingGroup.key, 'label', label );
						setEditingGroupKey( null );
					} }
					onCancel={ () => setEditingGroupKey( null ) }
				/>
			) }
			{ deletingGroup && (
				<GroupDeleteDialog
					group={ deletingGroup }
					otherGroups={ groups.filter(
						( group ) => group.key !== deletingGroup.key
					) }
					onConfirm={ ( keepItemsIn ) => {
						editor.removeGroup( deletingGroup.key, keepItemsIn );
						setDeletingGroupKey( null );
					} }
					onCancel={ () => setDeletingGroupKey( null ) }
				/>
			) }
		</div>
	);
};

export default StructureTab;
