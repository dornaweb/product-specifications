import { useEffect } from '@wordpress/element';
import { Button, Spinner } from '@wordpress/components';
import { __, _n, sprintf } from '@wordpress/i18n';
import { AREA_TABS } from '../shared/areas';
import { AreaTabs, Notice, PageHeader, PageShell } from '../shared/ui';
import { useUnsavedChangesGuard } from '../shared/platform';
import SettingControl from './SettingControl';
import { useSettings } from './useSettings';
import './styles.scss';

const SettingsPage = () => {
	const {
		response,
		draft,
		fieldErrors,
		notice,
		isLoading,
		isSaving,
		dirty,
		load,
		setValue,
		save,
		setNotice,
	} = useSettings();

	useUnsavedChangesGuard( dirty );

	useEffect( () => {
		const saveWithKeyboard = ( event: KeyboardEvent ) => {
			if (
				( event.metaKey || event.ctrlKey ) &&
				event.key.toLowerCase() === 's'
			) {
				event.preventDefault();
				void save();
			}
		};

		window.addEventListener( 'keydown', saveWithKeyboard );
		return () => window.removeEventListener( 'keydown', saveWithKeyboard );
	}, [ save ] );

	const actions = response?.canManage ? (
		<>
			<span className={ dirty ? 'ps-dirty' : 'ps-settings__clean' }>
				{ dirty
					? ( () => {
							const changedCount = response.fields.filter(
								( field ) =>
									JSON.stringify( draft[ field.key ] ) !==
									JSON.stringify(
										response.settings[ field.key ]
									)
							).length;

							return sprintf(
								/* translators: %d: number of unsaved settings. */
								_n(
									'%d unsaved change',
									'%d unsaved changes',
									changedCount,
									'product-specifications'
								),
								changedCount
							);
					  } )()
					: __( 'No changes', 'product-specifications' ) }
			</span>
			<Button
				variant="primary"
				disabled={ ! dirty || isSaving }
				isBusy={ isSaving }
				onClick={ () => void save() }
			>
				{ isSaving
					? __( 'Saving…', 'product-specifications' )
					: __( 'Save settings', 'product-specifications' ) }
			</Button>
		</>
	) : undefined;

	return (
		<PageShell
			header={
				<PageHeader
					title={ __( 'Settings', 'product-specifications' ) }
					description={ __(
						'How the plugin sits inside WooCommerce and your theme.',
						'product-specifications'
					) }
					actions={ actions }
					tabs={ <AreaTabs tabs={ AREA_TABS } /> }
				/>
			}
			notices={
				<>
					{ response && ! response.canManage && (
						<Notice
							notice={ {
								status: 'info',
								message: __(
									'You can see these settings but not change them. Changing plugin settings requires additional permission.',
									'product-specifications'
								),
							} }
						/>
					) }
					<Notice
						notice={ notice }
						onDismiss={ () => setNotice( null ) }
					/>
				</>
			}
		>
			{ isLoading && (
				<div className="ps-settings__loading">
					<Spinner />
					{ __( 'Loading settings…', 'product-specifications' ) }
				</div>
			) }
			{ ! isLoading && ! response && (
				<div className="ps-settings__failed">
					<Button variant="secondary" onClick={ () => void load() }>
						{ __( 'Try again', 'product-specifications' ) }
					</Button>
				</div>
			) }
			{ response && (
				<div className="ps-settings">
					{ response.sections.map( ( section ) => {
						const fields = response.fields.filter(
							( field ) => field.section === section.key
						);

						if ( fields.length === 0 ) {
							return null;
						}

						return (
							<section
								className="ps-settings-section"
								key={ section.key }
							>
								<div className="ps-settings-section__header">
									<h2>{ section.title }</h2>
									<p>{ section.description }</p>
								</div>
								<div className="ps-settings-section__body">
									{ fields.map( ( field ) => (
										<SettingControl
											key={ field.key }
											field={ field }
											value={
												draft[ field.key ] ??
												field.default
											}
											error={ fieldErrors[ field.key ] }
											readOnly={ ! response.canManage }
											onChange={ ( value ) =>
												setValue( field.key, value )
											}
										/>
									) ) }
								</div>
							</section>
						);
					} ) }
				</div>
			) }
		</PageShell>
	);
};

export default SettingsPage;
