import type {
	DismissNoticeAction,
	FailSaveAction,
	FinishSaveAction,
	GeneralField,
	InitializeEditorAction,
	SpecificationSetEditorNotice,
	SpecificationSetGeneralState,
	StartSavingAction,
	UpdateGeneralFieldAction,
} from './types';

export const actions = {
	initializeEditor(
		postId: number | null,
		general: SpecificationSetGeneralState
	): InitializeEditorAction {
		return {
			type: 'INITIALIZE_EDITOR',
			postId,
			general,
		};
	},
	updateGeneralField(
		field: GeneralField,
		value: SpecificationSetGeneralState[ GeneralField ]
	): UpdateGeneralFieldAction {
		return {
			type: 'UPDATE_GENERAL_FIELD',
			field,
			value,
		};
	},
	startSaving(): StartSavingAction {
		return {
			type: 'START_SAVING',
		};
	},
	finishSave(
		postId: number,
		general: SpecificationSetGeneralState,
		notice: SpecificationSetEditorNotice
	): FinishSaveAction {
		return {
			type: 'FINISH_SAVE',
			postId,
			general,
			notice,
		};
	},
	failSave( notice: SpecificationSetEditorNotice ): FailSaveAction {
		return {
			type: 'FAIL_SAVE',
			notice,
		};
	},
	dismissNotice(): DismissNoticeAction {
		return {
			type: 'DISMISS_NOTICE',
		};
	},
};
