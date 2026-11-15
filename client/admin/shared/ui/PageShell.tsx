import type { ReactNode } from 'react';
import MigrationBanner from '../MigrationBanner';

interface Props {
	header: ReactNode;
	notices?: ReactNode;
	children: ReactNode;
}

const PageShell = ( { header, notices, children }: Props ) => (
	<div className="ps-shell">
		{ header }
		<div className="ps-shell__content">
			<MigrationBanner />
			{ notices }
			{ children }
		</div>
	</div>
);

export default PageShell;
