import React from "react";

import manifest from '@neos-project/neos-ui-extensibility';

import { NAMESPACE } from "./constants";

import { Reminder } from "./Reminder";
import { IntervalEditor } from "./IntervalEditor";

manifest(NAMESPACE, {}, globalRegistry => {
    const containersRegistry = globalRegistry.get('containers');
    const editorsRegistry = globalRegistry.get('inspector').get('editors');

    containersRegistry.set(
        'Modals/Sitegeist.Bitzer.Reminder',
        props => <Reminder {...props} i18n={globalRegistry.get('i18n')}/>
    );

    editorsRegistry.set('Sitegeist.Bitzer/Inspector/Editors/IntervalEditor', {
        component: props => <IntervalEditor {...props} i18n={globalRegistry.get('i18n')}/>
    });
});