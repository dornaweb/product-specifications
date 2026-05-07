import MainNavTabs from '../MainNavTabs/MainNavTabs';
import './styles.scss';

type Props = {
	title: string;
};

const Header = ( { title }: Props ) => {
	return (
		<div className="product-specifications-layout__header">
			<h1 className="product-specifications-layout__header-title">
				{ title }
			</h1>
			<MainNavTabs />
		</div>
	);
};

export default Header;
