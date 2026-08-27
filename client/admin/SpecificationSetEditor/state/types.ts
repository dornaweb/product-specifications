export type SpecificationSetStatus = 'publish' | 'draft';

export interface SpecificationSetGeneralState {
	title: string;
	slug: string;
	description: string;
	status: SpecificationSetStatus;
}

export interface SpecificationSetEditorNotice {
	status: 'success' | 'error';
	message: string;
}

export interface SpecificationSetEditorFormState {
	general: SpecificationSetGeneralState;
}

export interface SpecificationSetEditorState {
	postId: number | null;
	form: SpecificationSetEditorFormState;
	hasUnsavedChanges: boolean;
	isSaving: boolean;
	notice: SpecificationSetEditorNotice | null;
}

export type GeneralField = keyof SpecificationSetGeneralState;

export interface UpdateGeneralFieldAction {
	type: 'UPDATE_GENERAL_FIELD';
	field: GeneralField;
	value: SpecificationSetGeneralState[ GeneralField ];
}

export interface InitializeEditorAction {
	type: 'INITIALIZE_EDITOR';
	postId: number | null;
	general: SpecificationSetGeneralState;
}

export interface StartSavingAction {
	type: 'START_SAVING';
}

export interface FinishSaveAction {
	type: 'FINISH_SAVE';
	postId: number;
	general: SpecificationSetGeneralState;
	notice: SpecificationSetEditorNotice;
}

export interface FailSaveAction {
	type: 'FAIL_SAVE';
	notice: SpecificationSetEditorNotice;
}

export interface DismissNoticeAction {
	type: 'DISMISS_NOTICE';
}

export type SpecificationSetEditorAction =
	| InitializeEditorAction
	| UpdateGeneralFieldAction
	| StartSavingAction
	| FinishSaveAction
	| FailSaveAction
	| DismissNoticeAction;
