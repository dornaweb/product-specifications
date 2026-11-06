import { Button } from '@wordpress/components';
import { useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { plus, caution, tag } from '@wordpress/icons';
import { DataViews } from '@wordpress/dataviews';
import type { Action } from '@wordpress/dataviews';
import {
	PageShell,
	PageHeader,
	EmptyState,
	Notice,
	Shortcut,
} from '../shared/ui';
import AreaTabs from '../shared/ui/AreaTabs';
import { AREA_TABS } from '../shared/areas';
import { isNarrowedView } from '../shared/ui/listView';
import { useSpecifications } from './hooks/useSpecifications';
import { fields } from './fields';
import type { SpecificationItem } from './mapRecord';
import SpecificationForm, {
	draftFrom,
	emptyDraft,
	type SpecificationDraft,
} from './components/SpecificationForm';
import DeleteDialog from './components/DeleteDialog';
import { payloadFrom } from './payload';

import './styles.scss';

type FormState =
	| { mode: 'closed' }
	| { mode: 'create'; draft: SpecificationDraft; typeCarried: boolean }
	| { mode: 'edit'; id: number; slug: string; draft: SpecificationDraft };

const SpecificationsPage = () => {
	const list = useSpecifications();
	const [ form, setForm ] = useState< FormState >( { mode: 'closed' } );
	const [ isSaving, setIsSaving ] = useState( false );
	const [ pendingDelete, setPendingDelete ] = useState<
		SpecificationItem[] | null
	>( null );
	const [ isDeleting, setIsDeleting ] = useState( false );
	const listRef = useRef< HTMLDivElement | null >( null );

	// Search is the primary control on the screen, so the first keystroke on
	// arrival searches.
	useEffect( () => {
		listRef.current
			?.querySelector< HTMLInputElement >( 'input[type="search"]' )
			?.focus();
	}, [] );

	// `N` opens the create form, but only when focus is not already in a field:
	// otherwise it would swallow the letter being typed.
	useEffect( () => {
		const onKeyDown = ( event: KeyboardEvent ) => {
			const target = event.target as HTMLElement | null;
			const isEditing = target?.closest(
				'input, textarea, select, [contenteditable]'
			);

			if (
				event.key.toLowerCase() === 'n' &&
				! isEditing &&
				! event.metaKey &&
				! event.ctrlKey &&
				! event.altKey
			) {
				event.preventDefault();
				openCreate();
			}
		};

		document.addEventListener( 'keydown', onKeyDown );

		return () => document.removeEventListener( 'keydown', onKeyDown );
	}, [] );

	const openCreate = ( name = '' ) =>
		setForm( {
			mode: 'create',
			draft: { ...emptyDraft(), name },
			typeCarried: false,
		} );

	const openEdit = ( item: SpecificationItem ) =>
		setForm( {
			mode: 'edit',
			id: item.id,
			slug: item.slug,
			draft: draftFrom( item ),
		} );

	const save = async ( andAnother: boolean ) => {
		if ( form.mode === 'closed' ) {
			return;
		}

		setIsSaving( true );

		const saved =
			form.mode === 'create'
				? await list.create( payloadFrom( form.draft ) )
				: await list.update( form.id, payloadFrom( form.draft ) );

		setIsSaving( false );

		if ( ! saved ) {
			return;
		}

		// The type carries into the next entry, because a run of entries is
		// usually the same type.
		setForm(
			andAnother
				? {
						mode: 'create',
						draft: emptyDraft( form.draft.type ),
						typeCarried: true,
				  }
				: { mode: 'closed' }
		);
	};

	const duplicateOf = useMemo( () => {
		if ( form.mode === 'closed' ) {
			return undefined;
		}

		const name = form.draft.name.trim().toLowerCase();

		if ( ! name ) {
			return undefined;
		}

		return list.data.find(
			( item ) =>
				item.name.toLowerCase() === name &&
				( form.mode !== 'edit' || item.id !== form.id )
		);
	}, [ form, list.data ] );

	const actions: Action< SpecificationItem >[] = [
		{
			id: 'edit',
			label: __( 'Edit', 'product-specifications' ),
			isPrimary: true,
			callback: ( items: SpecificationItem[] ) => {
				const [ item ] = items;

				if ( item ) {
					openEdit( item );
				}
			},
		},
		{
			id: 'delete',
			label: __( 'Delete', 'product-specifications' ),
			supportsBulk: true,
			callback: ( items: SpecificationItem[] ) =>
				setPendingDelete( items ),
		},
	];

	const addButton = (
		<Button variant="primary" icon={ plus } onClick={ () => openCreate() }>
			{ __( 'Add specification', 'product-specifications' ) }
			<Shortcut keys="N" />
		</Button>
	);

	// An open form must survive an empty collection: the first specification is
	// created from an empty list, and onboarding standing in front of the form
	// would make the Add button look dead.
	const hasNothingToShow =
		! list.isLoading &&
		! list.hasError &&
		list.data.length === 0 &&
		! isNarrowedView( list.view );
	const isEmpty = hasNothingToShow && form.mode === 'closed';
	const search = list.view.search ?? '';

	let content = (
		<div className="ps-surface" ref={ listRef }>
			{ form.mode !== 'closed' && (
				<SpecificationForm
					key={
						form.mode === 'edit'
							? `edit-${ form.id }`
							: `create-${ list.justAddedCount }`
					}
					draft={ form.draft }
					slug={ form.mode === 'edit' ? form.slug : undefined }
					label={
						form.mode === 'edit'
							? form.draft.name
							: createLabel( list.justAddedCount )
					}
					typeCarried={ form.mode === 'create' && form.typeCarried }
					isSaving={ isSaving }
					duplicateOf={ duplicateOf }
					onOpenDuplicate={ openEdit }
					onChange={ ( draft ) => setForm( { ...form, draft } ) }
					onSaveAndClose={ () => save( false ) }
					onSaveAndAddAnother={
						form.mode === 'create' ? () => save( true ) : undefined
					}
					onCancel={ () => setForm( { mode: 'closed' } ) }
					error={
						list.notice?.status === 'error'
							? list.notice.message
							: undefined
					}
				/>
			) }
			{ ! hasNothingToShow && (
				<DataViews< SpecificationItem >
					view={ list.view }
					onChangeView={ list.setView }
					fields={ fields() }
					actions={ actions }
					data={ list.data }
					isLoading={ list.isLoading }
					paginationInfo={ list.paginationInfo }
					getItemId={ ( item ) => String( item.id ) }
					defaultLayouts={ { table: {} } }
					searchLabel={ __(
						'Search specifications',
						'product-specifications'
					) }
					empty={
						<EmptyState
							title={
								search
									? sprintf(
											/* translators: %s: the search term that matched nothing. */
											__(
												'Nothing matches “%s”',
												'product-specifications'
											),
											search
									  )
									: __(
											'Nothing matches these filters',
											'product-specifications'
									  )
							}
							copy={
								search
									? __(
											'No specification has that name or slug. A search that finds nothing is usually the name of the thing you are about to add.',
											'product-specifications'
									  )
									: __(
											'No specification of this type exists yet.',
											'product-specifications'
									  )
							}
							actions={
								<>
									{ search && (
										<Button
											variant="primary"
											icon={ plus }
											onClick={ () =>
												openCreate( search )
											}
										>
											{ sprintf(
												/* translators: %s: the search term that matched nothing. */
												__(
													'Create “%s”',
													'product-specifications'
												),
												search
											) }
										</Button>
									) }
									<Button
										variant="tertiary"
										onClick={ () =>
											list.setView( {
												...list.view,
												page: 1,
												search: '',
												filters: [],
											} )
										}
									>
										{ __(
											'Clear search',
											'product-specifications'
										) }
									</Button>
								</>
							}
						/>
					}
				/>
			) }
		</div>
	);

	if ( isEmpty ) {
		content = (
			<EmptyState
				icon={ tag }
				title={ __(
					'No specifications yet',
					'product-specifications'
				) }
				copy={ __(
					'A specification is one row of a product table — a screen size, a weight, a material. Define it once and use it in any set.',
					'product-specifications'
				) }
				actions={ addButton }
			/>
		);
	}

	if ( list.hasError ) {
		content = (
			<EmptyState
				icon={ caution }
				title={ __(
					'Specifications could not be loaded',
					'product-specifications'
				) }
				copy={ __(
					'Your specifications are still intact. Check the connection and try again.',
					'product-specifications'
				) }
				actions={
					<Button variant="secondary" onClick={ list.retry }>
						{ __( 'Retry', 'product-specifications' ) }
					</Button>
				}
			/>
		);
	}

	return (
		<PageShell
			header={
				<PageHeader
					title={ __( 'Specifications', 'product-specifications' ) }
					description={ __(
						'Define each specification once, then use it in any set.',
						'product-specifications'
					) }
					actions={ addButton }
					tabs={ <AreaTabs tabs={ AREA_TABS } /> }
				/>
			}
			notices={
				<Notice
					notice={
						// A failed save is reported on the form that failed,
						// where the work still is.
						form.mode !== 'closed' &&
						list.notice?.status === 'error'
							? null
							: list.notice
					}
					onDismiss={ list.dismissNotice }
				/>
			}
		>
			{ content }
			{ pendingDelete && (
				<DeleteDialog
					items={ pendingDelete }
					isDeleting={ isDeleting }
					onCancel={ () => setPendingDelete( null ) }
					onConfirm={ async () => {
						setIsDeleting( true );
						await list.remove( pendingDelete );
						setIsDeleting( false );
						setPendingDelete( null );
					} }
				/>
			) }
		</PageShell>
	);
};

// The count of what this sitting has added, so a run of entries reports itself.
function createLabel( added: number ): string {
	if ( added === 0 ) {
		return __( 'New specification', 'product-specifications' );
	}

	return sprintf(
		/* translators: %d: how many specifications were added in this sitting. */
		__( 'New specification · %d added', 'product-specifications' ),
		added
	);
}

export default SpecificationsPage;
