import {
	Button,
	Modal,
	Notice,
	SelectControl,
	Spinner,
	TextareaControl,
} from '@wordpress/components';
import { useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { __, sprintf, _n } from '@wordpress/i18n';
import { specificationType } from '../admin/shared/specificationTypes';
import {
	readProductValues,
	readSets,
	readSpecifications,
	saveProductValues,
} from './api';
import { mergeSuggestedDefaults } from './suggestedDefaults';
import type { SetRecord, SpecificationRecord, StructureGroup } from './types';

interface Props {
	productId: number;
}

const titleOf = ( set: SetRecord ) =>
	typeof set.title === 'string'
		? set.title
		: set.title?.raw ?? set.title?.rendered ?? set.slug;

const groupsOf = ( set: SetRecord | undefined ): StructureGroup[] =>
	set?.meta?.specification_set_structure?.groups ?? [];

const errorMessage = ( error: unknown ) =>
	error instanceof Error
		? error.message
		: __(
				'The request failed. Please try again.',
				'product-specifications'
		  );

const ProductValuesPanel = ( { productId }: Props ) => {
	const [ sets, setSets ] = useState< SetRecord[] >( [] );
	const [ setSlug, setSetSlug ] = useState< string | null >( null );
	const [ values, setValues ] = useState< Record< string, unknown > >( {} );
	const [ specifications, setSpecifications ] = useState<
		Record< number, SpecificationRecord >
	>( {} );
	const [ loadedDefinitionsKey, setLoadedDefinitionsKey ] = useState( '' );
	const [ suggested, setSuggested ] = useState< Set< string > >( new Set() );
	const [ activeGroup, setActiveGroup ] = useState( '' );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ isSaving, setIsSaving ] = useState( false );
	const [ dirty, setDirty ] = useState( false );
	const [ error, setError ] = useState< string | null >( null );
	const [ changeMode, setChangeMode ] = useState( false );
	const [ candidate, setCandidate ] = useState( '' );
	const [ confirming, setConfirming ] = useState<
		string | null | undefined
	>();
	const allowSubmit = useRef( false );

	const selectedSet = sets.find( ( item ) => item.slug === setSlug );
	const groups = useMemo( () => groupsOf( selectedSet ), [ selectedSet ] );
	const specificationIds = useMemo(
		() =>
			Array.from(
				new Set(
					groups.flatMap( ( group ) =>
						group.items.map( ( item ) => item.term_id )
					)
				)
			),
		[ groups ]
	);
	const definitionsKey = specificationIds.join( ',' );

	useEffect( () => {
		Promise.all( [ readProductValues( productId ), readSets() ] )
			.then( ( [ response, availableSets ] ) => {
				setSets( availableSets );
				setSetSlug( response.set );
				setValues( response.values.values ?? {} );
			} )
			.catch( ( reason ) => setError( errorMessage( reason ) ) )
			.finally( () => setIsLoading( false ) );
	}, [ productId ] );

	useEffect( () => {
		const firstGroup = groups[ 0 ]?.key ?? '';
		if ( ! groups.some( ( group ) => group.key === activeGroup ) ) {
			setActiveGroup( firstGroup );
		}
	}, [ groups, activeGroup ] );

	useEffect( () => {
		let cancelled = false;

		readSpecifications( specificationIds )
			.then( ( records ) => {
				if ( cancelled ) {
					return;
				}

				setSpecifications(
					Object.fromEntries(
						records.map( ( item ) => [ item.id, item ] )
					)
				);

				setValues( ( current ) => {
					const { values: next, added } = mergeSuggestedDefaults(
						current,
						records
					);

					if ( added.length > 0 ) {
						setSuggested(
							( currentSuggested ) =>
								new Set( [ ...currentSuggested, ...added ] )
						);
						setDirty( true );
					}

					return next;
				} );
				setLoadedDefinitionsKey( definitionsKey );
			} )
			.catch( ( reason ) => {
				if ( ! cancelled ) {
					setError( errorMessage( reason ) );
					setLoadedDefinitionsKey( definitionsKey );
				}
			} );

		return () => {
			cancelled = true;
		};
	}, [ definitionsKey, specificationIds ] );

	const latestSave = useRef( { dirty, isSaving, setSlug, values } );
	useEffect( () => {
		latestSave.current = { dirty, isSaving, setSlug, values };
	}, [ dirty, isSaving, setSlug, values ] );

	useEffect( () => {
		const form = document.querySelector< HTMLFormElement >( '#post' );
		if ( ! form ) {
			return;
		}

		const beforeSubmit = ( event: SubmitEvent ) => {
			const current = latestSave.current;

			if ( allowSubmit.current || ! current.dirty ) {
				return;
			}

			// A save already in flight must still block this submit; retrying
			// it here would race the one that is already replaying the form.
			event.preventDefault();
			event.stopImmediatePropagation();

			if ( current.isSaving ) {
				return;
			}

			setIsSaving( true );
			setError( null );

			void saveProductValues( productId, current.setSlug, current.values )
				.then( () => {
					setDirty( false );
					allowSubmit.current = true;
					form.requestSubmit(
						event.submitter instanceof HTMLElement
							? ( event.submitter as HTMLButtonElement )
							: undefined
					);
				} )
				.catch( ( reason ) => setError( errorMessage( reason ) ) )
				.finally( () => setIsSaving( false ) );
		};

		form.addEventListener( 'submit', beforeSubmit, true );
		return () => form.removeEventListener( 'submit', beforeSubmit, true );
	}, [ productId ] );

	const updateValue = ( slug: string, value: unknown ) => {
		setValues( ( current ) => ( { ...current, [ slug ]: value } ) );
		setSuggested( ( current ) => {
			const next = new Set( current );
			next.delete( slug );
			return next;
		} );
		setDirty( true );
	};

	const applySet = ( next: string | null ) => {
		setSetSlug( next );
		setChangeMode( false );
		setCandidate( '' );
		setConfirming( undefined );
		setDirty( true );
	};

	if ( isLoading ) {
		return <Spinner />;
	}

	return (
		<div className="ps-product-values">
			{ error && (
				<Notice status="error" isDismissible={ false }>
					{ error }{ ' ' }
					{ __(
						'Your entered values are still here.',
						'product-specifications'
					) }
				</Notice>
			) }
			{ isSaving && (
				<Notice status="info" isDismissible={ false }>
					{ __(
						'Saving product specifications…',
						'product-specifications'
					) }
				</Notice>
			) }

			<div className="ps-product-values__set-bar">
				<div>
					<span className="ps-product-values__eyebrow">
						{ __( 'Specification set', 'product-specifications' ) }
					</span>
					{ selectedSet && (
						<strong>{ titleOf( selectedSet ) }</strong>
					) }
				</div>
				{ selectedSet && ! changeMode && (
					<Button
						variant="link"
						onClick={ () => setChangeMode( true ) }
					>
						{ __( 'Change', 'product-specifications' ) }
					</Button>
				) }
				{ selectedSet && (
					<span className="ps-product-values__summary">
						{ sprintf(
							/* translators: 1: number of specifications, 2: number of groups. */
							__(
								'%1$d specifications in %2$d groups',
								'product-specifications'
							),
							groups.reduce(
								( total, group ) => total + group.items.length,
								0
							),
							groups.length
						) }
					</span>
				) }
			</div>

			{ ( ! selectedSet || changeMode ) && (
				<div className="ps-product-values__assign">
					{ ! selectedSet && (
						<p>
							{ __(
								'No specifications apply until a specification set is chosen.',
								'product-specifications'
							) }
						</p>
					) }
					<SelectControl
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						label={ __(
							'Specification set',
							'product-specifications'
						) }
						value={ candidate }
						options={ [
							{
								value: '',
								label: changeMode
									? __(
											'No specification set',
											'product-specifications'
									  )
									: __(
											'Choose a set',
											'product-specifications'
									  ),
							},
							...sets.map( ( item ) => ( {
								value: item.slug,
								label: titleOf( item ),
							} ) ),
						] }
						onChange={ setCandidate }
					/>
					<Button
						variant="secondary"
						disabled={ ! changeMode && candidate === '' }
						onClick={ () =>
							selectedSet
								? setConfirming( candidate || null )
								: applySet( candidate )
						}
					>
						{ selectedSet
							? __( 'Continue', 'product-specifications' )
							: __(
									'Assign a specification set',
									'product-specifications'
							  ) }
					</Button>
					{ changeMode && (
						<Button
							variant="tertiary"
							onClick={ () => setChangeMode( false ) }
						>
							{ __( 'Cancel', 'product-specifications' ) }
						</Button>
					) }
				</div>
			) }

			{ setSlug && ! selectedSet && (
				<Notice status="warning" isDismissible={ false }>
					{ __(
						'The assigned specification set is missing. Choose another set before saving.',
						'product-specifications'
					) }
				</Notice>
			) }

			{ selectedSet && groups.length === 0 && (
				<p>
					{ __(
						'This set has no specification groups yet.',
						'product-specifications'
					) }
				</p>
			) }

			{ selectedSet &&
				groups.length > 0 &&
				loadedDefinitionsKey !== definitionsKey && <Spinner /> }

			{ selectedSet &&
				groups.length > 0 &&
				loadedDefinitionsKey === definitionsKey && (
					<div className="ps-product-values__workspace">
						<div
							className="ps-product-values__tabs"
							role="tablist"
							aria-orientation="vertical"
						>
							{ groups.map( ( group ) => {
								const filled = group.items.filter(
									( item ) =>
										Object.prototype.hasOwnProperty.call(
											values,
											item.slug
										) && values[ item.slug ] !== null
								).length;
								return (
									<button
										key={ group.key }
										type="button"
										role="tab"
										aria-selected={
											activeGroup === group.key
										}
										className={
											activeGroup === group.key
												? 'is-active'
												: ''
										}
										onClick={ () =>
											setActiveGroup( group.key )
										}
									>
										<span>{ group.label }</span>
										<small>{ `${ filled }/${ group.items.length }` }</small>
									</button>
								);
							} ) }
						</div>
						<div
							className="ps-product-values__fields"
							role="tabpanel"
						>
							{ groups
								.find( ( group ) => group.key === activeGroup )
								?.items.map( ( item ) => {
									const specification =
										specifications[ item.term_id ];
									if ( ! specification ) {
										return (
											<Notice
												key={ item.slug }
												status="warning"
												isDismissible={ false }
											>
												{ sprintf(
													/* translators: %s: slug of a missing specification. */
													__(
														'Missing specification: %s',
														'product-specifications'
													),
													item.slug
												) }
											</Notice>
										);
									}

									return (
										<ValueField
											key={ item.slug }
											specification={ specification }
											value={ values[ item.slug ] }
											isSuggested={ suggested.has(
												item.slug
											) }
											onChange={ ( value ) =>
												updateValue( item.slug, value )
											}
										/>
									);
								} ) }
						</div>
					</div>
				) }

			{ selectedSet && (
				<p className="description">
					{ __(
						'Specification values are saved with the product’s Update button.',
						'product-specifications'
					) }
				</p>
			) }

			{ confirming !== undefined && (
				<Modal
					title={
						confirming === null
							? __(
									'Remove specification set?',
									'product-specifications'
							  )
							: __(
									'Change specification set?',
									'product-specifications'
							  )
					}
					onRequestClose={ () => setConfirming( undefined ) }
				>
					<Notice
						status={ confirming === null ? 'warning' : 'info' }
						isDismissible={ false }
					>
						{ confirming === null
							? sprintf(
									/* translators: %d: number of stored values to delete. */
									_n(
										'%d stored value will be deleted when the product is saved.',
										'%d stored values will be deleted when the product is saved.',
										Object.keys( values ).length,
										'product-specifications'
									),
									Object.keys( values ).length
							  )
							: __(
									'Values shared by both sets stay in place. Values used only by the old set are kept and return if you switch back.',
									'product-specifications'
							  ) }
					</Notice>
					<div className="ps-product-values__modal-actions">
						<Button
							variant="tertiary"
							onClick={ () => setConfirming( undefined ) }
						>
							{ __( 'Cancel', 'product-specifications' ) }
						</Button>
						<Button
							variant="primary"
							isDestructive={ confirming === null }
							onClick={ () => applySet( confirming ) }
						>
							{ confirming === null
								? __(
										'Remove on save',
										'product-specifications'
								  )
								: __( 'Change set', 'product-specifications' ) }
						</Button>
					</div>
				</Modal>
			) }
		</div>
	);
};

