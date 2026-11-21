export type EditorTabName = 'general' | 'specifications' | 'comparison';

export interface EditorTab {
	name: EditorTabName;
	title: string;
	className?: string;
}
