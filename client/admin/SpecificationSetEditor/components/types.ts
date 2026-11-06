export type EditorTabName =
	| 'general'
	| 'specifications'
	| 'comparison'
	| 'layout';

export type EditorPlaceholderTabName = Exclude<
	EditorTabName,
	'general' | 'specifications'
>;

export interface EditorTab {
	name: EditorTabName;
	title: string;
	className?: string;
}
