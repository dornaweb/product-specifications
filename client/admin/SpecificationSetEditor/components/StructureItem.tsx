import {
	Button,
	DropdownMenu,
	MenuGroup,
	MenuItem,
	ToggleControl,
} from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { displayShortcut, isKeyboardEvent } from '@wordpress/keycodes';
import { dragHandle, moreVertical } from '@wordpress/icons';
import clsx from 'clsx';
import type { SpecificationSetEditorData } from '../hooks/useSpecificationSetEditor';
import type { StructureGroup, StructureItem as Item } from '../state';
import { beginDrag } from './dragging';

interface Props {
	item: Item;
	name: string;
	groupKey: string;
	otherGroups: StructureGroup[];
	editor: SpecificationSetEditorData;
	isOpen: boolean;
	onToggleOptions: () => void;
	onDragStart: () => void;
	onDragEnd: () => void;
	onMoveToNewGroup: () => void;
}

const StructureItem = ( {
	item,
	name,
	groupKey,
	otherGroups,
	editor,
	isOpen,
	onToggleOptions,
	onDragStart,
	onDragEnd,
	onMoveToNewGroup,
}: Props ) => (
	<>
		<div
			className={ clsx( 'ps-composer-row', {
				'is-hidden': ! item.visible,
			} ) }
			draggable
			onDragStart={ ( event ) => {
				beginDrag( event, name );
				onDragStart();
			} }
			onDragEnd={ onDragEnd }
			role="listitem"
		>
			{ /* The grip is the drag handle and the keyboard's equivalent of
			     it: focus it and the move shortcuts apply to this row. */ }
			<Button
				className="ps-composer-grip"
				size="compact"
				variant="tertiary"
				icon={ dragHandle }
				label={ sprintf(
					/* translators: %s: the specification being moved. */
					__( 'Move %s', 'product-specifications' ),
					name
				) }
				onKeyDown={ ( event: React.KeyboardEvent< HTMLElement > ) => {
					if ( isKeyboardEvent.alt( event, 'ArrowUp' ) ) {
						event.preventDefault();
						editor.moveItem( groupKey, item.slug, -1 );
					}

					if ( isKeyboardEvent.alt( event, 'ArrowDown' ) ) {
						event.preventDefault();
						editor.moveItem( groupKey, item.slug, 1 );
					}
				} }
			/>
			<span className="ps-composer-row__name">{ name }</span>
			<span className="ps-composer-row__actions">
				<Button
					size="compact"
					variant="tertiary"
					onClick={ onToggleOptions }
					aria-expanded={ isOpen }
				>
					{ __( 'Options', 'product-specifications' ) }
				</Button>
				<Button
					size="compact"
					variant="tertiary"
					onClick={ () => editor.removeItem( groupKey, item.slug ) }
				>
					{ __( 'Remove', 'product-specifications' ) }
				</Button>
				<DropdownMenu
					icon={ moreVertical }
					label={ __( 'Item actions', 'product-specifications' ) }
					toggleProps={ { size: 'compact' } }
				>
					{ ( { onClose } ) => (
						<>
							<MenuGroup>
								<MenuItem
									shortcut={ displayShortcut.alt( '↑' ) }
									onClick={ () => {
										editor.moveItem(
											groupKey,
											item.slug,
											-1
										);
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
										editor.moveItem(
											groupKey,
											item.slug,
											1
										);
										onClose();
									} }
								>
									{ __(
										'Move down',
										'product-specifications'
									) }
								</MenuItem>
							</MenuGroup>
							<MenuGroup
								label={ __(
									'Move to group',
									'product-specifications'
								) }
							>
								{ otherGroups.map( ( target ) => (
									<MenuItem
										key={ target.key }
										onClick={ () => {
											editor.moveItemToGroup(
												groupKey,
												target.key,
												item.slug
											);
											onClose();
										} }
									>
										{ target.label }
									</MenuItem>
								) ) }
								<MenuItem
									onClick={ () => {
										onMoveToNewGroup();
										onClose();
									} }
								>
									{ __(
										'New group…',
										'product-specifications'
									) }
								</MenuItem>
							</MenuGroup>
							<MenuGroup>
								<MenuItem
									isDestructive
									onClick={ () => {
										editor.removeItem(
											groupKey,
											item.slug
										);
										onClose();
									} }
								>
									{ __(
										'Remove from set',
										'product-specifications'
									) }
								</MenuItem>
							</MenuGroup>
						</>
					) }
				</DropdownMenu>
			</span>
		</div>
		{ isOpen && (
			<div className="ps-item-options">
				<ToggleControl
					__nextHasNoMarginBottom
					label={ __(
						'Visible on the storefront',
						'product-specifications'
					) }
					checked={ item.visible }
					onChange={ ( value ) =>
						editor.updateItem(
							groupKey,
							item.slug,
							'visible',
							value
						)
					}
				/>
				<p className="ps-item-options__help">
					{ __(
						'These apply to this set only. The same specification can be visible in one set and hidden in another.',
						'product-specifications'
					) }
				</p>
				<p className="ps-item-options__help">
					{ __(
						'Looking for a default value? It lives on the specification itself, so it is the same everywhere.',
						'product-specifications'
					) }
				</p>
			</div>
		) }
	</>
);

export default StructureItem;