const ValueField = ( {
	specification,
	value,
	isSuggested,
	onChange,
}: {
	specification: SpecificationRecord;
	value: unknown;
	isSuggested: boolean;
	onChange: ( value: unknown ) => void;
} ) => {
	const definition = specification.specification ?? {};
	const type = specificationType( definition.type ?? 'text' );
	const Control = type.valueControl;
	const isRawValue = typeof value !== 'string';

	return (
		<div className="ps-product-values__field">
			<div className="ps-product-values__field-label">
				<strong>{ specification.name }</strong>
				{ isSuggested && (
					<span>{ __( 'Suggested', 'product-specifications' ) }</span>
				) }
				{ specification.description && (
					<small>{ specification.description }</small>
				) }
			</div>
			<div>
				{ Control ? (
					<Control
						label={ specification.name }
						value={ value }
						options={ definition.options ?? {} }
						onChange={ onChange }
					/>
				) : (
					<>
						<Notice status="warning" isDismissible={ false }>
							{ sprintf(
								/* translators: %s: unavailable specification type id. */
								__(
									'The “%s” value type is unavailable. Its raw stored value is preserved until you replace it.',
									'product-specifications'
								),
								definition.type ?? 'unknown'
							) }
						</Notice>
						<TextareaControl
							__nextHasNoMarginBottom
							label={ __(
								'Raw stored value',
								'product-specifications'
							) }
							value={
								isRawValue
									? JSON.stringify( value ?? null )
									: value
							}
							onChange={ ( next ) => {
								if ( ! isRawValue ) {
									onChange( next );
									return;
								}
								try {
									onChange( JSON.parse( next ) );
								} catch {
									onChange( next );
								}
							} }
						/>
					</>
				) }
			</div>
		</div>
	);
};

export default ProductValuesPanel;
