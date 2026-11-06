import type { ReactNode } from 'react';

interface Props {
	header: ReactNode;
	notices?: ReactNode;
	children: ReactNode;
}

const PageShell = ( { header, notices, children }: Props ) => (
	<div className="ps-shell">
		{ header }
		<div className="ps-shell__content">
			{ notices }
			{ children }
		</div>
	</div>
);

export default PageShell;
