import {
	Button,
	DropdownMenu,
	Icon,
	MenuGroup,
	MenuItem,
	SearchControl,
	ToggleControl,
} from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import {
	plus,
	dragHandle,
	filter as filterIcon,
	moreVertical,
} from '@wordpress/icons';
import clsx from 'clsx';
import type { ReactNode } from 'react';
import { specificationTypes } from '../../shared/specificationTypes';
import type {
	LibraryEntry,
	SpecificationLibrary,
} from '../hooks/useSpecificationLibrary';
import { beginDrag } from './dragging';

interface Props {
	// While a specification is being created the column becomes the form: the
	// list it would show is not what the editor is looking at.
	form?: ReactNode;
	library: SpecificationLibrary;
	groups: { key: string; label: string }[];
	onDragStart: ( entry: LibraryEntry ) => void;
	onDragEnd: () => void;
	onAdd: ( entry: LibraryEntry, groupKey: string ) => void;
	onAddToNewGroup: ( entry: LibraryEntry ) => void;
	onNewSpecification: () => void;
}

// The count under the filters says what narrowed the column, because a number
// that says "hidden by search" when nothing was searched for is a lie.
function narrowedCount( library: SpecificationLibrary ): string {
	if ( library.search ) {
		return sprintf(
			/* translators: 1: how many specifications the column shows. 2: how many the search hides. */
			__(
				'%1$d shown · %2$d hidden by search',
				'product-specifications'
			),
			library.entries.length,
			library.hiddenCount
		);
	}

	return sprintf(
		/* translators: 1: how many specifications the column shows. 2: how many the filters hide. */
		__( '%1$d shown · %2$d hidden by filters', 'product-specifications' ),
		library.entries.length,
		library.hiddenCount
	);
}

const LibraryColumn = ( {
	form,
	library,
	groups,
	onDragStart,
	onDragEnd,
	onAdd,
	onAddToNewGroup,
	onNewSpecification,
}: Props ) => {
	const types = specificationTypes();
	const typeLabel = library.type ? types[ library.type ]?.label : undefined;

	if ( form ) {
		return (
			<section className="ps-composer-panel ps-composer-panel--library">
				{ form }
			</section>
		);
	}

	return (
		<section className="ps-composer-panel ps-composer-panel--library">
			<div className="ps-composer-panel__header is-stacked">
				<strong>
					{ __( 'Specification library', 'product-specifications' ) }
				</strong>
				<span>
					{ __(
						'Drag a specification into a group, or use its menu to move it',
						'product-specifications'
					) }
				</span>
			</div>
			<div className="ps-composer-panel__body">
				<SearchControl
					__nextHasNoMarginBottom
					label={ __(
						'Find a specification',
						'product-specifications'
					) }
					placeholder={ __(
						'Find a specification',
						'product-specifications'
					) }
					value={ library.search }
					onChange={ library.setSearch }
				/>
				<div className="ps-composer-filters">
					<DropdownMenu
						icon={ filterIcon }
						toggleProps={ { size: 'compact', variant: 'tertiary' } }
						label={ __( 'Type', 'product-specifications' ) }
						text={ __( 'Type', 'product-specifications' ) }
					>
						{ ( { onClose } ) => (
							<MenuGroup>
								{ Object.entries( types ).map(
									( [ id, type ] ) => (
										<MenuItem
											key={ id }
											isSelected={ library.type === id }
											role="menuitemradio"
											onClick={ () => {
												library.setType( id );
												onClose();
											} }
										>
											{ type.label }
										</MenuItem>
									)
								) }
							</MenuGroup>
						) }
					</DropdownMenu>
					{ typeLabel && (
						<Button
							size="compact"
							variant="tertiary"
							onClick={ () => library.setType( '' ) }
						>
							{ sprintf(
								/* translators: %s: the type the column is filtered by. */
								__( 'Type: %s ✕', 'product-specifications' ),
								typeLabel
							) }
						</Button>
					) }
				</div>
				<ToggleControl
					__nextHasNoMarginBottom
					label={ __(
						'Hide ones already used',
						'product-specifications'
					) }
					checked={ library.hideUsed }
					onChange={ library.setHideUsed }
				/>
				<p className="ps-composer-library__meta">
					{ library.search || library.type || library.hideUsed
						? narrowedCount( library )
						: sprintf(
								/* translators: 1: how many specifications exist. 2: how many are in this set. */
								__(
									'%1$d available · %2$d in this set',
									'product-specifications'
								),
								library.total,
								library.inSetCount
						  ) }
				</p>
				<ul className="ps-composer-library__list">
					{ library.entries.map( ( entry ) => (
						<li
							key={ entry.id }
							className={ clsx( 'ps-composer-library__item', {
								'is-used': entry.isInSet,
							} ) }
							draggable={ ! entry.isInSet }
							onDragStart={ ( event ) => {
								beginDrag( event, entry.name );
								onDragStart( entry );
							} }
							onDragEnd={ onDragEnd }
						>
							<Icon
								className="ps-composer-grip"
								icon={ dragHandle }
								size={ 20 }
							/>
							<span className="ps-composer-library__name">
								{ entry.name }
							</span>
							{ entry.isInSet ? (
								<span className="ps-pill is-neutral">
									{ __(
										'In this set',
										'product-specifications'
									) }
								</span>
							) : (
								<DropdownMenu
									icon={ moreVertical }
									label={ __(
										'Add to a group',
										'product-specifications'
									) }
									toggleProps={ { size: 'compact' } }
								>
									{ ( { onClose } ) => (
										<MenuGroup
											label={ __(
												'Add to',
												'product-specifications'
											) }
										>
											{ groups.map( ( group ) => (
												<MenuItem
													key={ group.key }
													onClick={ () => {
														onAdd(
															entry,
															group.key
														);
														onClose();
													} }
												>
													{ group.label }
												</MenuItem>
											) ) }
											<MenuItem
												onClick={ () => {
													onAddToNewGroup( entry );
													onClose();
												} }
											>
												{ __(
													'New group…',
													'product-specifications'
												) }
											</MenuItem>
										</MenuGroup>
									) }
								</DropdownMenu>
							) }
						</li>
					) ) }
					{ library.entries.length === 0 && ! library.isLoading && (
						<li className="ps-composer-library__empty">
							{ __(
								'Nothing matches. Clear the search, or create one below.',
								'product-specifications'
							) }
						</li>
					) }
				</ul>
			</div>
			<div className="ps-composer-panel__footer">
				<Button
					size="compact"
					variant="secondary"
					icon={ plus }
					onClick={ onNewSpecification }
				>
					{ __( 'New specification', 'product-specifications' ) }
				</Button>
			</div>
		</section>
	);
};

export default LibraryColumn;
