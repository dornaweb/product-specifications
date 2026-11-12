import {
	Button,
	DropdownMenu,
	Icon,
	MenuGroup,
	MenuItem,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __, sprintf, _n } from '@wordpress/i18n';
import { displayShortcut } from '@wordpress/keycodes';
import {
	chevronDown,
	chevronRight,
	dragHandle,
	moreVertical,
} from '@wordpress/icons';
import clsx from 'clsx';
import type { SpecificationSetEditorData } from '../hooks/useSpecificationSetEditor';
import type { StructureGroup as Group } from '../state';
import StructureItem from './StructureItem';
import { acceptDrop, beginDrag } from './dragging';

interface Props {
	group: Group;
	editor: SpecificationSetEditorData;
	otherGroups: Group[];
	nameOf: ( slug: string ) => string;
	onDragStart: ( groupKey: string ) => void;
	onDragEnd: () => void;
	onItemDragStart: ( slug: string ) => void;
	onDropInGroup: ( groupKey: string, position: number ) => void;
	onEdit: () => void;
	onDelete: () => void;
	onMoveItemToNewGroup: ( groupKey: string, slug: string ) => void;
}

const StructureGroup = ( {
	group,
	editor,
	otherGroups,
	nameOf,
	onDragStart,
	onDragEnd,
	onItemDragStart,
	onDropInGroup,
	onEdit,
	onDelete,
	onMoveItemToNewGroup,
}: Props ) => {
	const [ isCollapsed, setIsCollapsed ] = useState( false );
	const [ openItem, setOpenItem ] = useState< string | null >( null );
	const [ dropAt, setDropAt ] = useState< number | null >( null );

	const dropTarget = ( position: number ) => ( {
		onDragOver: ( event: React.DragEvent ) => {
			acceptDrop( event );
			setDropAt( position );
		},
		onDragLeave: () => setDropAt( null ),
		onDrop: ( event: React.DragEvent ) => {
			event.preventDefault();
			setDropAt( null );
			onDropInGroup( group.key, position );
		},
	} );

	return (
		<div
			className={ clsx( 'ps-composer-group', {
				'is-empty': group.items.length === 0,
				'is-hidden': ! group.visible,
			} ) }
		>
			<div
				className="ps-composer-group__header"
				draggable
				onDragStart={ ( event ) => {
					beginDrag( event, group.label );
					onDragStart( group.key );
				} }
				onDragEnd={ onDragEnd }
			>
				<Icon
					className="ps-composer-grip"
					icon={ dragHandle }
					size={ 20 }
				/>
				<Button
					size="small"
					variant="tertiary"
					icon={ isCollapsed ? chevronRight : chevronDown }
					label={
						isCollapsed
							? __( 'Expand group', 'product-specifications' )
							: __( 'Collapse group', 'product-specifications' )
					}
					onClick={ () => setIsCollapsed( ! isCollapsed ) }
				/>
				<span className="ps-composer-group__name">{ group.label }</span>
				<span className="ps-composer-group__count">
					{ group.items.length === 0
						? __( 'Empty group', 'product-specifications' )
						: sprintf(
								/* translators: %d: number of specifications in the group. */
								_n(
									'%d specification',
									'%d specifications',
									group.items.length,
									'product-specifications'
								),
								group.items.length
						  ) }
				</span>
				<DropdownMenu
					icon={ moreVertical }
					label={ __( 'Group actions', 'product-specifications' ) }
					className="ps-composer-group__menu"
					toggleProps={ { size: 'compact' } }
				>
					{ ( { onClose } ) => (
						<>
							<MenuGroup>
								<MenuItem
									onClick={ () => {
										onEdit();
										onClose();
									} }
								>
									{ __( 'Edit', 'product-specifications' ) }
								</MenuItem>
								<MenuItem
									shortcut={ displayShortcut.alt( '↑' ) }
									onClick={ () => {
										editor.moveGroup( group.key, -1 );
										onClose();
									} }
								>
									{ __(
										'Move up',
										'product-specifications'
									) }
								</MenuItem>
								<MenuItem
									shortcut={ displayShortcut.alt( '↓' ) }
									onClick={ () => {
										editor.moveGroup( group.key, 1 );
										onClose();
									} }
								>
									{ __(
										'Move down',
										'product-specifications'
									) }
								</MenuItem>
								<MenuItem
									onClick={ () => {
										editor.updateGroup(
											group.key,
											'visible',
											! group.visible
										);
										onClose();
									} }
								>
									{ group.visible
										? __(
												'Hide on the storefront',
												'product-specifications'
										  )
										: __(
												'Show on the storefront',
												'product-specifications'
										  ) }
								</MenuItem>
							</MenuGroup>
							<MenuGroup>
								<MenuItem
									isDestructive
									onClick={ () => {
										onDelete();
										onClose();
									} }
								>
									{ __( 'Delete', 'product-specifications' ) }
								</MenuItem>
							</MenuGroup>
						</>
					) }
				</DropdownMenu>
			</div>
			{ ! isCollapsed && (
				<div className="ps-composer-group__rows" role="list">
					{ group.items.map( ( item, index ) => (
						<div key={ item.slug }>
							{ /* Drawn at rest as well as during a drag: a
							     target that appears only once the pointer is
							     moving shifts the layout under it. */ }
							<div
								className={ clsx( 'ps-composer-insert', {
									'is-over': dropAt === index,
								} ) }
								{ ...dropTarget( index ) }
							/>
							<StructureItem
								item={ item }
								name={ nameOf( item.slug ) }
								groupKey={ group.key }
								otherGroups={ otherGroups }
								editor={ editor }
								isOpen={ openItem === item.slug }
								onToggleOptions={ () =>
									setOpenItem(
										openItem === item.slug
											? null
											: item.slug
									)
								}
								onDragStart={ () =>
									onItemDragStart( item.slug )
								}
								onDragEnd={ onDragEnd }
								onMoveToNewGroup={ () =>
									onMoveItemToNewGroup( group.key, item.slug )
								}
							/>
						</div>
					) ) }
					<div
						className={ clsx( 'ps-composer-drop', {
							'is-over': dropAt === group.items.length,
						} ) }
						{ ...dropTarget( group.items.length ) }
					>
						{ __(
							'Drop specifications here',
							'product-specifications'
						) }
					</div>
				</div>
			) }
		</div>
	);
};

export default StructureGroup;
