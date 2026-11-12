import {
	Button,
	Notice,
	SelectControl,
	TextControl,
	TextareaControl,
} from '@wordpress/components';
import { useEffect, useRef, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { displayShortcut, isKeyboardEvent } from '@wordpress/keycodes';
import { safeDecodeURIComponent } from '@wordpress/url';
import { Field, FieldGrid, InlineForm, Shortcut } from '../../shared/ui';
import {
	specificationType,
	specificationTypes,
	type Suggestion,
} from '../../shared/specificationTypes';
import type { SpecificationItem } from '../mapRecord';

export interface SpecificationDraft {
	name: string;
	description: string;
	type: string;
	options: Record< string, unknown >;
	defaultValue: unknown;
	suggestions: Suggestion[];
}

export function emptyDraft( type = '' ): SpecificationDraft {
	return {
		name: '',
		description: '',
		type,
		options: {},
		defaultValue: null,
		suggestions: [],
	};
}

export function draftFrom( item: SpecificationItem ): SpecificationDraft {
	return {
		name: item.name,
		description: item.description,
		type: item.type,
		options: item.options,
		defaultValue: item.defaultValue,
		suggestions: item.suggestions,
	};
}

interface Props {
	draft: SpecificationDraft;
	onChange: ( draft: SpecificationDraft ) => void;
	onSaveAndClose: () => void;
	onSaveAndAddAnother?: () => void;
	onCancel: () => void;
	label: string;
	saveLabel?: string;
	slug?: string;
	isSaving: boolean;
	duplicateOf?: SpecificationItem;
	onOpenDuplicate?: ( item: SpecificationItem ) => void;
	typeCarried?: boolean;
	error?: string;
}

const SpecificationForm = ( {
	draft,
	onChange,
	onSaveAndClose,
	onSaveAndAddAnother,
	onCancel,
	label,
	saveLabel,
	slug,
	isSaving,
	duplicateOf,
	onOpenDuplicate,
	typeCarried,
	error,
}: Props ) => {
	const typeFieldRef = useRef< HTMLDivElement | null >( null );
	const nameFieldRef = useRef< HTMLDivElement | null >( null );
	const savedType = useRef( draft.type );
	const [ pendingType, setPendingType ] = useState< string | null >( null );
	const [ optionsError, setOptionsError ] = useState< string >();

	// The type is the spine of the form: with none chosen there is nothing to
	// fill in yet, and with one carried forward the name is the only field the
	// next entry needs. Where the form opens is decided once, when it opens.
	const opensOnName = useRef( Boolean( draft.type ) );

	useEffect( () => {
		const field = opensOnName.current ? nameFieldRef : typeFieldRef;

		field.current?.querySelector< HTMLElement >( 'input, select' )?.focus();
	}, [] );

	const currentType = specificationType( draft.type );
	const { options: OptionsPanel, defaultControl: DefaultControl } =
		currentType;
	// The rest of the form appears once the type is chosen, because the type
	// decides which fields there are. A name carried in from a failed search is
	// the same commitment, and hiding it would lose the word already typed.
	const isStarted = Boolean( draft.type ) || Boolean( draft.name.trim() );

	const chooseType = ( next: string ) => {
		// Values stored under the old type may not be readable by the new one,
		// so a saved specification asks before anything changes.
		if ( slug && next !== savedType.current ) {
			setPendingType( next );
			return;
		}

		setPendingType( null );
		setOptionsError( undefined );
		onChange( {
			...draft,
			type: next,
			options: {},
			defaultValue: null,
		} );
	};

	const submit = ( andAnother: boolean ) => {
		const invalid = specificationType( draft.type ).validateOptions?.(
			draft.options
		);

		if ( invalid ) {
			setOptionsError( invalid );
			return;
		}

		setOptionsError( undefined );

		if ( andAnother && onSaveAndAddAnother ) {
			onSaveAndAddAnother();
			return;
		}

		onSaveAndClose();
	};

	const isSavable =
		Boolean( draft.type ) && Boolean( draft.name.trim() ) && ! isSaving;

	return (
		<div
			onKeyDown={ ( event ) => {
				if ( isKeyboardEvent.primary( event, 'Enter' ) ) {
					event.preventDefault();
					submit( Boolean( onSaveAndAddAnother ) );
				}

				if ( isKeyboardEvent.primary( event, 's' ) ) {
					event.preventDefault();
					submit( false );
				}

				if ( event.key === 'Escape' ) {
					event.preventDefault();
					onCancel();
				}
			} }
			role="presentation"
		>
			<InlineForm
				label={ label }
				atTop
				actions={
					pendingType ? (
						<>
							<Button
								variant="primary"
								onClick={ () => {
									savedType.current = pendingType;
									setPendingType( null );
									onChange( {
										...draft,
										type: pendingType,
										options: {},
										defaultValue: null,
									} );
								} }
							>
								{ __(
									'Change type',
									'product-specifications'
								) }
							</Button>
							<Button
								variant="tertiary"
								onClick={ () => setPendingType( null ) }
							>
								{ sprintf(
									/* translators: %s: the label of the type the specification is saved with. */
									__( 'Keep %s', 'product-specifications' ),
									specificationType( draft.type ).label
								) }
							</Button>
						</>
					) : (
						<>
							{ onSaveAndAddAnother && (
								<Button
									variant="primary"
									isBusy={ isSaving }
									disabled={ ! isSavable }
									onClick={ () => submit( true ) }
								>
									{ __(
										'Save and add another',
										'product-specifications'
									) }
									<Shortcut
										keys={ displayShortcut.primary( '↵' ) }
									/>
								</Button>
							) }
							<Button
								variant={
									onSaveAndAddAnother
										? 'secondary'
										: 'primary'
								}
								isBusy={ isSaving }
								disabled={ ! isSavable }
								onClick={ () => submit( false ) }
							>
								{ saveLabel ??
									( onSaveAndAddAnother
										? __(
												'Save and close',
												'product-specifications'
										  )
										: __(
												'Save',
												'product-specifications'
										  ) ) }
								<Shortcut
									keys={ displayShortcut.primary( 's' ) }
								/>
							</Button>
							<Button variant="tertiary" onClick={ onCancel }>
								{ __( 'Cancel', 'product-specifications' ) }
								<Shortcut keys="Esc" />
							</Button>
						</>
					)
				}
			>
				<FieldGrid>
					<div
						ref={ typeFieldRef }
						className={ pendingType ? 'ps-field--wide' : undefined }
					>
						<Field
							label={ __( 'Type', 'product-specifications' ) }
							help={
								typeCarried
									? __(
											'Carried over from the last one you added.',
											'product-specifications'
									  )
									: __(
											'What kind of answer this records. It decides the input on the product screen and how the value renders.',
											'product-specifications'
									  )
							}
						>
							{ ( controlProps ) => (
								<SelectControl
									{ ...controlProps }
									__nextHasNoMarginBottom
									__next40pxDefaultSize
									value={ pendingType ?? draft.type }
									options={ [
										...( draft.type
											? []
											: [
													{
														value: '',
														label: __(
															'Choose a type…',
															'product-specifications'
														),
													},
											  ] ),
										...( draft.type &&
										! currentType.isRegistered
											? [
													{
														value: draft.type,
														label: currentType.label,
													},
											  ]
											: [] ),
										...Object.entries(
											specificationTypes()
										).map( ( [ id, entry ] ) => ( {
											value: id,
											label: entry.label,
										} ) ),
									] }
									onChange={ chooseType }
								/>
							) }
						</Field>
						{ pendingType && (
							<Notice status="warning" isDismissible={ false }>
								{ sprintf(
									/* translators: 1: the type the specification is saved with. 2: the type it would change to. */
									__(
										'Changing the type may make existing values unreadable. This is a %1$s today, and values that %2$s cannot read stay stored and appear as unreadable on the product screen. Nothing is deleted, and changing the type back restores them.',
										'product-specifications'
									),
									specificationType( draft.type ).label,
									specificationType( pendingType ).label
								) }
							</Notice>
						) }
					</div>
					{ ! isStarted && ! pendingType && (
						<ul className="ps-type-list">
							{ Object.entries( specificationTypes() ).map(
								( [ id, entry ] ) => (
									<li key={ id }>
										<b>{ entry.label }</b>{ ' ' }
										{ entry.description }
									</li>
								)
							) }
						</ul>
					) }
					{ isStarted && ! pendingType && (
						<>
							<div ref={ nameFieldRef }>
								<Field
									label={ __(
										'Name',
										'product-specifications'
									) }
									help={
										duplicateOf ? (
											<>
												{ sprintf(
													/* translators: %s: the name of the existing specification. */
													__(
														'A specification called “%s” already exists. This is allowed — slugs cannot collide — but it is usually a mistake.',
														'product-specifications'
													),
													duplicateOf.name
												) }{ ' ' }
												{ onOpenDuplicate && (
													<Button
														variant="link"
														onClick={ () =>
															onOpenDuplicate(
																duplicateOf
															)
														}
													>
														{ __(
															'Open the existing one',
															'product-specifications'
														) }
													</Button>
												) }
											</>
										) : (
											__(
												'Renaming later changes the label everywhere and touches no stored data.',
												'product-specifications'
											)
										)
									}
									helpTone={
										duplicateOf ? 'warning' : 'neutral'
									}
								>
									{ ( controlProps ) => (
										<TextControl
											{ ...controlProps }
											__nextHasNoMarginBottom
											__next40pxDefaultSize
											value={ draft.name }
											placeholder={ __(
												'Ready to type',
												'product-specifications'
											) }
											onChange={ ( name ) =>
												onChange( { ...draft, name } )
											}
										/>
									) }
								</Field>
							</div>
							{ slug && (
								<Field
									label={ __(
										'Slug',
										'product-specifications'
									) }
									help={ __(
										'Permanent. It is the identity used in exports and templates, and never follows a rename.',
										'product-specifications'
									) }
								>
									{ ( controlProps ) => (
										<TextControl
											{ ...controlProps }
											__nextHasNoMarginBottom
											__next40pxDefaultSize
											className="ps-slug"
											readOnly
											value={ safeDecodeURIComponent(
												slug
											) }
											onChange={ () => undefined }
										/>
									) }
								</Field>
							) }
							{ OptionsPanel && (
								<OptionsPanel
									options={ draft.options }
									suggestions={ draft.suggestions }
									error={ optionsError }
									onChange={ ( options ) => {
										setOptionsError( undefined );
										onChange( { ...draft, options } );
									} }
								/>
							) }
							{ DefaultControl && (
								<DefaultControl
									value={ draft.defaultValue }
									options={ draft.options }
									suggestions={ draft.suggestions }
									onChange={ ( defaultValue ) =>
										onChange( { ...draft, defaultValue } )
									}
								/>
							) }
							<Field
								label={ __(
									'Description',
									'product-specifications'
								) }
								wide
							>
								{ ( controlProps ) => (
									<TextareaControl
										{ ...controlProps }
										__nextHasNoMarginBottom
										value={ draft.description }
										placeholder={ __(
											'Optional',
											'product-specifications'
										) }
										onChange={ ( description ) =>
											onChange( {
												...draft,
												description,
											} )
										}
									/>
								) }
							</Field>
						</>
					) }
					{ error && (
						<div className="ps-field--wide">
							<Notice status="error" isDismissible={ false }>
								{ error }
							</Notice>
						</div>
					) }
				</FieldGrid>
			</InlineForm>
		</div>
	);
};

export default SpecificationForm;
