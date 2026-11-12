import { useEffect, useRef } from '@wordpress/element';
import { Notice as WordPressNotice, Snackbar } from '@wordpress/components';

export type NoticeStatus = 'success' | 'warning' | 'error' | 'info';

export interface NoticeState {
	status: NoticeStatus;
	message: string;
}

interface Props {
	notice: NoticeState | null;
	onDismiss?: () => void;
	className?: string;
}

const SUCCESS_TIMEOUT = 4000;

// A confirmation of what the user just did is transient and takes no room in
// the layout: a banner above the list would push the page down on every save,
// and a run of entries meets that on every one of them. Everything else stays
// in place until it is read.
const Notice = ( { notice, onDismiss, className }: Props ) => {
	const dismiss = useRef( onDismiss );

	dismiss.current = onDismiss;

	useEffect( () => {
		if ( notice?.status !== 'success' ) {
			return;
		}

		const timer = setTimeout( () => dismiss.current?.(), SUCCESS_TIMEOUT );

		return () => clearTimeout( timer );
	}, [ notice ] );

	if ( ! notice ) {
		return null;
	}

	if ( notice.status === 'success' ) {
		return (
			<div className="ps-snackbar">
				<Snackbar onRemove={ onDismiss }>{ notice.message }</Snackbar>
			</div>
		);
	}

	return (
		<WordPressNotice
			className={ className }
			status={ notice.status }
			onRemove={ onDismiss }
		>
			{ notice.message }
		</WordPressNotice>
	);
};

export default Notice;
