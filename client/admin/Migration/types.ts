export type RunStatus = 'idle' | 'running' | 'failed' | 'complete';

export interface SkippedEntry {
	step: string;
	item: number;
}

export interface ErrorEntry extends SkippedEntry {
	message: string;
}

export interface MigrationRun {
	status: RunStatus;
	step: string;
	cursor: number;
	processed: number;
	total: number;
	started_at: number | null;
	skipped: SkippedEntry[];
	errors: ErrorEntry[];
	failure: string | null;
}

export interface StepScope {
	key: string;
	total: number;
}

export interface MigrationStatus {
	migrated: boolean;
	run: MigrationRun;
	steps: StepScope[] | null;
}

export type ScreenState = 'pending' | 'running' | 'failed' | 'complete';
