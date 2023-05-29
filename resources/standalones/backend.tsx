import { BaseControl } from '@wordpress/components';
import { useEffect, useState } from 'react';

const MainControl = () => {
    const [message, setMessage] = useState<string>('salam');
    const [message2, setMessage2] = useState<string>('salam');

    useEffect(() => {
        setMessage2(message);
    }, [message]);

    return (
        <BaseControl className={'sdasd' + message} id="sslam" label="Sla">
            {message}
        </BaseControl>
    );
};

export default MainControl;
