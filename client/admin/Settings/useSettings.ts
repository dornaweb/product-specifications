import { useCallback, useEffect, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { fetchSettings, saveSettings, SettingsApiError } from './api';
import type { SettingsResponse, SettingValue } from './types';
import type { NoticeState } from '../shared/ui';

const sameSettings = (
	left: Record< string, SettingValue >,
	right: Record< string, SettingValue >
) => JSON.stringify( left ) === JSON.stringify( right );

export function useSettings() {
	const [ response, setResponse ] = useState< SettingsResponse | null >(
		null
	);
	const [ saved, setSaved ] = useState< Record< string, SettingValue > >(
		{}
	);
	const [ draft, setDraft ] = useState< Record< string, SettingValue > >(
		{}
	);
	const [ fieldErrors, setFieldErrors ] = useState<
		Record< string, string >
	>( {} );
	const [ notice, setNotice ] = useState< NoticeState | null >( null );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ isSaving, setIsSaving ] = useState( false );

	const load = useCallback( async () => {
		setIsLoading( true );
		setNotice( null );

		try {
			const loaded = await fetchSettings();
			setResponse( loaded );
			setSaved( loaded.settings );
			setDraft( loaded.settings );
			setFieldErrors( loaded.errors );
		} catch ( error ) {
			setNotice( {
				status: 'error',
				message:
					error instanceof Error
						? error.message
						: __(
								'The settings could not be loaded.',
								'product-specifications'
						  ),
			} );
		} finally {
			setIsLoading( false );
		}
	}, [] );

	useEffect( () => {
		void load();
	}, [ load ] );

	const dirty = useMemo(
		() => ! sameSettings( saved, draft ),
		[ saved, draft ]
	);

	const setValue = useCallback( ( key: string, value: SettingValue ) => {
		setDraft( ( current ) => ( { ...current, [ key ]: value } ) );
		setFieldErrors( ( current ) => {
			const next = { ...current };
			delete next[ key ];
			return next;
		} );
		setNotice( null );
	}, [] );

	const save = useCallback( async () => {
		if ( ! dirty || isSaving || ! response?.canManage ) {
			return;
		}

		setIsSaving( true );
		setNotice( null );

		try {
			const updated = await saveSettings( draft );
			setResponse( updated );
			setSaved( updated.settings );
			setDraft( updated.settings );
			setFieldErrors( updated.errors );
			setNotice( {
				status: 'success',
				message: __( 'Settings saved.', 'product-specifications' ),
			} );
		} catch ( error ) {
			setFieldErrors(
				error instanceof SettingsApiError ? error.fields : {}
			);
			setNotice( {
				status: 'error',
				message:
					error instanceof Error
						? error.message
						: __(
								'The settings could not be saved.',
								'product-specifications'
						  ),
			} );
		} finally {
			setIsSaving( false );
		}
	}, [ dirty, draft, isSaving, response?.canManage ] );

	return {
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
	};
}
