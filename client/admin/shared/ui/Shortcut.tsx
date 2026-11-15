interface Props {
	keys: string;
}

// A shortcut is drawn on the control that carries it: one nobody can see is one
// nobody uses. `Button`'s own `shortcut` prop only reaches a tooltip, which
// hover hides from the keyboard and touch alike.
const Shortcut = ( { keys }: Props ) => (
	<span className="ps-shortcut">{ keys }</span>
);

export default Shortcut;
