export type EditorTabName =
	| 'general'
	| 'groups'
	| 'assignments'
	| 'comparison'
	| 'preview'
	| 'advanced';

export type EditorPlaceholderTabName = Exclude< EditorTabName, 'general' >;

export interface EditorTab {
	name: EditorTabName;
	title: string;
	className?: string;
}
