import { __, sprintf, _n } from '@wordpress/i18n';
import { useMemo, useState } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { plus, layout as layoutIcon, caution, tag } from '@wordpress/icons';
import { DataViews } from '@wordpress/dataviews';
import { PageShell, PageHeader, EmptyState, Notice } from '../shared/ui';
import { isNarrowedView } from '../shared/ui/listView';
import AreaTabs from '../shared/ui/AreaTabs';
import { AREA_TABS } from '../shared/areas';
import { adminPath, goToAdminPath } from '../shared/platform';
import { useSpecificationSets } from './hooks/useSpecificationSets';
import { useSpecificationCount } from '../Specifications/hooks/useSpecificationCount';
import { fields, createActions } from './fields';
import type { SpecificationSetItem } from './fields';
import DeleteDialog from './components/DeleteDialog';

const CREATE_PATH = '/dwps-product-specifications/specification-set/new';
const LIBRARY_PATH = '/dwps-product-specifications/specifications';

const goToCreate = ( event: React.MouseEvent ) => {
	event.preventDefault();
	goToAdminPath( CREATE_PATH );
};

const EmptyCollection = ( {
	createButton,
	specifications,
}: {
	createButton: JSX.Element;
	specifications: number;
} ) => (
	<EmptyState
		icon={ layoutIcon }
		title={ __( 'No specification sets yet', 'product-specifications' ) }
		copy={ sprintf(
			/* translators: %s: how many specifications the library holds. */
			__(
				'A set decides which specifications a kind of product has, and how they are grouped on the storefront. %s ready to use.',
				'product-specifications'
			),
			sprintf(
				/* translators: %d: number of specifications. */
				_n(
					'You have %d specification',
					'You have %d specifications',
					specifications,
					'product-specifications'
				),
				specifications
			)
		) }
		actions={ createButton }
	/>
);

// Zero sets and zero specifications is a first run, not an empty list: the
// answer is to define a specification, and the steps name the whole journey.
const FirstRun = ( { createButton }: { createButton: JSX.Element } ) => (
	<EmptyState
		icon={ tag }
		title={ __(
			'Start with the details customers compare',
			'product-specifications'
		) }
		copy={ __(
			'A specification is one reusable fact about a product — like screen size, material, or warranty. Define those facts first, then group them into a set for each product family.',
			'product-specifications'
		) }
		steps={ [
			{
				title: __( 'Define specifications', 'product-specifications' ),
				copy: __(
					'Choose each fact and its value format.',
					'product-specifications'
				),
			},
			{
				title: __( 'Create a set', 'product-specifications' ),
				copy: __(
					'Organize related facts into clear groups.',
					'product-specifications'
				),
			},
			{
				title: __( 'Fill product values', 'product-specifications' ),
				copy: __(
					'Assign the set and enter product-specific data.',
					'product-specifications'
				),
			},
		] }
		actions={
			<>
				<Button
					variant="primary"
					icon={ plus }
					href={ adminPath( LIBRARY_PATH ) }
					onClick={ ( event: React.MouseEvent ) => {
						event.preventDefault();
						goToAdminPath( LIBRARY_PATH );
					} }
				>
					{ __(
						'Define your first specification',
						'product-specifications'
					) }
				</Button>
				{ createButton }
			</>
		}
	/>
);

const SpecificationSetsPage = () => {
	const {
		view,
		setView,
		data,
		isLoading,
		hasError,
		retry,
		notice,
		dismissNotice,
		handlers,
		paginationInfo,
	} = useSpecificationSets();
	const specifications = useSpecificationCount();
	const [ pendingDelete, setPendingDelete ] = useState<
		SpecificationSetItem[] | null
	>( null );
	const [ isDeleting, setIsDeleting ] = useState( false );
	// Delete asks first: what happens to the products assigned to the set is
	// not something a row action should decide on one click.
	const actions = useMemo(
		() =>
			createActions( {
				...handlers,
				remove: async ( items: SpecificationSetItem[] ) =>
					setPendingDelete( items ),
			} ),
		[ handlers ]
	);

	// A search box over nothing is a control that cannot do anything, so an
	// empty collection replaces the whole list surface.
	const isEmpty =
		! isLoading &&
		! hasError &&
		specifications !== null &&
		data.length === 0 &&
		! isNarrowedView( view );

	const createButton = (
		<Button
			variant="primary"
			icon={ plus }
			href={ adminPath( CREATE_PATH ) }
			onClick={ goToCreate }
		>
			{ __( 'Add set', 'product-specifications' ) }
		</Button>
	);
	let content = (
		<DataViews< SpecificationSetItem >
			view={ view }
			onChangeView={ setView }
			fields={ fields }
			actions={ actions }
			data={ data }
			isLoading={ isLoading }
			paginationInfo={ paginationInfo }
			getItemId={ ( item ) => String( item.id ) }
			defaultLayouts={ { table: {} } }
			searchLabel={ __( 'Search sets', 'product-specifications' ) }
		/>
	);

	if ( isEmpty ) {
		content =
			specifications === 0 ? (
				<FirstRun
					createButton={
						<Button
							variant="tertiary"
							href={ adminPath( CREATE_PATH ) }
							onClick={ goToCreate }
						>
							{ __(
								'Create an empty set instead',
								'product-specifications'
							) }
						</Button>
					}
				/>
			) : (
				<EmptyCollection
					createButton={ createButton }
					specifications={ specifications ?? 0 }
				/>
			);
	}

	if ( hasError ) {
		content = (
			<EmptyState
				icon={ caution }
				title={ __(
					'Specification sets could not be loaded',
					'product-specifications'
				) }
				copy={ __(
					'Your sets are still intact. Check the connection and try again.',
					'product-specifications'
				) }
				actions={
					<Button variant="secondary" onClick={ retry }>
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
					title={ __(
						'Specification sets',
						'product-specifications'
					) }
					description={ __(
						'Group the specifications that belong to each kind of product.',
						'product-specifications'
					) }
					actions={ createButton }
					tabs={ <AreaTabs tabs={ AREA_TABS } /> }
				/>
			}
			notices={ <Notice notice={ notice } onDismiss={ dismissNotice } /> }
		>
			{ content }
			{ pendingDelete && (
				<DeleteDialog
					items={ pendingDelete }
					isDeleting={ isDeleting }
					onCancel={ () => setPendingDelete( null ) }
					onConfirm={ async () => {
						setIsDeleting( true );
						await handlers.remove( pendingDelete );
						setIsDeleting( false );
						setPendingDelete( null );
					} }
				/>
			) }
		</PageShell>
	);
};

export default SpecificationSetsPage;
