import { Notice } from '@wordpress/components';
import type { SpecificationSetEditorNotice as NoticeState } from '../state';

interface EditorNoticeProps {
	notice: NoticeState | null;
	onDismiss: () => void;
}

const EditorNotice = ( { notice, onDismiss }: EditorNoticeProps ) => {
	if ( ! notice ) {
		return null;
	}

	return (
		<Notice
			className="product-specifications-specs-set-editor__notice"
			status={ notice.status }
			onRemove={ onDismiss }
		>
			{ notice.message }
		</Notice>
	);
};

export default EditorNotice;
